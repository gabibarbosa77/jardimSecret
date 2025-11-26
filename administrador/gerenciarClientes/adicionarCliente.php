<?php
 require '../../conexao/conecta.php';

$success_message = '';
$error_message = '';

// Lógica para processar o CADASTRO do novo cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $nomeCompleto = $_POST['nomeCompleto'];
    $nomeUsuario = $_POST['nomeUsuario'];
    $senhaUsuario = $_POST['senhaUsuario']; 
    $telefoneUsuario = $_POST['telefoneUsuario'];
    $cpfUsuario = $_POST['cpfUsuario'];
    $emailUsuario = $_POST['emailUsuario'];
    $cepUsuario = $_POST['cepUsuario'];
    $enderecoUsuario = $_POST['enderecoUsuario'];
    $numUsuario = $_POST['numUsuario'];
    $bairroUsuario = $_POST['bairroUsuario'];
    $dataNasc = $_POST['dataNasc'];
    $tipoUsuario = 'cliente'; // Define o tipo de usuário como 'cliente' por padrão

    // Prepara a consulta SQL para inserir um novo cliente na tabela tc_usuario
    $stmt = $conn->prepare("INSERT INTO tc_usuario (nomeCompleto, nomeUsuario, senhaUsuario, telefoneUsuario, cpfUsuario, emailUsuario, cepUsuario, enderecoUsuario, numUsuario, bairroUsuario, dataNasc, tipoUsuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssssssssis", $nomeCompleto, $nomeUsuario, $senhaUsuario, $telefoneUsuario, $cpfUsuario, $emailUsuario, $cepUsuario, $enderecoUsuario, $numUsuario, $bairroUsuario, $dataNasc, $tipoUsuario);
        if ($stmt->execute()) {
            $success_message = "Cliente cadastrado com sucesso!";
            // Redireciona para a página de gerenciamento com a mensagem de sucesso
            header("Location: gerenciarClientes.php?success_message=" . urlencode($success_message));
            exit();
        } else {
            $error_message = "Erro ao cadastrar cliente: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Erro ao preparar a consulta de inserção: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Cliente | Caminho do Saber</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a8d73;
            --secondary-color: #f4f4f4;
            --accent-color: #ff7e5f;
            --dark-color: #2c3e50;
            --light-color: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: var(--dark-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* --- Estilos do header --- */
        header {
            background: linear-gradient(135deg, var(--primary-color), #4a6b5b);
            color: white;
            padding: 10px 20px;
            box-shadow: var(--shadow);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo img {
            height: 50px;
        }

        .site-title {
            margin: 0;
            font-size: 1.5rem;
            flex-grow: 1;
            text-align: center;
        }

        .user-menu {
            position: relative;
        }

        .user-toggle {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 8px 12px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .user-toggle:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--light-color);
            min-width: 200px;
            box-shadow: var(--shadow);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
        }

        .user-dropdown.show {
            display: block;
        }

        .user-dropdown a {
            color: var(--dark-color);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: var(--transition);
        }

        .user-dropdown a:hover {
            background-color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .site-title {
                display: none;
            }
        }

        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Adicionar Novo Cliente</h2>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <form method="post" class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="nomeCompleto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome de Usuário</label>
                        <input type="text" name="nomeUsuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <div class="password-container">
                            <input type="password" name="senhaUsuario" id="senhaUsuario" class="form-control" required>
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CPF</label>
                        <input type="text" name="cpfUsuario" id="cpfUsuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Data de Nascimento</label>
                        <input type="text" name="dataNasc" id="dataNasc" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="emailUsuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefoneUsuario" id="telefoneUsuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="enderecoUsuario" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Número</label>
                                <input type="text" name="numUsuario" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cepUsuario" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="bairroUsuario" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="gerenciarClientes.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" name="submit" class="btn btn-primary">Cadastrar Cliente</button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu do usuário
        document.getElementById('userToggle').addEventListener('click', function() {
            document.getElementById('userDropdown').classList.toggle('show');
        });

        // Fechar menu quando clicar fora
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.user-toggle') && !event.target.closest('.user-toggle')) {
                var dropdowns = document.getElementsByClassName("user-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        });

        // Lógica para máscaras de input
        function mascara(o,f){
            v_obj=o;
            v_fun=f;
            setTimeout("execmascara()",1);
        }
        function execmascara(){
            v_obj.value=v_fun(v_obj.value);
        }
        function mtel(v){
            v=v.replace(/\D/g,"");
            v=v.replace(/^(\d{2})(\d)/g,"($1) $2");
            v=v.replace(/(\d)(\d{4})$/,"$1-$2");
            return v;
        }
        function mcpf(v){
            v=v.replace(/\D/g,"");
            v=v.replace(/(\d{3})(\d)/,"$1.$2");
            v=v.replace(/(\d{3})(\d)/,"$1.$2");
            v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2");
            return v;
        }
        function mdata(v){
            v=v.replace(/\D/g,"");
            v=v.replace(/(\d{2})(\d)/,"$1/$2");
            v=v.replace(/(\d{2})(\d)/,"$1/$2");
            return v;
        }

        window.addEventListener('load', function() {
            const telInput = document.getElementById('telefoneUsuario');
            const cpfInput = document.getElementById('cpfUsuario');
            const dataInput = document.getElementById('dataNasc');

            if (telInput) telInput.addEventListener('keyup', function() { mascara(this, mtel); });
            if (cpfInput) cpfInput.addEventListener('keyup', function() { mascara(this, mcpf); });
            if (dataInput) dataInput.addEventListener('keyup', function() { mascara(this, mdata); });

            // Lógica para o "olho" da senha
            const togglePassword = document.getElementById('togglePassword');
            const senhaInput = document.getElementById('senhaUsuario');

            if (togglePassword && senhaInput) {
                togglePassword.addEventListener('click', function() {
                    const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    senhaInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>