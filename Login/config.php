<?php
$baseUrlPath = dirname($_SERVER['SCRIPT_NAME']);
$projectRoot = preg_replace('/\/Login$/', '', $baseUrlPath);
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . $projectRoot);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Arthursm0210');
define('DB_NAME', 'login');

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'arthurssmagalhaes@gmail.com');
define('SMTP_PASS', 'zsml tdyv zzvb hblx');
define('SMTP_PORT', 587);
define('SMTP_FROM_EMAIL', 'arthurssmagalhaes@gmail.com');
define('SMTP_FROM_NAME', 'Suporte LACC');