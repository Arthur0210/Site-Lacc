<?php
session_start(); // Inicia a sessão para usar $_SESSION

// Se o usuário já estiver logado, redireciona para a página de sucesso
if (isset($_SESSION['usuario'])) {
    header("Location: sucesso.php"); // Redireciona
    exit(); // Encerra script
}

include("conexao.php"); // Inclui arquivo de conexão com o banco

$erro = []; // Array para armazenar mensagens de erro

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); // Pega email e remove espaços
    $senha = trim($_POST['senha'] ?? ''); // Pega senha e remove espaços

    // Valida campos vazios
    if (empty($email) || empty($senha)) {
        $erro[] = "Preencha e-mail e senha."; // Adiciona erro
    } else {
        // Prepara query segura para evitar SQL Injection
        // MODIFICADO: Busca também a coluna 'tipo_usuario'
        $stmt = $mysqli->prepare("SELECT codigo, senha, tipo_usuario FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email); // Liga o parâmetro à query
        $stmt->execute(); // Executa a query
        $resultado = $stmt->get_result(); // Pega o resultado

        // Verifica se encontrou o usuário
        if ($resultado->num_rows === 0) {
            $erro[] = "Credenciais inválidas."; // Usuário não encontrado
        } else {
            $usuario = $resultado->fetch_assoc(); // Pega dados do usuário

            // Verifica se a senha informada bate com a hash armazenada
            if (password_verify($senha, $usuario['senha'])) {
                // Armazena dados do usuário na sessão
                $_SESSION['usuario'] = $usuario['codigo'];
                $_SESSION['ultimo_acesso'] = time();
                // ADICIONADO: Salva o tipo de usuário na sessão
                $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

                session_regenerate_id(true); // Evita session fixation

                header("Location: sucesso.php"); // Redireciona após login
                exit();
            } else {
                $erro[] = "Credenciais inválidas."; // Senha incorreta
            }
        }
        $stmt->close(); // Fecha statement
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../Paginas/style.css"> </head>
<body>
    <div class="container">

        <?php if (!empty($erro)): ?>
            <div class="alert error">
                <?php foreach ($erro as $msg): ?>
                    <p><?= htmlspecialchars($msg) ?></p> <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="Seu e-mail" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
            </div>
            
            <button type="submit" class="btn">Entrar</button>
            
            <div class="links">
                <a href="esqueciSenha.php">Esqueceu sua senha?</a>
                <a href="cadastrar.php">Criar nova conta</a>
            </div>
        </form>
    </div>
</body>
</html>