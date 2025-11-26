<?php
require '../conexao/conecta.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nomeProduto'];
    $marca = $_POST['marcaProduto'];
    $valor = floatval($_POST['valorProduto']);
    $descricao = $_POST['descricaoProduto'];
    $tipo = intval($_POST['tipoProduto']);
    $estoque = intval($_POST['estoque']); // NOVO CAMPO
    
    // Processar imagem
    if ($_FILES['imagemProduto']['error'] == UPLOAD_ERR_OK) {
        $imagem = file_get_contents($_FILES['imagemProduto']['tmp_name']);
        
        $stmt = $conn->prepare("INSERT INTO tb_produto (nomeProduto, marcaProduto, valorProduto, descricaoProduto, imagemProduto, tipoProduto, estoque) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssii", $nome, $marca, $valor, $descricao, $imagem, $tipo, $estoque);
        
        if ($stmt->execute()) {
            header("Location: cadQuest.php?success=1");
            exit();
        } else {
            header("Location: cadQuest.php?error=" . urlencode($stmt->error));
            exit();
        }
        $stmt->close();
    } else {
        header("Location: cadQuest.php?error=Erro no upload da imagem");
        exit();
    }
}

$conn->close();
?>