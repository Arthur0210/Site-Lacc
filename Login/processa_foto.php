<?php
session_start();
require_once "../Login/conexao.php";

if (!isset($_SESSION['usuario'])) {
    die("Acesso negado.");
}

$id_usuario = $_SESSION['usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nova_foto'])) {
    $arquivo = $_FILES['nova_foto'];

    if ($arquivo['error'] !== 0) {
        die("Erro no envio do arquivo.");
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png'];

    if (!in_array($extensao, $permitidos)) {
        die("Formato inválido! Use apenas JPG ou PNG.");
    }

    if ($arquivo['size'] > 2 * 1024 * 1024) {
        die("Arquivo muito grande! Máximo 2MB.");
    }

    $novo_nome = "perfil_" . $id_usuario . "_" . time() . "." . $extensao;
    $caminho_final = "../uploads/perfil/" . $novo_nome;

    if (move_uploaded_file($arquivo['tmp_name'], $caminho_final)) {

        $stmt = $mysqli->prepare("UPDATE usuario SET foto = ? WHERE codigo = ?");
        $stmt->bind_param("si", $novo_nome, $id_usuario);
        
        if ($stmt->execute()) {
            header("Location: sucesso.php?atualizado=sim");
        } else {
            echo "Erro ao atualizar banco de dados.";
        }
    } else {
        echo "Erro ao mover o arquivo para a pasta. Verifique as permissões.";
    }
}

// Dica de mestre: No servidor da UFMG (Linux), às vezes você precisa dar "permissão de escrita" para a pasta uploads. Quando chegar a hora de subir o site, eu te ensino o comando chmod para o servidor deixar o PHP salvar arquivos lá!

//tava fazendo essas coisas e o gemini me mandou isso. VER ISSO DPS
//NGINX PHP