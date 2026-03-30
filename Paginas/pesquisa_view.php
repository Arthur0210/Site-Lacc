<?php
require_once '../Login/conexao.php'; 

$id_pesquisa = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titulo = "Pesquisa não encontrada";
$conteudo = "<p>A pesquisa que você está procurando não existe ou foi removida.</p>";

if ($id_pesquisa > 0) {
    $sql = "SELECT titulo, conteudo FROM paginas_pesquisa WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_pesquisa);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        $titulo = $row['titulo'];
        $conteudo = $row['conteudo'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo htmlspecialchars($titulo); ?> - LACC</title>
</head>
<body>
    
    <?php include 'header.html'; ?>

    <main>
        <section class="post">
            <h2 class="section-title" style="margin-bottom: 20px; text-align: left;">
                <?php echo htmlspecialchars($titulo); ?>
            </h2>
            
            <div class="post-content">
                <?php echo $conteudo; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.html'; ?>
    
</body>
</html>