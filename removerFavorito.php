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
        $stmt = $conn->prepare("DELETE FROM tb_favoritos WHERE idUsuario = ? AND idProduto = ?");
        $stmt->bind_param('ii', $idUsuario, $idProduto);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Produto removido dos favoritos']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover dos favoritos']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
}
?>
