<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];

// Coletar dados do formulário
$nomeCompleto = $_POST['nomeCompleto'] ?? '';
$nomeUsuario = $_POST['nomeUsuario'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$dataNasc = $_POST['dataNasc'] ?? '';
$cpf = $_POST['cpf'] ?? '';
$cep = $_POST['cep'] ?? '';
$endereco = $_POST['endereco'] ?? '';
$numero = $_POST['numero'] ?? '';
$bairro = $_POST['bairro'] ?? '';
$complemento = $_POST['complemento'] ?? '';
$instrucaoEntrega = $_POST['instrucaoEntrega'] ?? '';

// Verificar se senha foi fornecida
if (!empty($_POST['senha'])) {
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $sql = "UPDATE tb_usuario SET 
                nomeCompleto = ?, 
                nomeUsuario = ?, 
                emailUsuario = ?, 
                senhaUsuario = ?, 
                telefoneUsuario = ?, 
                dataNasc = ?, 
                cpfUsuario = ?, 
                cepUsuario = ?, 
                enderecoUsuario = ?, 
                numUsuario = ?, 
                bairroUsuario = ?, 
                complemento = ?,
                instrucaoEntrega = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssssssi', 
        $nomeCompleto, $nomeUsuario, $email, $senha, $telefone, 
        $dataNasc, $cpf, $cep, $endereco, $numero, $bairro, 
        $complemento, $instrucaoEntrega, $id
    );
} else {
    $sql = "UPDATE tb_usuario SET 
                nomeCompleto = ?, 
                nomeUsuario = ?, 
                emailUsuario = ?, 
                telefoneUsuario = ?, 
                dataNasc = ?, 
                cpfUsuario = ?, 
                cepUsuario = ?, 
                enderecoUsuario = ?, 
                numUsuario = ?, 
                bairroUsuario = ?, 
                complemento = ?,
                instrucaoEntrega = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssssssi', 
        $nomeCompleto, $nomeUsuario, $email, $telefone, 
        $dataNasc, $cpf, $cep, $endereco, $numero, $bairro, 
        $complemento, $instrucaoEntrega, $id
    );
}

if ($stmt->execute()) {
    $_SESSION['mensagem_sucesso'] = "Perfil atualizado com sucesso!";
} else {
    $_SESSION['mensagem_erro'] = "Erro ao atualizar perfil: " . $conn->error;
}

$conn->close();
header("Location: perfil.php");
exit();
?>