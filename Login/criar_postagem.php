<?php
global $mysqli;

use Random\RandomException;

session_start();
require_once "conexao.php"; // A variável $mysqli deve vir daqui

// Define o cabeçalho JSON imediatamente
header('Content-Type: application/json; charset=utf-8');

// 1. Verificação de Segurança (Controle de Acesso)
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'Acesso negado.']);
    exit();
}

// 2. Validação do Método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'msg' => 'Método inválido.']);
    exit();
}

// 3. Captura e Limpeza de Dados
$id = $_SESSION['usuario'];
$titulo = trim($_POST['titulo'] ?? '');
$conteudo = trim($_POST['conteudo'] ?? '');
$poster_path = null;

if (empty($titulo) || empty($conteudo) || $conteudo === '<p><br></p>') {
    echo json_encode(['success' => false, 'msg' => 'Título e conteúdo são obrigatórios.']);
    exit();
}

// 4. Tratamento do Upload de Imagem (Pôster)
if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['poster']['tmp_path'];
    $fileName = $_FILES['poster']['name'];
    $fileSize = $_FILES['poster']['size'];
    $fileType = $_FILES['poster']['type'];

    // Validações de segurança da imagem
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        echo json_encode(['success' => false, 'msg' => 'Extensão de imagem não permitida (use JPG, PNG ou WEBP).']);
        exit();
    }

    if ($fileSize > 2 * 1024 * 1024) { // Limite de 2MB
        echo json_encode(['success' => false, 'msg' => 'A imagem é muito grande (máximo 2MB).']);
        exit();
    }

    // Nome único para evitar sobrescrever arquivos
    try {
        $new_file_name = bin2hex(random_bytes(10)) . '.' . $file_ext;
    } catch (RandomException $e) {
        echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
    }
    $upload_dir = '../uploads/posters/';

    // Cria a pasta se não existir
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $dest_path = $upload_dir . $new_file_name;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        $poster_path = 'uploads/posters/' . $new_file_name;
    } else {
        echo json_encode(['success' => false, 'msg' => 'Erro ao mover o arquivo de imagem.']);
        exit();
    }
}

// 5. Filtro de Segurança HTML (XSS)
// Mantemos as tags básicas que o Pell utiliza
$tags_permitidas = '<p><a><b><i><u><strong><em><ul><ol><li><br><h1><h2><h3>';
$conteudo_limpo = strip_tags($conteudo, $tags_permitidas);

// 6. Inserção no Banco de Dados
try {
    // Adicionei a coluna 'poster' na query. Certifique-se de que ela existe na sua tabela!
    $sql = "INSERT INTO postagens (usuario_id, titulo, conteudo, poster, data) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("isss", $id, $titulo, $conteudo_limpo, $poster_path);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'msg' => 'Postagem publicada com sucesso!']);
    } else {
        throw new Exception("Falha na execução do banco.");
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'msg' => 'Erro interno ao salvar postagem.']);
}

exit();