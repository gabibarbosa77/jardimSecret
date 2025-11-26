<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

$idUsuario = $_SESSION['id'];
$itemId = $_POST['itemId'] ?? null;
$action = $_POST['action'] ?? '';

if ($action === 'update' && isset($_POST['quantity'])) {
    $quantity = intval($_POST['quantity']);
    
    $stmt = $conn->prepare("UPDATE tb_carrinho SET quantProduto = ? WHERE idCarrinho = ? AND idUsuario = ?");
    $stmt->bind_param("iii", $quantity, $itemId, $idUsuario);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar quantidade']);
    }
} elseif ($action === 'remove') {
    $stmt = $conn->prepare("DELETE FROM tb_carrinho WHERE idCarrinho = ? AND idUsuario = ?");
    $stmt->bind_param("ii", $itemId, $idUsuario);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover item']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}

$conn->close();
?>