<?php
include("conexao.php");

$erro = [];
$sucesso = ""; 
$token = $_GET['token'] ?? '';

if (empty($token)) { 
    header("Location: login.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $mysqli;
    $token = $_POST['token'];
    $nova = trim($_POST['nova_senha'] ?? '');
    $confirma = trim($_POST['confirma_senha'] ?? '');

    if (empty($nova) || empty($confirma)) {
        $erro[] = "Preencha ambos os campos de senha.";
    } elseif ($nova !== $confirma) {
        $erro[] = "As senhas não coincidem.";
    } elseif (strlen($nova) < 8) { 
        $erro[] = "A senha deve ter pelo menos 8 caracteres.";
    } else {
        $stmt = $mysqli->prepare("SELECT codigo FROM usuario WHERE token_redefinicao = ? AND token_expira > NOW()"); 
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0) {
            $erro[] = "Token inválido ou expirado. Solicite um novo link.";
        } else {
            $hash = password_hash($nova, PASSWORD_DEFAULT);
            $stmt_upd = $mysqli->prepare("UPDATE usuario SET senha = ?, token_redefinicao = NULL, token_expira = NULL WHERE token_redefinicao = ?"); // Prepara atualização no banco
            $stmt_upd->bind_param("ss", $hash, $token);

            if ($stmt_upd->execute()) {
                $sucesso = "Senha redefinida com sucesso. Agora você pode fazer login.";
            } else {
                $erro[] = "Erro ao atualizar a senha. Por favor, tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nova Senha</title>
    <link rel="stylesheet" href="../Paginas/style.css">
</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>

        <?php if (!empty($erro)): ?>
            <div class="alert error">
                <?php foreach ($erro as $msg): ?>
                    <p><?= htmlspecialchars($msg) ?></p>
                <?php endforeach; ?>
            </div>
        <?php elseif ($sucesso): ?>
            <div class="alert success">
                <p><?= htmlspecialchars($sucesso) ?></p>
                <p><a href="login.php">Ir para página de login</a></p>
            </div>
        <?php endif; ?>

        <?php if (!$sucesso): ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label for="nova_senha">Nova senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>
            
            <div class="form-group">
                <label for="confirma_senha">Confirme a senha:</label>
                <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Digite novamente" required minlength="8">
            </div>
            
            <button type="submit" class="btn">Redefinir senha</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
