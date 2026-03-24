<?php
session_start();
require_once "conexao.php";

header('Content-Type: application/json');

// ADICIONADO: Bloco de segurança principal
// Se o usuário não está logado OU não tem um tipo de usuário definido OU não é 'admin', o acesso é bloqueado.
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(403); // Código HTTP para "Acesso Proibido"
    echo json_encode(['success' => false, 'msg' => 'Acesso negado. Você não tem permissão para postar.']);
    exit(); // Para a execução do script imediatamente.
}

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'msg' => 'Método não permitido.']);
    exit();
}

$id = $_SESSION['usuario'];
$titulo = trim($_POST['titulo'] ?? '');
$conteudo = trim($_POST['conteudo'] ?? '');

if (empty($titulo) || empty($conteudo)) {
    echo json_encode(['success' => false, 'msg' => 'Por favor, preencha o título e o conteúdo.']);
    exit();
}

// Permite um conjunto seguro de tags HTML para evitar XSS
$conteudo = strip_tags($conteudo, '<p><a><img><b><i><u><strong><em><ul><ol><li><br><h1><h2><h3><h4><h5><h6>');

// Insere a postagem no banco de dados
$stmt = $mysqli->prepare("INSERT INTO postagens (usuario_id, titulo, conteudo, data) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iss", $id, $titulo, $conteudo);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'msg' => 'Postagem publicada com sucesso!']);
} else {
    // Para depuração: error_log($stmt->error);
    echo json_encode(['success' => false, 'msg' => 'Erro ao salvar a postagem no banco de dados.']);
}

$stmt->close();
exit();