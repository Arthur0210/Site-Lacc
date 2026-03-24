<?php
session_start();

// >>> NOVO BLOCO DE CÓDIGO <<<
// Se o usuário já estiver logado, redireciona para a página de sucesso.
// Isso impede que um usuário logado acesse a página de cadastro.
if (isset($_SESSION['usuario'])) {
    header("Location: sucesso.php");
    exit(); // Encerra o script para garantir que o redirecionamento ocorra.
}
// >>> FIM DO NOVO BLOCO <<<

include("conexao.php");

$erro = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $sobrenome = trim($_POST['sobrenome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Validações
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
                // Após o cadastro, loga o usuário automaticamente
                $novo_id = $mysqli->insert_id;
                $_SESSION['usuario'] = $novo_id;
                // Busca o tipo do novo usuário (será 'comum' por padrão)
                $stmt_tipo = $mysqli->prepare("SELECT tipo_usuario FROM usuario WHERE codigo = ?");
                $stmt_tipo->bind_param("i", $novo_id);
                $stmt_tipo->execute();
                $resultado_tipo = $stmt_tipo->get_result()->fetch_assoc();
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
}
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

        <form method="POST" action="">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" placeholder="Seu nome" required>
            </div>
            
            <div class="form-group">
                <label for="sobrenome">Sobrenome:</label>
                <input type="text" id="sobrenome" name="sobrenome" placeholder="Seu sobrenome" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="Seu e-mail" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>
            
            <button type="submit" class="btn">Cadastrar</button>
            
            <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
        </form>
    </div>
</body>
</html>