<?php
session_start();
require_once "conexao.php";

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verifica inatividade (30 minutos)
if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['ultimo_acesso'] = time();

$id = $_SESSION['usuario'];

// Busca dados do usuário
$stmt = $mysqli->prepare("SELECT nome, sobrenome, email FROM usuario WHERE codigo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minha Conta</title>
<link rel="stylesheet" href="../Paginas/style.css">
</head>
<body>
<?php include '../Paginas/header.html'; ?>

<main class="content-main">
    <div class="user-info">
        <h1>Minha Conta</h1>
        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?> <?= htmlspecialchars($usuario['sobrenome']) ?></p>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p><a class="btn" href="editar_conta.php">Editar perfil</a> <a class="btn" href="logout.php">Sair</a></p>
    </div>

    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
        <button id="btn-nova-postagem" class="btn">+ Nova Postagem</button>
    <?php endif; ?>

    <h2>Minhas Postagens</h2>
    <div id="postagens">
        <?php
        $stmt_posts = $mysqli->prepare("SELECT titulo, conteudo, data FROM postagens WHERE usuario_id = ? ORDER BY data DESC");
        $stmt_posts->bind_param("i", $id);
        $stmt_posts->execute();
        $result_posts = $stmt_posts->get_result();

        if ($result_posts->num_rows > 0) {
            while ($post = $result_posts->fetch_assoc()) {
                echo "<div class='post-item'>";
                echo "<h3>" . htmlspecialchars($post['titulo']) . "</h3>";
                echo "<small><i>Publicado em: " . date("d/m/Y H:i", strtotime($post['data'])) . "</i></small>";
                echo "<div class='post-content'>" . $post['conteudo'] . "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>Você ainda não fez nenhuma postagem.</p>";
        }
        $stmt_posts->close();
        ?>
    </div>

<?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
    <div id="modal">
        <div id="modal-content">
            <span id="modal-close">&times;</span>
            <h2>Nova Postagem</h2>
            
            <input type="text" id="titulo" placeholder="Título da sua postagem" required>
            
            <div class="form-group-upload">
                <label for="poster">Pôster da Postagem (Opcional):</label>
                <input type="file" id="poster" name="poster" accept="image/jpeg, image/png, image/webp">
            </div>
            
            <div id="toolbar">
                <button type="button" onclick="format('bold')"><b>B</b></button>
                <button type="button" onclick="format('italic')"><i>I</i></button>
                <button type="button" onclick="format('underline')"><u>U</u></button>
                <button type="button" onclick="format('insertOrderedList')">1.</button>
                <button type="button" onclick="format('insertUnorderedList')">•</button>
            </div>
            <div id="editor" contenteditable="true"></div>
            <button id="btn-publicar">Publicar</button>
            <div id="form-msg"></div>
        </div>
    </div>

    <script>
    const modal = document.getElementById('modal');
    const btnAbrir = document.getElementById('btn-nova-postagem');
    if (btnAbrir) {
        const btnFechar = document.getElementById('modal-close');
        const editor = document.getElementById('editor');

        btnAbrir.onclick = () => {
            document.getElementById('form-msg').textContent = '';
            document.getElementById('titulo').value = '';
            document.getElementById('poster').value = ''; 
            editor.innerHTML = '';
            modal.style.display = 'block';
        };
        btnFechar.onclick = () => { modal.style.display = 'none'; };
        window.onclick = (event) => { if (event.target == modal) modal.style.display = 'none'; };

        function format(cmd, value = null) { document.execCommand(cmd, false, value); }

        document.getElementById('btn-publicar').onclick = async () => {
            const titulo = document.getElementById('titulo').value.trim();
            const conteudo = editor.innerHTML.trim();
            const posterInput = document.getElementById('poster');
            const posterFile = posterInput.files[0]; 

            const formMsg = document.getElementById('form-msg');
            if (!titulo || !conteudo) {
                formMsg.textContent = "Título e conteúdo são obrigatórios.";
                formMsg.style.color = 'red';
                return;
            }
            const formData = new FormData();
            formData.append('titulo', titulo);
            formData.append('conteudo', conteudo);
            
            if (posterFile) {
                formData.append('poster', posterFile);
            }
            
            formMsg.textContent = 'Publicando...';
            formMsg.style.color = 'black';
            try {
                const response = await fetch('criar_postagem.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (response.ok && data.success) {
                    location.reload();
                } else {
                    formMsg.textContent = data.msg || "Ocorreu um erro ao publicar.";
                    formMsg.style.color = 'red';
                }
            } catch (error) {
                formMsg.textContent = 'Erro de conexão. Tente novamente.';
                formMsg.style.color = 'red';
            }
        };
    }
    </script>
<?php endif; ?>
</main>
<?php include '../Paginas/footer.html'; ?>
</body>
</html>