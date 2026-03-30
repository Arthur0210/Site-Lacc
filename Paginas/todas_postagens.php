<?php
require_once "../Login/conexao.php";

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/login.php");
    exit();
}

$ordensPermitidas = [
    'recentes'  => 'p.data DESC',
    'populares' => 'p.visualizacoes DESC',
    'antigas'   => 'p.data ASC'
];
$ordem   = $_GET['ordem'] ?? 'recentes';
$orderBy = $ordensPermitidas[$ordem] ?? $ordensPermitidas['recentes'];

$busca = trim($_GET['busca'] ?? '');
$filtro_tempo = $_GET['tempo'] ?? 'todos';

$por_pagina   = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset       = ($pagina_atual - 1) * $por_pagina;

$where_sql = "WHERE 1=1";
$params = [];
$tipos = "";

if (!empty($busca)) {
    $where_sql .= " AND (p.titulo LIKE ? OR p.conteudo LIKE ?)";
    $termo = "%" . $busca . "%"; 
    $params[] = $termo;
    $params[] = $termo;
    $tipos .= "ss";
}

if ($filtro_tempo === '30dias') {
    $where_sql .= " AND p.data >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($filtro_tempo === 'ano') {
    $where_sql .= " AND YEAR(p.data) = YEAR(NOW())";
}

$sql_count = "SELECT COUNT(*) AS total FROM postagens p $where_sql";
$stmt_count = $mysqli->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($tipos, ...$params); 
}
$stmt_count->execute();
$total_posts = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = (int) ceil($total_posts / $por_pagina);
$stmt_count->close();

$sql_posts = "
    SELECT p.id, p.titulo, p.conteudo, p.data, p.visualizacoes,
           u.nome, u.sobrenome
    FROM postagens p
    JOIN usuario u ON p.usuario_id = u.codigo
    $where_sql
    ORDER BY $orderBy
    LIMIT ? OFFSET ?
";

$stmt = $mysqli->prepare($sql_posts);
$params_finais = $params;
$params_finais[] = $por_pagina;
$params_finais[] = $offset;
$tipos_finais = $tipos . "ii";

if (!empty($params_finais)) {
    $stmt->bind_param($tipos_finais, ...$params_finais);
}
$stmt->execute();
$resultado = $stmt->get_result();

function linkPagina($pag, $ordem, $busca, $tempo) {
    return "?ordem=" . urlencode($ordem) . "&busca=" . urlencode($busca) . "&tempo=" . urlencode($tempo) . "&pagina=" . $pag;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas as Postagens - LACC</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'header.html'; ?>

    <main style="max-width: 1400px;">
        
        <div class="timeline-container">
            
            <aside class="timeline-sidebar">
                
                <h3>Pesquisar</h3>
                <form action="todas_postagens.php" method="GET" class="search-form">
                    <input type="hidden" name="ordem" value="<?= htmlspecialchars($ordem) ?>">
                    <input type="hidden" name="tempo" value="<?= htmlspecialchars($filtro_tempo) ?>">
                    <input type="text" name="busca" placeholder="Buscar publicações..." value="<?= htmlspecialchars($busca) ?>">
                </form>
                
                <h3 style="margin-top: 20px;">Ordenar por</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?= linkPagina(1, 'recentes', $busca, $filtro_tempo) ?>" class="btn" style="background-color: <?= $ordem == 'recentes' ? 'var(--cor-primaria)' : 'var(--cor-secundaria)' ?>;">Mais Recentes</a>
                    <a href="<?= linkPagina(1, 'populares', $busca, $filtro_tempo) ?>" class="btn" style="background-color: <?= $ordem == 'populares' ? 'var(--cor-primaria)' : 'var(--cor-secundaria)' ?>;">Mais Populares</a>
                    <a href="<?= linkPagina(1, 'antigas', $busca, $filtro_tempo) ?>" class="btn" style="background-color: <?= $ordem == 'antigas' ? 'var(--cor-primaria)' : 'var(--cor-secundaria)' ?>;">Mais Antigas</a>
                </div>

                <h3 style="margin-top: 35px; font-size: 1.2rem;">Filtros</h3>
                
                <form action="todas_postagens.php" method="GET">
                    <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">
                    <input type="hidden" name="ordem" value="<?= htmlspecialchars($ordem) ?>">

                    <label for="area" class="label-filtro">Área Científica</label>
                    <select name="area" id="area" class="select-filtro" onchange="this.form.submit()">
                        <option value="todas">Todas as áreas</option>
                        <option value="automacao">Automação Industrial</option>
                        <option value="computacao">Ciência da Computação</option>
                        <option value="robotica">Robótica</option>
                    </select>

                    <label for="tempo" class="label-filtro">Ano de Publicação</label>
                    <select name="tempo" id="tempo" class="select-filtro" onchange="this.form.submit()">
                        <option value="todos" <?= $filtro_tempo == 'todos' ? 'selected' : '' ?>>Todos os anos</option>
                        <option value="ano" <?= $filtro_tempo == 'ano' ? 'selected' : '' ?>>Este ano</option>
                        <option value="30dias" <?= $filtro_tempo == '30dias' ? 'selected' : '' ?>>Últimos 30 dias</option>
                    </select>
                </form>

            </aside>

            <section class="timeline-content">
                <h2 class="section-title" style="text-align: left; margin-bottom: 30px;">
                    <?= !empty($busca) ? 'Resultados para "' . htmlspecialchars($busca) . '"' : 'Publicações do Site' ?>
                </h2>
                
                <?php if ($resultado->num_rows > 0): ?>
                    <div class="timeline-grid">
                        
                        <?php while ($post = $resultado->fetch_assoc()): ?>
                            <article class="post-item">
                                
                                <?php /* $caminhoImagem = !empty($post['poster']) ? '../' . $post['poster'] : '../Imagens/placeholder-membro.png'; */ ?>
                                <img src="banner_futuro.jpg" alt="Banner da publicação (Em breve)" class="post-img">
                                
                                <div class="post-item-body">
                                    <h3><?= htmlspecialchars($post['titulo']) ?></h3>
                                    <small>
                                        Por <?= htmlspecialchars($post['nome'] . ' ' . $post['sobrenome']) ?>
                                        em <?= date("d/m/Y", strtotime($post['data'])) ?>
                                        <br>👁 <?= $post['visualizacoes'] ?? 0 ?> visualizações
                                    </small>
                                    
                                    <p>
                                        <?= htmlspecialchars(substr(strip_tags($post['conteudo']), 0, 150)) ?>...
                                    </p>
                                    
                                    <a href="ver_post.php?id=<?= (int) $post['id'] ?>" class="btn btn-ler-mais">Ler mais</a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        
                    </div>

                    <?php if ($total_paginas > 1): ?>
                        <nav style="margin-top: 40px; display: flex; justify-content: center; gap: 10px;">
                            <?php if ($pagina_atual > 1): ?>
                                <a href="<?= linkPagina($pagina_atual - 1, $ordem, $busca, $filtro_tempo) ?>" class="btn" style="padding: 5px 15px;">←</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <?php if ($i === $pagina_atual): ?>
                                    <strong class="btn" style="background-color: var(--cor-primaria); padding: 5px 15px; cursor: default;"><?= $i ?></strong>
                                <?php else: ?>
                                    <a href="<?= linkPagina($i, $ordem, $busca, $filtro_tempo) ?>" class="btn" style="background-color: var(--cor-fundo-claro); color: var(--cor-texto); padding: 5px 15px;"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagina_atual < $total_paginas): ?>
                                <a href="<?= linkPagina($pagina_atual + 1, $ordem, $busca, $filtro_tempo) ?>" class="btn" style="padding: 5px 15px;">→</a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid var(--cor-borda);">
                        <p style="text-align: center; font-size: 1.1rem;">
                            <?= !empty($busca) ? 'Nenhuma publicação encontrada com essas palavras.' : 'Nenhuma publicação encontrada.' ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php $stmt->close(); ?>
            </section>
        </div>
    </main>

    <?php include 'footer.html'; ?>
    
</body>
</html>