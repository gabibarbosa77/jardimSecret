<?php
 require '../../conexao/conecta.php';

// Lógica para LISTAR todos os clientes (CORRIGIDO)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchTerm = "%$search%";
$query = "SELECT * FROM tb_usuario WHERE tipoUsuario = 'cliente' AND (nomeCompleto LIKE ? OR emailUsuario LIKE ?) ORDER BY nomeCompleto";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$clientes = [];

// Verifica se a consulta retornou um resultado válido
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
} else {
    // Adiciona uma mensagem de erro em caso de falha na consulta
    die("Erro ao listar clientes: " . $conn->error);
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes | Caminho do Saber</title>
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
        /* --- Fim dos estilos do header --- */

        .container {
            max-width: 1200px;
            margin-top: 20px;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .table thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        .table-hover tbody tr:hover {
            background-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    
<?php include 'menu.php' ?>


    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Clientes</h2>
            <a href="adicionarCliente.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Adicionar Cliente
            </a>
        </div>

        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar clientes..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="gerenciarClientes.php" class="btn btn-secondary">Limpar</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nome Completo</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">CPF</th>
                        <th scope="col">Telefone</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= htmlspecialchars($cliente['id']) ?></td>
                            <td><?= htmlspecialchars($cliente['nomeCompleto']) ?></td>
                            <td><?= htmlspecialchars($cliente['emailUsuario']) ?></td>
                            <td><?= htmlspecialchars($cliente['cpfUsuario']) ?></td>
                            <td><?= htmlspecialchars($cliente['telefoneUsuario']) ?></td>
                            <td>
                                <a href="editarCliente.php?id=<?= $cliente['id'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="excluirCliente.php?id=<?= $cliente['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum cliente encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
    </script>
</body>
</html>
<?php
$conn->close();
?>