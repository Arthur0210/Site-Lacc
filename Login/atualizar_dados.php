<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['usuario'];

    $nome       = trim($_POST['nome']);
    $sobrenome  = trim($_POST['sobrenome']);
    $email      = trim($_POST['email']);
    $vinculo    = trim($_POST['vinculo']);
    $curso      = trim($_POST['curso']);
    $interesses = trim($_POST['interesses']);
    $lattes     = trim($_POST['link_lattes']);

    $sql = "UPDATE usuario SET 
                nome = ?, 
                sobrenome = ?, 
                email = ?, 
                vinculo = ?, 
                curso = ?, 
                interesses = ?, 
                link_lattes = ? 
            WHERE codigo = ?";
            
    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param("sssssssi", $nome, $sobrenome, $email, $vinculo, $curso, $interesses, $lattes, $id_usuario);

    if ($stmt->execute()) {
        header("Location: sucesso.php?atualizado=1");
        exit();
    } else {
        echo "Erro ao atualizar os dados: " . $mysqli->error;
    }

    $stmt->close();
} else {
    header("Location: sucesso.php");
    exit();
}