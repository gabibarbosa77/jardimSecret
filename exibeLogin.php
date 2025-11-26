<?php
session_start();

require 'conexao/conecta.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioLogin = trim($_POST['usuarioLogin']);
    $loginSenha = $_POST['loginSenha'];

    // Adicionado o campo 'tipoUsuario' na consulta
    $stmt = $conn->prepare("SELECT senhaUsuario, id, tipoUsuario FROM tb_usuario WHERE nomeUsuario = ?");
    $stmt->bind_param("s", $usuarioLogin);
    $stmt->execute();

    $result = $stmt->get_result();

    // Verifica se o usuário existe
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $senha_hash = $row["senhaUsuario"];
        $id = $row["id"];
        $tipoUsuario = $row["tipoUsuario"]; // Pegando o tipo de usuário do banco de dados

        // CORREÇÃO: Usar password_verify() em vez de comparação direta
        if (password_verify($loginSenha, $senha_hash)) {
            // Se a senha estiver correta, inicia a sessão
            $_SESSION['nomeUsuario'] = $usuarioLogin;
            $_SESSION['id'] = $id;
            $_SESSION['tipoUsuario'] = $tipoUsuario; // Armazenando o tipo de usuário na sessão

            // Estrutura IF/ELSE para redirecionar com base no tipo de usuário
            if ($tipoUsuario === 'administrador') {
                echo "<script>
                    window.location.href = 'administrador/gerenciar_produtos.php';
                </script>";
            } else { // Caso seja 'cliente' ou qualquer outro tipo
                echo "<script>
                    window.location.href = 'home.php';
                </script>";
            }
        } else {
            echo "Senha incorreta!";
            echo "<br><a href='login.html'>Tentar novamente</a>";
        }
    } else {
        echo "Usuário não encontrado!";
        echo "<br><a href='login.html'>Tentar novamente</a>";
    }

    $stmt->close();
}

$conn->close();
?>