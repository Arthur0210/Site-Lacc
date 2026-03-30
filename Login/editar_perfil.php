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
                <label for="upload-foto-edicao" class="perfil-foto-area-edicao" title="Clique para trocar a foto">
                    <img src="<?= htmlspecialchars($caminho_foto) ?>" alt="Foto de Perfil" class="foto-redonda">
                </label>
                
                <form action="processa_foto.php" method="POST" enctype="multipart/form-data" id="form-foto-edicao" style="display: none;">
                    <input type="file" id="upload-foto-edicao" name="nova_foto" accept="image/png, image/jpeg" onchange="document.getElementById('form-foto-edicao').submit();">
                </form>
                
                <small style="color: #888; margin-top: 10px; display: block;">Clique na foto para alterar</small>
            </div>

            <div class="perfil-info-area" style="width: 100%;">
                
                <form action="atualizar_dados.php" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                    
                    <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 10px;">
                        <input type="text" name="nome" value="<?= htmlspecialchars($dados_user['nome']) ?>" placeholder="Nome" style="font-size: 1.5rem; font-weight: bold; border: none; border-bottom: 1px dashed #ccc; padding: 2px 0; width: 140px; outline: none; color: var(--cor-texto); background: transparent;">
                        <input type="text" name="sobrenome" value="<?= htmlspecialchars($dados_user['sobrenome']) ?>" placeholder="Sobrenome" style="font-size: 1.5rem; font-weight: bold; border: none; border-bottom: 1px dashed #ccc; padding: 2px 0; flex-grow: 1; outline: none; color: var(--cor-texto); background: transparent;">
                    </div>

                    <div style="display: flex; align-items: center;">
                        <strong style="width: 90px; color: #555;">E-mail:</strong> 
                        <input type="email" name="email" value="<?= htmlspecialchars($dados_user['email']) ?>" style="border: none; border-bottom: 1px dashed #ccc; padding: 4px 0; flex-grow: 1; outline: none; color: var(--cor-texto); background: transparent;">
                    </div>
                    
                    <div style="display: flex; align-items: center;">
                        <strong style="width: 90px; color: #555;">Vínculo:</strong> 
                        <input type="text" name="vinculo" value="<?= htmlspecialchars($dados_user['vinculo'] ?? '') ?>" placeholder="Ex: Bolsista, Estagiário..." style="border: none; border-bottom: 1px dashed #ccc; padding: 4px 0; flex-grow: 1; outline: none; color: var(--cor-texto); background: transparent;">
                    </div>
                    
                    <div style="display: flex; align-items: center;">
                        <strong style="width: 90px; color: #555;">Curso:</strong> 
                        <input type="text" name="curso" value="<?= htmlspecialchars($dados_user['curso'] ?? '') ?>" placeholder="Ex: Automação Industrial..." style="border: none; border-bottom: 1px dashed #ccc; padding: 4px 0; flex-grow: 1; outline: none; color: var(--cor-texto); background: transparent;">
                    </div>
                    
                    <div style="display: flex; align-items: center;">
                        <strong style="width: 90px; color: #555;">Interesses:</strong> 
                        <input type="text" name="interesses" value="<?= htmlspecialchars($dados_user['interesses'] ?? '') ?>" placeholder="Ex: Robótica, C++..." style="border: none; border-bottom: 1px dashed #ccc; padding: 4px 0; flex-grow: 1; outline: none; color: var(--cor-texto); background: transparent;">
                    </div>

                    <div style="display: flex; align-items: center;">
                        <strong style="width: 90px; color: #555;">Lattes:</strong> 
                        <input type="url" name="link_lattes" value="<?= htmlspecialchars($dados_user['link_lattes'] ?? '') ?>" placeholder="Link do seu currículo Lattes" style="border: none; border-bottom: 1px dashed #ccc; padding: 4px 0; flex-grow: 1; outline: none; color: var(--cor-texto); background: transparent;">
                    </div>

                    <div style="margin-top: 15px; display: flex; gap: 15px; align-items: center;">
                        <button type="submit" class="btn" style="padding: 8px 20px;">Salvar Alterações</button>
                        <a href="sucesso.php" style="color: #888; text-decoration: none; font-size: 0.95rem; transition: color 0.3s;">Cancelar</a>
                    </div>
                </form>
            </div>
                </form>
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
                            
                            <a href="../Paginas/editar_post.php?id=<?= $post['id'] ?>" class="btn-editar-post">Editar postagem</a>
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