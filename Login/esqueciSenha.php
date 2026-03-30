<?php
require_once("conexao.php");

$erro = [];
$sucesso = "";

if (isset($_POST['ok'])) {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = "E-mail inválido.";
    } else {
        // 1. Buscamos o código e o horário do último envio
        $stmt = $mysqli->prepare("SELECT codigo, ultimo_envio FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0) {
            $erro[] = "Este e-mail não está cadastrado em nosso sistema.";
        } else {
            $usuario = $res->fetch_assoc();

            // --- INÍCIO DA VERIFICAÇÃO DE LIMITE ---
            $agora = new DateTime();
            $pode_enviar = true;

            if ($usuario['ultimo_envio'] !== null) {
                $ultimoEnvio = new DateTime($usuario['ultimo_envio']);
                $intervalo = $ultimoEnvio->diff($agora);

                // Limite de 5 minutos entre envios para evitar spam
                if ($intervalo->i < 5 && $intervalo->y == 0 && $intervalo->m == 0 && $intervalo->d == 0 && $intervalo->h == 0) {
                    $aguarde = 5 - $intervalo->i;
                    $erro[] = "Você já solicitou um link recentemente. Por favor, aguarde {$aguarde} minuto(s) para tentar novamente.";
                    $pode_enviar = false;
                }
            }
            // --- FIM DA VERIFICAÇÃO DE LIMITE ---

            if ($pode_enviar) {
                $token = bin2hex(random_bytes(32));
                $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));
                $agora_str = $agora->format("Y-m-d H:i:s");

                // Atualizamos o token, a expiração e o timestamp do último envio
                $stmt_update = $mysqli->prepare("UPDATE usuario SET token_redefinicao = ?, token_expira = ?, ultimo_envio = ? WHERE email = ?");
                $stmt_update->bind_param("ssss", $token, $expira, $agora_str, $email);

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