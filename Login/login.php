<?php
global $mysqli;

use Random\RandomException;

session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: sucesso.php");
    exit();
}

include("conexao.php");

// =============================================
// PROTEÇÃO CONTRA BRUTE FORCE
// =============================================
$max_tentativas = 5;
$janela_tempo   = 15 * 60; // 15 minutos

if (!isset($_SESSION['login_tentativas'])) {
    $_SESSION['login_tentativas']    = 0;
    $_SESSION['login_primeiro_erro'] = null;
}

$bloqueado = false;
if ($_SESSION['login_tentativas'] >= $max_tentativas) {
    $tempo_passado = time() - $_SESSION['login_primeiro_erro'];

    if ($tempo_passado < $janela_tempo) {
        $bloqueado = true;
        $tempo_restante = ceil(($janela_tempo - $tempo_passado) / 60);
    } else {
        // Janela expirou: reseta contadores
        $_SESSION['login_tentativas']    = 0;
        $_SESSION['login_primeiro_erro'] = null;
    }
}

// =============================================
// GERAÇÃO DO TOKEN CSRF
// =============================================
if (empty($_SESSION['csrf_token_login'])) {
    try {
        $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));
    } catch (RandomException $e) {

    }
}

// Nota: usamos um token separado do cadastro ('csrf_token_login')
// para evitar conflito se o usuário abrir as duas páginas ao mesmo tempo.

$erro = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Valida token CSRF ---
    $token_recebido = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token_login'], $token_recebido)) {
        http_response_code(403);
        die("Requisição inválida. Por favor, recarregue a página e tente novamente.");
    }

    // Regenera o token após cada submissão válida
    try {
        $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));
    } catch (RandomException $e) {

    }

    // --- Verifica bloqueio ---
    if ($bloqueado) {
        $erro[] = "Muitas tentativas. Aguarde $tempo_restante minuto(s) para tentar novamente.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? ''; // Sem trim na senha!

        if (empty($email) || empty($senha)) {
            $erro[] = "Preencha e-mail e senha.";
        } else {
            $stmt = $mysqli->prepare("SELECT codigo, senha, tipo_usuario FROM usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 0) {
                $erro[] = "Credenciais inválidas.";
            } else {
                $usuario = $resultado->fetch_assoc();

                if (password_verify($senha, $usuario['senha'])) {
                    // Login bem-sucedido: reseta contadores
                    $_SESSION['login_tentativas']    = 0;
                    $_SESSION['login_primeiro_erro'] = null;

                    $_SESSION['usuario']       = $usuario['codigo'];
                    $_SESSION['ultimo_acesso'] = time();
                    $_SESSION['tipo_usuario']  = $usuario['tipo_usuario'];

                    session_regenerate_id(true);

                    header("Location: sucesso.php");
                    exit();
                } else {
                    $erro[] = "Credenciais inválidas.";
                }
            }
            $stmt->close();
        }

        // Incrementa tentativas se houve erro
        if (!empty($erro)) {
            $_SESSION['login_tentativas']++;

            if ($_SESSION['login_tentativas'] === 1) {
                $_SESSION['login_primeiro_erro'] = time();
            }

            if ($_SESSION['login_tentativas'] >= $max_tentativas) {
                $bloqueado = true;
                $tempo_restante = ceil($janela_tempo / 60);
                $erro = ["Muitas tentativas. Aguarde $tempo_restante minuto(s) para tentar novamente."];
            }
        }
    }
}

$tentativas_restantes = max(0, $max_tentativas - $_SESSION['login_tentativas']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

    <?php if (!$bloqueado && $_SESSION['login_tentativas'] > 0): ?>
        <div class="alert warning">
            <p>Atenção: <?= $tentativas_restantes ?> tentativa(s) restante(s) antes do bloqueio temporário.</p>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- Token CSRF oculto -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token_login']) ?>">

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" placeholder="Seu e-mail"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
        </div>

        <button type="submit" class="btn" <?= $bloqueado ? 'disabled' : '' ?>>Entrar</button>

        <div class="links">
            <a href="esqueciSenha.php">Esqueceu sua senha?</a>
            <a href="cadastrar.php">Criar nova conta</a>
        </div>
    </form>
</div>
</body>
</html>