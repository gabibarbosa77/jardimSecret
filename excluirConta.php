<?php
session_start();

require 'conexao/conecta.php';

$id = $_SESSION['id'];

// Primeiro, exclua os registros relacionados (se houver)
// $conn->query("DELETE FROM tabela_relacionada WHERE usuario_id = $id");

// Depois, exclua o usuário
$sql = "DELETE FROM tb_usuario WHERE id = $id";

if ($conn->query($sql)) {
    // Limpar a sessão e redirecionar
    session_unset();
    session_destroy();
    header("Location: login.html?conta_excluida=1");
} else {
    header("Location: perfil.php?erro_exclusao=1");
}

$conn->close();
?>