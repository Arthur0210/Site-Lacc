<?php
require_once "../Login/conexao.php";

// Define a lista de ordenações permitidas para segurança
$ordensPermitidas = [
    'recentes' => 'p.data DESC',
    'populares' => 'p.visualizacoes DESC',
    'antigas' => 'p.data ASC'
];

$ordem = $_GET['ordem'] ?? 'recentes';

// Define $orderBy apenas se a $ordem for uma chave válida, senão, usa o padrão
$orderBy = $ordensPermitidas[$ordem] ?? $ordensPermitidas['recentes'];

$sql = "
    SELECT p.id, p.titulo, p.conteudo, p.data, p.visualizacoes, u.nome, u.sobrenome
    FROM postagens p
    JOIN usuario u ON p.usuario_id = u.codigo
    ORDER BY $orderBy
";
$resultado = $mysqli->query($sql);

if (!$resultado) {
    die("Erro na consulta: " . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Todas as Postagens</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <h1>Postagens do Site</h1>
        <p><a class="saiba-mais" href="index.php">← Voltar para o Início</a></p>

        <div style="margin-bottom: 20px;">
            <a href="?ordem=recentes"><button>Mais Recentes</button></a>
            <a href="?ordem=populares"><button>Mais Populares</button></a>
            <a href="?ordem=antigas"><button>Mais Antigas</button></a>
        </div>

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($post = $resultado->fetch_assoc()): ?>
                <div class="post">
                    <h2><?= htmlspecialchars($post['titulo']) ?></h2>
                    <small>
                        Por <?= htmlspecialchars($post['nome'] . ' ' . $post['sobrenome']) ?>
                        em <?= date("d/m/Y", strtotime($post['data'])) ?> — <?= $post['visualizacoes'] ?? 0 ?> visualizações
                    </small>
                    <div class="conteudo-post">
                        <?= nl2br(strip_tags(substr($post['conteudo'], 0, 500))) . '...' ?>
                    </div>
                    <a class="saiba-mais" href="ver_post.php?id=<?= $post['id'] ?>">Leia mais</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma postagem foi encontrada.</p>
        <?php endif; ?>
    </main>
</body>
</html>