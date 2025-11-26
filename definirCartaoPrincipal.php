<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

$idUsuario = $_SESSION['id'];
$idCartao = $_POST['id'] ?? 0;

// Verificar se o cartão pertence ao usuário
$sql = "SELECT * FROM tb_cartao WHERE id = ? AND idUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $idCartao, $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cartão não encontrado']);
    exit();
}

// Remover principal de todos os cartões
$sql = "UPDATE tb_cartao SET principal = 'nao' WHERE idUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();

// Definir novo cartão principal
$sql = "UPDATE tb_cartao SET principal = 'sim' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idCartao);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao definir cartão principal']);
}
?>