<?php
session_start();
require_once '../Login/conexao.php';

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] ?? '') !== 'admin') {
    die("<h2 style='text-align: center; color: red; margin-top: 50px;'>Acesso Negado. Apenas coordenadores podem acessar esta área.</h2>");
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');
    $autor_id = $_SESSION['usuario']; 

    if (empty($titulo) || empty($conteudo)) {
        $mensagem = "<p style='color: red; font-weight: bold;'>Por favor, preencha o título e o conteúdo.</p>";
    } else {
        $sql = "INSERT INTO paginas_pesquisa (titulo, conteudo, autor_id) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssi", $titulo, $conteudo, $autor_id);
        
        if ($stmt->execute()) {
            $novo_id = $stmt->insert_id;
            $mensagem = "<p style='color: green; font-weight: bold;'>Pesquisa publicada com sucesso! <a href='pesquisa_view.php?id=$novo_id'>Clique aqui para ver a página.</a></p>";
        } else {
            $mensagem = "<p style='color: red; font-weight: bold;'>Erro ao salvar no banco de dados.</p>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Criar Nova Pesquisa - LACC</title>
</head>
<body>
    
    <?php include 'header.html'; ?>

    <main>
        <section class="post">
            <h2 class="section-title" style="margin-bottom: 20px; text-align: left;">Criar Nova Pesquisa</h2>
            
            <?php echo $mensagem; ?>

            <form action="criar_pesquisas.php" method="POST" style="box-shadow: none; padding: 0; max-width: 100%;">
                
                <label for="titulo">Título da Pesquisa:</label>
                <input type="text" id="titulo" name="titulo" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--cor-borda); border-radius: 8px; font-family: var(--fonte-titulo); font-size: 1.2rem;">

                <label for="conteudo">Texto da Pesquisa:</label>

                <div id="toolbar-simples" style="margin-bottom: 10px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" onclick="inserirTag('<b>', '</b>')" class="btn" style="padding: 6px 12px; font-size: 0.85rem;">Negrito</button>
                    <button type="button" onclick="inserirTag('<i>', '</i>')" class="btn" style="padding: 6px 12px; font-size: 0.85rem;">Itálico</button>
                    <button type="button" onclick="inserirTag('<u>', '</u>')" class="btn" style="padding: 6px 12px; font-size: 0.85rem;">Sublinhado</button>
                    <button type="button" onclick="inserirTag('<h3>', '</h3>')" class="btn" style="padding: 6px 12px; font-size: 0.85rem;">Subtítulo</button>
                    <button type="button" onclick="inserirTag('<br>', '')" class="btn" style="padding: 6px 12px; font-size: 0.85rem;">Pular Linha</button>
                </div>

                <textarea id="conteudo" name="conteudo" rows="15" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--cor-borda); border-radius: 8px; font-family: var(--fonte-corpo); line-height: 1.6; resize: vertical;"></textarea>

                <script>
                function inserirTag(tagInicio, tagFim) {
                    const textarea = document.getElementById('conteudo');
                    const inicioSelecionado = textarea.selectionStart;
                    const fimSelecionado = textarea.selectionEnd;
                    const textoSelecionado = textarea.value.substring(inicioSelecionado, fimSelecionado);

                    textarea.value = textarea.value.substring(0, inicioSelecionado) 
                                + tagInicio + textoSelecionado + tagFim 
                                + textarea.value.substring(fimSelecionado);

                    textarea.focus();

                    if (textoSelecionado.length === 0) {
                        textarea.selectionEnd = inicioSelecionado + tagInicio.length;
                    } else {
                        textarea.selectionEnd = inicioSelecionado + tagInicio.length + textoSelecionado.length + tagFim.length;
                    }
                }
                </script>

                <button type="submit" class="btn">Publicar Pesquisa</button>
            </form>
        </section>
    </main>

    <?php include 'footer.html'; ?>
    
</body>
</html>