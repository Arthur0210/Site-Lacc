<?php
// ARQUIVO DE CONFIGURAÇÃO CENTRAL

// -- Configuração da URL Base --
// Detecta a URL raiz do seu projeto automaticamente.
// Altere '/LACC' se o nome da sua pasta principal for diferente.
$baseUrlPath = dirname($_SERVER['SCRIPT_NAME']);
// Remove subdiretórios como /Login para chegar à raiz do projeto
$projectRoot = preg_replace('/\/Login$/', '', $baseUrlPath);
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . $projectRoot);


// -- Configuração do Banco de Dados --
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Arthursm0210'); // Em produção, é melhor usar variáveis de ambiente
define('DB_NAME', 'login');

// -- Configuração de E-mail (PHPMailer) --
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'arthurssmagalhaes@gmail.com');
define('SMTP_PASS', 'zsml tdyv zzvb hblx'); // Em produção, use variáveis de ambiente
define('SMTP_PORT', 587);
define('SMTP_FROM_EMAIL', 'arthurssmagalhaes@gmail.com');
define('SMTP_FROM_NAME', 'Suporte LACC');