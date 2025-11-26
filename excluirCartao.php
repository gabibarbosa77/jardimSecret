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

$cartao = $result->fetch_assoc();

// Excluir cartão
$sql = "DELETE FROM tb_cartao WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idCartao);

if ($stmt->execute()) {
    // Se era o cartão principal, definir outro como principal
    if ($cartao['principal'] === 'sim') {
        $sql = "SELECT id FROM tb_cartao WHERE idUsuario = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $novoPrincipal = $result->fetch_assoc();
            $sql = "UPDATE tb_cartao SET principal = 'sim' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $novoPrincipal['id']);
            $stmt->execute();
        }
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir cartão']);
}
?>