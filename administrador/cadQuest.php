<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos | Jardim Secret</title>
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

        /* --- Estilos do novo header --- */
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
        /* --- Fim dos estilos do novo header --- */

        .container {
            max-width: 800px;
            margin-top: 20px;
        }

        .card {
            box-shadow: var(--shadow);
            border: none;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-control, .form-select {
            border-radius: 0.25rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: #4a6b5b;
            border-color: #4a6b5b;
        }
    </style>
</head>
<body>
<?php include 'menu.php' ?>

    <div class="container mt-5">
        <div class="card p-4">
            <h1 class="text-center mb-4">Cadastro de Produtos</h1>
            <form action="CADASTRAR_IAMGEM_E_QUESTAO.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="imagemProduto" class="form-label">Foto do Produto</label>
                    <input type="file" id="imagemProduto" name="imagemProduto" accept="image/*" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="nomeProduto" class="form-label">Nome do Produto</label>
                    <input type="text" id="nomeProduto" name="nomeProduto" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="estoque" class="form-label">Estoque</label>
                    <input type="number" id="estoque" name="estoque" class="form-control" min="0" value="0" required>
                </div>

                <div class="mb-3">
                    <label for="marcaProduto" class="form-label">Marca do Produto</label>
                    <input type="text" id="marcaProduto" name="marcaProduto" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="valorProduto" class="form-label">Valor do Produto (R$)</label>
                    <input type="number" step="0.01" id="valorProduto" name="valorProduto" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="descricaoProduto" class="form-label">Descrição do Produto</label>
                    <textarea id="descricaoProduto" name="descricaoProduto" rows="4" class="form-control" required></textarea>
                </div>

                <?php

                    require '../conexao/conecta.php';

                    $sql = "SELECT idTipoProduto, tipo FROM tb_tipoproduto";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo '<div class="mb-3">';
                        echo '<label for="tipoProduto" class="form-label">Tipo do Produto</label>';
                        echo '<select id="tipoProduto" name="tipoProduto" class="form-select" required>';
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['idTipoProduto'] . '">' . $row['tipo'] . '</option>';
                        }
                        echo '</select>';
                        echo '</div>';
                    } else {
                        echo "<p>Nenhum tipo de produto cadastrado.</p>";
                    }
                    $conn->close();
                ?>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Cadastrar Produto
                    </button>
                </div>
            </form>
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