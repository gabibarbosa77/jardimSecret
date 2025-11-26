<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

$idUsuario = $_SESSION['id'];
$numeroCartao = $_POST['numero_cartao'] ?? '';
$nomeTitular = $_POST['nome_titular'] ?? '';
$mesValidade = $_POST['mes_validade'] ?? '';
$anoValidade = $_POST['ano_validade'] ?? '';
$cvv = $_POST['cvv'] ?? '';
$bandeira = $_POST['bandeira'] ?? '';
$apelido = $_POST['apelido'] ?? '';
$principal = $_POST['principal'] ?? 'nao';

// Remover formatação do número do cartão
$numeroCartao = preg_replace('/\D/', '', $numeroCartao);

// Verificar se é para definir como principal
if ($principal === 'sim') {
    // Remover principal de outros cartões
    $sql = "UPDATE tb_cartao SET principal = 'nao' WHERE idUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
}

// Inserir novo cartão
$sql = "INSERT INTO tb_cartao (idUsuario, numero_cartao, nome_titular, mes_validade, ano_validade, cvv, bandeira, apelido, principal) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issssssss', $idUsuario, $numeroCartao, $nomeTitular, $mesValidade, $anoValidade, $cvv, $bandeira, $apelido, $principal);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar cartão']);
}
?>