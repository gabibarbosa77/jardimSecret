<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

$idUsuario = $_SESSION['id'];
$idProduto = $_POST['productId'] ?? null;
$quantidade = $_POST['quantity'] ?? 1;

if (!$idProduto) {
    echo json_encode(['success' => false, 'message' => 'Produto não especificado']);
    exit();
}

// Verificar se o produto já está no carrinho
$checkQuery = $conn->prepare("SELECT idCarrinho, quantProduto FROM tb_carrinho WHERE idUsuario = ? AND idProduto = ?");
$checkQuery->bind_param("ii", $idUsuario, $idProduto);
$checkQuery->execute();
$result = $checkQuery->get_result();

if ($result->num_rows > 0) {
    // Produto já existe no carrinho - atualizar quantidade
    $item = $result->fetch_assoc();
    $novaQuantidade = $item['quantProduto'] + $quantidade;
    
    $updateQuery = $conn->prepare("UPDATE tb_carrinho SET quantProduto = ? WHERE idCarrinho = ?");
    $updateQuery->bind_param("ii", $novaQuantidade, $item['idCarrinho']);
    
    if ($updateQuery->execute()) {
        echo json_encode(['success' => true, 'action' => 'updated', 'newQuantity' => $novaQuantidade]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar carrinho']);
    }
} else {
    // Produto não está no carrinho - adicionar novo item
    // Primeiro, precisamos do próximo ID disponível para o carrinho
    $maxIdQuery = $conn->query("SELECT COALESCE(MAX(idCarrinho), 0) + 1 as nextId FROM tb_carrinho");
    $nextId = $maxIdQuery->fetch_assoc()['nextId'];
    
    $insertQuery = $conn->prepare("INSERT INTO tb_carrinho (idCarrinho, idUsuario, idProduto, quantProduto) VALUES (?, ?, ?, ?)");
    $insertQuery->bind_param("iiii", $nextId, $idUsuario, $idProduto, $quantidade);
    
    if ($insertQuery->execute()) {
        echo json_encode(['success' => true, 'action' => 'added', 'newQuantity' => $quantidade]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar ao carrinho']);
    }
}

$conn->close();
?>