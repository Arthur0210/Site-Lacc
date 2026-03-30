<?php
include("conexao.php"); // Inclui arquivo de conexão com banco de dados

$erro = []; // Array para armazenar mensagens de erro
$sucesso = ""; // String para mensagem de sucesso
$token = $_GET['token'] ?? ''; // Pega token da URL, ou vazio se não existir

// Verifica token antes de mostrar o formulário
if (empty($token)) { // Se não existe token
    header("Location: login.php"); // Redireciona para login
    exit(); // Para execução do script
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Se formulário foi enviado via POST
    global $mysqli;
    $token = $_POST['token']; // Pega token enviado via POST
    $nova = $_POST['nova_senha'] ?? '';
    $confirma = $_POST['confirma_senha'] ?? '';

    if (empty($nova) || empty($confirma)) { // Se algum campo estiver vazio
        $erro[] = "Preencha ambos os campos de senha."; // Adiciona erro
    } elseif ($nova !== $confirma) { // Se senhas não coincidem
        $erro[] = "As senhas não coincidem."; // Adiciona erro
    } elseif (strlen($nova) < 8) { // Se senha menor que 8 caracteres
        $erro[] = "A senha deve ter pelo menos 8 caracteres."; // Adiciona erro
    } else { // Se passou validações
        // Verifica token no banco e se ainda não expirou
        $stmt = $mysqli->prepare("SELECT codigo FROM usuario WHERE token_redefinicao = ? AND token_expira > NOW()"); // Prepara query de forma segura
        $stmt->bind_param("s", $token); // Liga parâmetro string
        $stmt->execute(); // Executa query
        $res = $stmt->get_result(); // Pega resultado

        if ($res->num_rows == 0) { // Se não encontrou token válido
            $erro[] = "Token inválido ou expirado. Solicite um novo link."; // Adiciona erro
        } else { // Token válido
            $hash = password_hash($nova, PASSWORD_DEFAULT); // Cria hash da nova senha
            $stmt_upd = $mysqli->prepare("UPDATE usuario SET senha = ?, token_redefinicao = NULL, token_expira = NULL WHERE token_redefinicao = ?"); // Prepara atualização no banco
            $stmt_upd->bind_param("ss", $hash, $token); // Liga parâmetros

            if ($stmt_upd->execute()) { // Executa update
                $sucesso = "Senha redefinida com sucesso. Agora você pode fazer login."; // Mensagem de sucesso
            } else {
                $erro[] = "Erro ao atualizar a senha. Por favor, tente novamente."; // Mensagem de erro se falhar
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define charset UTF-8 -->
    <title>Nova Senha</title> <!-- Título da página -->
    <link rel="stylesheet" href="../Paginas/style.css"> <!-- o style.css -->
</head>
<body>
<div class="container">
    <h2>Redefinir Senha</h2> <!-- Título do formulário -->

    <?php if (!empty($erro)): ?> <!-- Se houver erros do PHP -->
        <div class="alert error">
            <?php foreach ($erro as $msg): ?> <!-- Para cada erro -->
                <p><?= htmlspecialchars($msg) ?></p> <!-- Exibe mensagem segura -->
            <?php endforeach; ?>
        </div>
    <?php elseif ($sucesso): ?> <!-- Se houver sucesso -->
        <div class="alert success">
            <p><?= htmlspecialchars($sucesso) ?></p> <!-- Exibe mensagem de sucesso -->
            <p><a href="login.php">Ir para página de login</a></p> <!-- Link para login -->
        </div>
    <?php endif; ?>

    <?php if (!$sucesso): ?> <!-- Mostra formulário apenas se não houver sucesso -->
        <form method="POST" onsubmit="return validarAntesDEnviar()"> <!-- Formulário POST -->
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>"> <!-- Token oculto -->

            <div class="form-group">
                <label for="nova_senha">Nova senha:</label> <!-- Label nova senha -->
                <input
                        type="password"
                        id="nova_senha"
                        name="nova_senha"
                        placeholder="Mínimo 8 caracteres"
                        required
                        minlength="8"
                        oninput="validarSenha()"> <!-- Valida ao vivo enquanto digita -->
            </div>

            <div class="form-group">
                <label for="confirma_senha">Confirme a senha:</label> <!-- Label confirmação -->
                <input
                        type="password"
                        id="confirma_senha"
                        name="confirma_senha"
                        placeholder="Digite novamente"
                        required
                        minlength="8"
                        oninput="validarSenha()"> <!-- Valida ao vivo enquanto digita -->
            </div>

            <!-- Área onde as mensagens ao vivo aparecem -->
            <div id="feedback-ao-vivo"></div>

            <button type="submit" id="btn-redefinir" class="btn" disabled>Redefinir senha</button> <!-- Botão começa desabilitado -->
        </form>
    <?php endif; ?>
</div>

<script>
    function validarSenha() {
        const nova     = document.getElementById('nova_senha').value;
        const confirma = document.getElementById('confirma_senha').value;
        const inputNova     = document.getElementById('nova_senha');
        const inputConfirma = document.getElementById('confirma_senha');
        const feedback = document.getElementById('feedback-ao-vivo');
        const botao    = document.getElementById('btn-redefinir');

        let erros = [];

        // Espelho exato das verificações PHP
        if (nova === '' || confirma === '') {
            erros.push("Preencha ambos os campos de senha.");
        } else if (nova !== confirma) {
            erros.push("As senhas não coincidem.");
        } else if (nova.length < 8) {
            erros.push("A senha deve ter pelo menos 8 caracteres.");
        }

        // Aplica borda colorida nos inputs
        if (nova.length > 0) {
            inputNova.classList.toggle('input-invalido', nova.length < 8);
            inputNova.classList.toggle('input-valido', nova.length >= 8);
        } else {
            inputNova.classList.remove('input-invalido', 'input-valido');
        }

        if (confirma.length > 0) {
            inputConfirma.classList.toggle('input-invalido', nova !== confirma);
            inputConfirma.classList.toggle('input-valido', nova === confirma && confirma.length >= 8);
        } else {
            inputConfirma.classList.remove('input-invalido', 'input-valido');
        }

        // Renderiza mensagens ou sucesso
        if (erros.length > 0) {
            feedback.innerHTML = erros.map(e => `<div class="erro-live">⚠ ${e}</div>`).join('');
            botao.disabled = true; // Bloqueia envio enquanto há erro
        } else if (nova.length > 0 && confirma.length > 0) {
            feedback.innerHTML = `<div class="sucesso-live">✔ Senhas válidas! Pode continuar.</div>`;
            botao.disabled = false; // Libera o botão
        } else {
            feedback.innerHTML = '';
            botao.disabled = true;
        }
    }

    // Barreira final: impede envio se JS identificar erro (segurança extra)
    function validarAntesDEnviar() {
        if (document.getElementById('nova_senha').value === '') return false;
        if (document.getElementById('confirma_senha').value === '') return false;
        if (document.getElementById('nova_senha').value !== document.getElementById('confirma_senha').value) return false;
        if (document.getElementById('nova_senha').value.length < 8) return false;
        return true;
    }
</script>
</body>
</html>