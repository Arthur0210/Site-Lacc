<?php
require_once("conexao.php");

$erro = [];
$sucesso = "";

if (isset($_POST['ok'])) {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = "E-mail inválido.";
    } else {
        $stmt = $mysqli->prepare("SELECT codigo FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0) {
            $erro[] = "Este e-mail não está cadastrado em nosso sistema.";
        } else {
            $token = bin2hex(random_bytes(32));
            $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $stmt_update = $mysqli->prepare("UPDATE usuario SET token_redefinicao = ?, token_expira = ? WHERE email = ?");
            $stmt_update->bind_param("sss", $token, $expira, $email);
            
            if ($stmt_update->execute()) {
                $link = BASE_URL . "/Login/nova_senha.php?token=$token";
                $assunto = "Redefinição de Senha - LACC";

                require_once 'enviar_email.php';

                if (enviarEmail($email, $assunto, $link)) {
                    $sucesso = "Um link de redefinição foi enviado para seu e-mail.";
                } else {
                    $erro[] = "Erro ao enviar o e-mail. Por favor, tente novamente mais tarde.";
                }
            } else {
                $erro[] = "Ocorreu um erro no banco de dados. Tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="../Paginas/style.css">
</head>
<body>
    <main>
        <form class="form-container">
            <h2>Recuperação de Senha</h2>

            <?php if (!empty($erro)): ?>
                <div class="alert error">
                    <?php foreach ($erro as $msg): ?>
                        <p><?= htmlspecialchars($msg) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($sucesso): ?>
                <div class="alert success">
                    <p><?= htmlspecialchars($sucesso) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST">
                <p>Digite seu e-mail para receber um link de redefinição de senha.</p>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="Seu e-mail cadastrado" required>
                <button type="submit" name="ok" class="btn">Enviar link de redefinição</button>
            </form>
            <p><a class="saiba-mais" href="login.php">← Voltar para login</a></p>
        </form>
    </main>
</body>
</html>