<?php
session_start();
require_once "conexao.php"; 

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario'];

$stmt = $mysqli->prepare("SELECT nome, sobrenome, email, foto, vinculo, curso, interesses, link_lattes FROM usuario WHERE codigo = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$dados_user = $stmt->get_result()->fetch_assoc();

$caminho_foto = !empty($dados_user['foto']) ? "../uploads/perfil/" . $dados_user['foto'] : "../Imagens/placeholder-membro.png";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minha Conta - LACC</title>
    <link rel="stylesheet" href="../Paginas/style.css">
    <style>
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .post-card {
            background: #fff;
            border: 1px solid var(--cor-borda);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .post-card h4 { margin-top: 0; margin-bottom: 10px; color: var(--cor-texto); font-size: 1.2rem; }
        .post-card p { font-size: 0.9rem; color: #666; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; }
        .btn-ler { margin-top: 15px; text-align: center; display: block; text-decoration: none; padding: 8px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include '../Paginas/header.html'; ?>

    <main>
        
        <section class="perfil-container">
            <div class="perfil-foto-area">
                <img src="<?= htmlspecialchars($caminho_foto) ?>" alt="Foto de Perfil" class="foto-redonda">
                <a href="editar_perfil.php" class="btn btn-editar">Editar Perfil</a>
            </div>

            <div class="perfil-info-area">
                <h2><?= htmlspecialchars($dados_user['nome'] . " " . $dados_user['sobrenome']) ?></h2>
                <p><strong>E-mail:</strong> <?= htmlspecialchars($dados_user['email']) ?></p>
                
                <p><strong>Vínculo:</strong> <?= htmlspecialchars($dados_user['vinculo'] ?? 'Não informado') ?></p>
                <p><strong>Curso:</strong> <?= htmlspecialchars($dados_user['curso'] ?? 'Não informado') ?></p>
                <p><strong>Interesses:</strong> <?= htmlspecialchars($dados_user['interesses'] ?? 'Não informado') ?></p>
                
                <?php if (!empty($dados_user['link_lattes'])): ?>
                    <div style="margin-top: 15px;">
                        <a href="<?= htmlspecialchars($dados_user['link_lattes']) ?>" target="_blank" style="color: var(--cor-primaria); text-decoration: none; font-weight: bold;">🔗 Currículo Lattes</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="minhas-postagens-section">
            <h3>Minhas Postagens</h3>
            
            <div class="posts-grid">
                <?php
                $stmt_posts = $mysqli->prepare("SELECT id, titulo, conteudo, data FROM postagens WHERE usuario_id = ? ORDER BY data DESC");
                $stmt_posts->bind_param("i", $id_usuario);
                $stmt_posts->execute();
                $result_posts = $stmt_posts->get_result();

                if ($result_posts->num_rows > 0):
                    while ($post = $result_posts->fetch_assoc()): ?>
                        
                        <div class='post-card'>
                            <div>
                                <h4><?= htmlspecialchars($post['titulo']) ?></h4>
                                <small style="color: #999;">Publicado em: <?= date("d/m/Y", strtotime($post['data'])) ?></small>
                                <p><?= htmlspecialchars(substr(strip_tags($post['conteudo']), 0, 120)) ?>...</p>
                            </div>
                            
                            <a href="../Paginas/ver_post.php?id=<?= $post['id'] ?>" class="btn btn-ler">Ler postagem completa</a>
                        </div>

                    <?php endwhile;
                else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: #666; font-size: 1.1rem; padding: 20px;">Você ainda não fez nenhuma postagem.</p>
                <?php endif;
                
                $stmt_posts->close();
                ?>
            </div>
        </section>

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