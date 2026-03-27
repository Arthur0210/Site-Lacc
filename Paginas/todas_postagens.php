<?php
require_once "../Login/conexao.php";

// =============================================
// VERIFICAÇÃO DE LOGIN (remova se a página for pública)
// =============================================
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/login.php");
    exit();
}

// =============================================
// ORDENAÇÃO COM WHITELIST
// =============================================
$ordensPermitidas = [
    'recentes'  => 'p.data DESC',
    'populares' => 'p.visualizacoes DESC',
    'antigas'   => 'p.data ASC'
];

$ordem   = $_GET['ordem'] ?? 'recentes';
$orderBy = $ordensPermitidas[$ordem] ?? $ordensPermitidas['recentes'];

// =============================================
// PAGINAÇÃO
// =============================================
$por_pagina   = 10; // Quantidade de posts por página
$pagina_atual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset       = ($pagina_atual - 1) * $por_pagina;

// Conta o total de postagens para calcular o número de páginas
$total_result = $mysqli->query("SELECT COUNT(*) AS total FROM postagens");
$total_posts  = $total_result->fetch_assoc()['total'];
$total_paginas = (int) ceil($total_posts / $por_pagina);

// =============================================
// BUSCA AS POSTAGENS COM LIMIT E OFFSET
// =============================================
$stmt = $mysqli->prepare("
    SELECT p.id, p.titulo, p.conteudo, p.data, p.visualizacoes,
           u.nome, u.sobrenome
    FROM postagens p
    JOIN usuario u ON p.usuario_id = u.codigo
    ORDER BY $orderBy
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $por_pagina, $offset);
$stmt->execute();
$resultado = $stmt->get_result();

if (!$resultado) {
    // Loga o erro internamente e exibe mensagem genérica ao usuário
    error_log("Erro na consulta de postagens: " . $mysqli->error);
    die("Erro ao carregar as postagens. Por favor, tente novamente mais tarde.");
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

    <!-- Botões de ordenação -->
    <div style="margin-bottom: 20px;">
        <a href="?ordem=recentes&pagina=1"><button>Mais Recentes</button></a>
        <a href="?ordem=populares&pagina=1"><button>Mais Populares</button></a>
        <a href="?ordem=antigas&pagina=1"><button>Mais Antigas</button></a>
    </div>

    <?php if ($resultado->num_rows > 0): ?>
        <?php while ($post = $resultado->fetch_assoc()): ?>
            <div class="post">
                <h2><?= htmlspecialchars($post['titulo']) ?></h2>
                <small>
                    Por <?= htmlspecialchars($post['nome'] . ' ' . $post['sobrenome']) ?>
                    em <?= date("d/m/Y", strtotime($post['data'])) ?>
                    — <?= $post['visualizacoes'] ?? 0 ?> visualizações
                </small>
                <div class="conteudo-post">
                    <?= nl2br(strip_tags(substr($post['conteudo'], 0, 500))) ?>...
                </div>
                <!-- Cast para inteiro no ID do link -->
                <a class="saiba-mais" href="ver_post.php?id=<?= (int) $post['id'] ?>">Leia mais</a>
            </div>
        <?php endwhile; ?>

        <!-- =============================================
             PAGINAÇÃO
             ============================================= -->
        <?php if ($total_paginas > 1): ?>
            <nav style="margin-top: 30px;">
                <?php if ($pagina_atual > 1): ?>
                    <a href="?ordem=<?= htmlspecialchars($ordem) ?>&pagina=<?= $pagina_atual - 1 ?>">← Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <?php if ($i === $pagina_atual): ?>
                        <strong>[<?= $i ?>]</strong>
                    <?php else: ?>
                        <a href="?ordem=<?= htmlspecialchars($ordem) ?>&pagina=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?ordem=<?= htmlspecialchars($ordem) ?>&pagina=<?= $pagina_atual + 1 ?>">Próxima →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <p>Nenhuma postagem foi encontrada.</p>
    <?php endif; ?>

    <?php $stmt->close(); ?>
</main>
</body>
</html>