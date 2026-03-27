<?php
session_start();
require_once "conexao.php"; // A variável $mysqli vem daqui
global $mysqli;

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
    <link rel="stylesheet" href="https://unpkg.com/pell/dist/pell.min.css">

    <script src="https://unpkg.com/pell/dist/pell.min.js" defer></script>

    <script src="../js/postagens.js" defer></script>
</head>
<body>
<?php include '../Paginas/header.html'; ?>

<main class="content-main">
    <div class="user-info">
        <h1>Minha Conta</h1>
        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?> <?= htmlspecialchars($usuario['sobrenome']) ?></p>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p>
            <a class="btn" href="editar_conta.php">Editar perfil</a>
            <a class="btn" href="logout.php">Sair</a>
        </p>
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

        if ($result_posts->num_rows > 0):
            while ($post = $result_posts->fetch_assoc()): ?>
                <div class='post-item'>
                    <h3><?= htmlspecialchars($post['titulo']) ?></h3>
                    <small><i>Publicado em: <?= date("d/m/Y H:i", strtotime($post['data'])) ?></i></small>
                    <div class='post-content'><?= $post['conteudo'] ?></div>
                </div>
            <?php endwhile;
        else: ?>
            <p>Você ainda não fez nenhuma postagem.</p>
        <?php endif;
        $stmt_posts->close();
        ?>
    </div>

    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
        <div id="modal">
            <div id="modal-content">
                <span id="modal-close">&times;</span>

                <h2>Nova Postagem</h2>

                <div class="form-group">
                    <label for="titulo">Título:</label>
                    <input type="text" id="titulo" placeholder="Título da sua postagem" required>
                </div>

                <div class="form-group-upload">
                    <label for="poster">Pôster da Postagem (Opcional):</label>
                    <input type="file" id="poster" name="poster" accept="image/jpeg, image/png, image/webp">
                </div>

                <div id="editor-pell" class="pell"></div>

                <button id="btn-publicar" class="btn">Publicar</button>
                <div id="form-msg"></div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include '../Paginas/footer.html'; ?>
</body>
</html>