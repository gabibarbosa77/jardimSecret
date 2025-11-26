<?php
require 'conexao/conecta.php';

// Coletar dados do POST
$nomeCompleto = trim($_POST["nomeCompleto"]);
$nomeUsuario = trim($_POST["nomeUsuario"]);
$emailUsuario = $_POST["emailUsuario"];
$telefoneUsuario = $_POST["telefoneUsuario"];
$cpfUsuario = $_POST["cpfUsuario"];
$dataNasc = $_POST["dataNasc"];
$senhaUsuario = $_POST["senhaUsuario"];
$cepUsuario = $_POST["cepUsuario"];
$enderecoUsuario = $_POST["enderecoUsuario"];
$numUsuario = $_POST["numUsuario"];
$complemento = $_POST["complemento"];
$bairroUsuario = $_POST["bairroUsuario"];
$tipoUsuario = 'cliente';

// Criar hash da senha
$senhaHash = password_hash($senhaUsuario, PASSWORD_DEFAULT);

if ($senhaHash === false) {
    header("Location: cadastro1.php?error=database");
    exit();
}

// Verificar se o nome de usuário já existe
$sql_usuario = "SELECT nomeUsuario FROM tb_usuario WHERE nomeUsuario = ?";
$stmt_usuario = mysqli_prepare($conn, $sql_usuario);
mysqli_stmt_bind_param($stmt_usuario, 's', $nomeUsuario);
mysqli_stmt_execute($stmt_usuario);
mysqli_stmt_store_result($stmt_usuario);

// Verificar se o CPF já existe
$sql_cpf = "SELECT cpfUsuario FROM tb_usuario WHERE cpfUsuario = ?";
$stmt_cpf = mysqli_prepare($conn, $sql_cpf);
mysqli_stmt_bind_param($stmt_cpf, 's', $cpfUsuario);
mysqli_stmt_execute($stmt_cpf);
mysqli_stmt_store_result($stmt_cpf);

if (mysqli_stmt_num_rows($stmt_usuario) > 0) {
    // Redirecionar com erro de username e manter os dados preenchidos
    $params = http_build_query([
        'error' => 'username',
        'nomeCompleto' => $nomeCompleto,
        'emailUsuario' => $emailUsuario,
        'telefoneUsuario' => $telefoneUsuario,
        'cpfUsuario' => $cpfUsuario,
        'dataNasc' => $dataNasc,
        'cepUsuario' => $cepUsuario,
        'enderecoUsuario' => $enderecoUsuario,
        'numUsuario' => $numUsuario,
        'complemento' => $complemento,
        'bairroUsuario' => $bairroUsuario
    ]);
    header("Location: cadastro1.php?" . $params);
    exit();
} else if (mysqli_stmt_num_rows($stmt_cpf) > 0) {
    // Redirecionar com erro de CPF e manter os dados preenchidos
    $params = http_build_query([
        'error' => 'cpf',
        'nomeCompleto' => $nomeCompleto,
        'nomeUsuario' => $nomeUsuario,
        'emailUsuario' => $emailUsuario,
        'telefoneUsuario' => $telefoneUsuario,
        'dataNasc' => $dataNasc,
        'cepUsuario' => $cepUsuario,
        'enderecoUsuario' => $enderecoUsuario,
        'numUsuario' => $numUsuario,
        'complemento' => $complemento,
        'bairroUsuario' => $bairroUsuario
    ]);
    header("Location: cadastro1.php?" . $params);
    exit();
} else {
    // Realizar o cadastro
    $sql = "INSERT INTO tb_usuario (nomeCompleto, nomeUsuario, senhaUsuario, telefoneUsuario, cpfUsuario, emailUsuario, cepUsuario, enderecoUsuario, numUsuario, complemento, bairroUsuario, dataNasc, tipoUsuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmtInsert, 'sssssssssssss', $nomeCompleto, $nomeUsuario, $senhaHash, $telefoneUsuario, $cpfUsuario, $emailUsuario, $cepUsuario, $enderecoUsuario, $numUsuario, $complemento, $bairroUsuario, $dataNasc, $tipoUsuario);
    
    if (mysqli_stmt_execute($stmtInsert)) {
        header("Location: cadastro1.php?success=1");
        exit();
    } else {
        header("Location: cadastro1.php?error=database");
        exit();
    }
}

mysqli_stmt_close($stmt_usuario);
mysqli_stmt_close($stmt_cpf);
mysqli_close($conn);
?>