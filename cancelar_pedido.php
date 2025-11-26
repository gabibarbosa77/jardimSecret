<?php
session_start();
require 'conexao/conecta.php';
require 'status_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

$idPedido = $_POST['id_pedido'] ?? 0;
$idUsuario = $_SESSION['id'];

if (empty($idPedido) || !is_numeric($idPedido)) {
    echo json_encode(['success' => false, 'message' => 'ID do pedido inválido']);
    exit();
}

try {
    // BUSCAR IDs DINAMICAMENTE
    $statusIds = getStatusIds($conn);
    $cancelavel = [$statusIds['pendente'], $statusIds['confirmado']];
    $idCancelado = $statusIds['cancelado'];
    
    // Verificar pedido
    $sql_verificar = "SELECT idCompra, idStatus FROM tb_compra WHERE idCompra = ? AND idUsuario = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("ii", $idPedido, $idUsuario);
    $stmt_verificar->execute();
    $resultado = $stmt_verificar->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
        exit();
    }
    
    $pedido = $resultado->fetch_assoc();
    
    // Verificar se pode cancelar (status pendente ou confirmado)
    if (!in_array($pedido['idStatus'], $cancelavel)) {
        echo json_encode(['success' => false, 'message' => 'Este pedido não pode ser cancelado.']);
        exit();
    }
    
    // Atualizar para status cancelado
    $sql_cancelar = "UPDATE tb_compra SET idStatus = ? WHERE idCompra = ?";
    $stmt_cancelar = $conn->prepare($sql_cancelar);
    $stmt_cancelar->bind_param("ii", $idCancelado, $idPedido);
    
    if ($stmt_cancelar->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pedido cancelado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cancelar pedido']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

$conn->close();
?>