<?php
global $mysqli;

use Random\RandomException;

session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: sucesso.php");
    exit();
}

include("conexao.php");

$max_tentativas = 5;
$janela_tempo   = 15 * 60;

if (!isset($_SESSION['cadastro_tentativas'])) {
    $_SESSION['cadastro_tentativas'] = 0;
    $_SESSION['cadastro_primeiro_erro'] = null;
}

$bloqueado = false;
if ($_SESSION['cadastro_tentativas'] >= $max_tentativas) {
    $tempo_passado = time() - $_SESSION['cadastro_primeiro_erro'];

    if ($tempo_passado < $janela_tempo) {
        $bloqueado = true;
        $tempo_restante = ceil(($janela_tempo - $tempo_passado) / 60);
    } else {
        $_SESSION['cadastro_tentativas']    = 0;
        $_SESSION['cadastro_primeiro_erro'] = null;
    }
}

if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (RandomException $e) {

    }
}

$erro = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token_recebido = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token_recebido)) {
        http_response_code(403);
        die("Requisição inválida. Por favor, recarregue a página e tente novamente.");
    }

    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (RandomException $e) {

    }

    if ($bloqueado) {
        $erro[] = "Muitas tentativas. Aguarde $tempo_restante minuto(s) para tentar novamente.";
    } else {
        $nome      = trim($_POST['nome']      ?? '');
        $sobrenome = trim($_POST['sobrenome'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $senha     = $_POST['senha']          ?? '';

        if (empty($nome) || empty($sobrenome)) {
            $erro[] = "Preencha nome e sobrenome.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro[] = "E-mail inválido.";
        }

        if (strlen($senha) < 8) {
            $erro[] = "A senha deve ter pelo menos 8 caracteres.";
        }

        if (empty($erro)) {
            $stmt = $mysqli->prepare("SELECT codigo FROM usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $erro[] = "E-mail já cadastrado.";
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt_insert = $mysqli->prepare("INSERT INTO usuario (nome, sobrenome, email, senha) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $nome, $sobrenome, $email, $senha_hash);

                if ($stmt_insert->execute()) {
                    $_SESSION['cadastro_tentativas']    = 0;
                    $_SESSION['cadastro_primeiro_erro'] = null;

                    $novo_id = $mysqli->insert_id;
                    $_SESSION['usuario'] = $novo_id;

                    $stmt_tipo = $mysqli->prepare("SELECT tipo_usuario FROM usuario WHERE codigo = ?");
                    $stmt_tipo->bind_param("i", $novo_id);
                    $stmt_tipo->execute();
                    $resultado_tipo = $stmt_tipo->get_result()->fetch_assoc();
                    $stmt_tipo->close();
                    $_SESSION['tipo_usuario'] = $resultado_tipo['tipo_usuario'];

                    header("Location: sucesso.php");
                    exit();
                } else {
                    $erro[] = "Erro ao cadastrar. Por favor, tente novamente.";
                }

                $stmt_insert->close();
            }
            $stmt->close();
        }

        if (!empty($erro)) {
            $_SESSION['cadastro_tentativas']++;

            if ($_SESSION['cadastro_tentativas'] === 1) {
                $_SESSION['cadastro_primeiro_erro'] = time();
            }

            if ($_SESSION['cadastro_tentativas'] >= $max_tentativas) {
                $bloqueado = true;
                $tempo_restante = ceil($janela_tempo / 60);
                $erro = ["Muitas tentativas. Aguarde {$tempo_restante} minuto(s) para tentar novamente."];
            }
        }
    }
}

$tentativas_restantes = max(0, $max_tentativas - $_SESSION['cadastro_tentativas']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="../Paginas/style.css">
</head>
<body>
<div class="container">

    <?php if (!empty($erro)): ?>
        <div class="alert error">
            <?php foreach ($erro as $msg): ?>
                <p><?= htmlspecialchars($msg) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$bloqueado && $_SESSION['cadastro_tentativas'] > 0): ?>
        <div class="alert warning">
            <p>Atenção: <?= $tentativas_restantes ?> tentativa(s) restante(s) antes do bloqueio temporário.</p>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" placeholder="Seu nome"
                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="sobrenome">Sobrenome:</label>
            <input type="text" id="sobrenome" name="sobrenome" placeholder="Seu sobrenome"
                   value="<?= htmlspecialchars($_POST['sobrenome'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" placeholder="Seu e-mail"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" placeholder="Mínimo 8 caracteres"
                   required minlength="8">
        </div>

        <button type="submit" class="btn" <?= $bloqueado ? 'disabled' : '' ?>>Cadastrar</button>

        <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
    </form>
</div>
</body>
</html>