<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit();
}

$idUsuario = $_SESSION['id'];

$sql = "SELECT * FROM tb_cartao WHERE idUsuario = ? ORDER BY principal DESC, data_cadastro DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

$cartoes = [];
while ($row = $result->fetch_assoc()) {
    $cartoes[] = $row;
}

echo json_encode($cartoes);
?>