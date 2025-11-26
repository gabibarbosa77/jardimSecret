<?php
 require '../../conexao/conecta.php';

$message = '';
$message_type = '';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM tb_usuario WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Cliente excluído com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao excluir cliente: " . $stmt->error;
        $message_type = 'danger';
    }
    $stmt->close();
} else {
    $message = "ID do cliente não fornecido.";
    $message_type = 'danger';
}

$conn->close();

// Redireciona de volta para a página de visualização com a mensagem
header("Location: gerenciarClientes.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
exit();
?>