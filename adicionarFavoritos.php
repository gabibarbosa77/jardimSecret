<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['id'];
    $idProduto = $_POST['idProduto'] ?? '';
    
    if (empty($idProduto)) {
        echo json_encode(['success' => false, 'message' => 'ID do produto não informado']);
        exit();
    }
    
    try {
        // Verificar se o produto existe e está ativo
        $stmt = $conn->prepare("SELECT idProduto FROM tb_produto WHERE idProduto = ? AND ativo = 1");
        $stmt->bind_param('i', $idProduto);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
            exit();
        }
        
        // Verificar se já está nos favoritos
        $stmt = $conn->prepare("SELECT idFavorito FROM tb_favoritos WHERE idUsuario = ? AND idProduto = ?");
        $stmt->bind_param('ii', $idUsuario, $idProduto);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Produto já está nos favoritos']);
            exit();
        }
        
        // Adicionar aos favoritos
        $stmt = $conn->prepare("INSERT INTO tb_favoritos (idUsuario, idProduto) VALUES (?, ?)");
        $stmt->bind_param('ii', $idUsuario, $idProduto);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Produto adicionado aos favoritos']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar aos favoritos']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
}
?>
