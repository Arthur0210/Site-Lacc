<?php
require_once "../Login/conexao.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    http_response_code(404);
    echo "Postagem não encontrada.";
    exit();
}

$stmt = $mysqli->prepare("
    SELECT p.titulo, p.conteudo, p.data, p.visualizacoes, 
           u.nome, u.sobrenome 
    FROM postagens p 
    JOIN usuario u ON p.usuario_id = u.codigo 
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    http_response_code(404);
    echo "Erro 404: Postagem não encontrada.";
    exit();
}

$post = $result->fetch_assoc();
$stmt->close();

$updateViews = $mysqli->prepare("
    UPDATE postagens 
    SET visualizacoes = visualizacoes + 1 
    WHERE id = ?
");
$updateViews->bind_param("i", $id);
$updateViews->execute();
$updateViews->close();

$visualizacoes = ($post['visualizacoes'] ?? 0) + 1;

$tags_permitidas = '<b><i><u><br>';
$conteudo_seguro = nl2br(strip_tags($post['conteudo'], $tags_permitidas));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['titulo']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <div class="post">
        <h1><?= htmlspecialchars($post['titulo']) ?></h1>
        <p>
            <strong>Por:</strong>
            <?= htmlspecialchars($post['nome']) ?>
            <?= htmlspecialchars($post['sobrenome']) ?>
        </p>

        <p>
            <em>Publicado em <?= date("d/m/Y", strtotime($post['data'])) ?></em>
            — <?= $visualizacoes ?> visualizações
        </p>
        <hr>
        <div class="conteudo">
            <?= $conteudo_seguro ?>
        </div>
    </div>
    <p><a class="saiba-mais" href="todas_postagens.php">← Voltar</a></p>
</main>
</body>
</html>