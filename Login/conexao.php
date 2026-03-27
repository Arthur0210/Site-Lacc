<?php
// Inclui o novo arquivo de configuração
require_once __DIR__ . '/config.php';

// Conexão com tratamento de erros usando as constantes
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    error_log("Erro na conexão: " . $mysqli->connect_error);
    die("Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}

// Define o charset para UTF-8 para suportar caracteres especiais
$mysqli->set_charset("utf8mb4");