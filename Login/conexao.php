<?php

require_once __DIR__ . '/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    error_log("Erro na conexão: " . $mysqli->connect_error);
    die("Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}

$mysqli->set_charset("utf8mb4");