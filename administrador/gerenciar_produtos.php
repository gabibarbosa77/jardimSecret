<?php

 require '../conexao/conecta.php';

if (isset($_GET['action'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['action'] == 'delete') {
        $stmt = $conn->prepare("DELETE FROM tb_produto WHERE idProduto = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success_message = "Produto excluído com sucesso!";
        } else {
            $error_message = "Erro ao excluir produto: " . $stmt->error;
        }
        $stmt->close();
    }
    
    if ($_GET['action'] == 'edit') {
        $stmt = $conn->prepare("SELECT * FROM tb_produto WHERE idProduto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produto = $result->fetch_assoc();
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nome = $_POST['nomeProduto'];
    $marca = $_POST['marcaProduto'];
    $valor = floatval($_POST['valorProduto']);
    $descricao = $_POST['descricaoProduto'];
    $tipo = intval($_POST['tipoProduto']);
    $estoque = intval($_POST['estoque']); // NOVO CAMPO
    
    if ($_FILES['imagemProduto']['error'] == UPLOAD_ERR_OK) {
        $imagem = file_get_contents($_FILES['imagemProduto']['tmp_name']);
        $stmt = $conn->prepare("UPDATE tb_produto SET nomeProduto=?, marcaProduto=?, valorProduto=?, descricaoProduto=?, imagemProduto=?, tipoProduto=?, estoque=? WHERE idProduto=?");
        $stmt->bind_param("ssdsssii", $nome, $marca, $valor, $descricao, $imagem, $tipo, $estoque, $id);
    } else {
        $stmt = $conn->prepare("UPDATE tb_produto SET nomeProduto=?, marcaProduto=?, valorProduto=?, descricaoProduto=?, tipoProduto=?, estoque=? WHERE idProduto=?");
        $stmt->bind_param("ssdsiii", $nome, $marca, $valor, $descricao, $tipo, $estoque, $id);
    }
    
    if ($stmt->execute()) {
        $success_message = "Produto atualizado com sucesso!";
    } else {
        $error_message = "Erro ao atualizar produto: " . $stmt->error;
    }
    $stmt->close();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT p.*, t.tipo FROM tb_produto p 
          JOIN tb_tipoproduto t ON p.tipoProduto = t.idTipoProduto 
          WHERE p.nomeProduto LIKE ? OR p.marcaProduto LIKE ? 
          ORDER BY p.nomeProduto";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Erro ao preparar a consulta: " . $conn->error);
}

$searchTerm = "%$search%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);

if (!$stmt->execute()) {
    die("Erro ao executar a consulta: " . $stmt->error);
}

$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos | Jardim Secret</title>
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
            max-width: 1200px;
            margin-top: 20px;
        }

        .card {
            box-shadow: var(--shadow);
            border: none;
        }

        .product-img {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    
<?php include 'menu.php' ?>

    <?php if (!empty($success_message)): ?>
    <div class="container mt-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
    <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mt-4">
        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar produtos..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="gerenciar_produtos.php" class="btn btn-secondary">Limpar</a>
            </div>
        </form>

        <div class="row" id="productsContainer">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                    <div class="col-lg-4 col-md-6 mb-4 product-card" data-category="<?= strtolower($row['tipo']) ?>">
                        <div class="card h-100">
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['imagemProduto']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($row['nomeProduto']) ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title"><?= htmlspecialchars($row['nomeProduto']) ?></h5>
                                    <span class="badge bg-success"><?= htmlspecialchars($row['tipo']) ?></span>
                                </div>
                                <p class="text-muted"><?= htmlspecialchars($row['marcaProduto']) ?></p>
                                <p class="card-text"><?= substr(htmlspecialchars($row['descricaoProduto']), 0, 100) ?>...</p>
                                
                                <!-- NOVA LINHA COM ESTOQUE -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Estoque: <?= $row['estoque'] ?></span>
                                    <span class="price-tag">R$ <?= number_format($row['valorProduto'], 2, ',', '.') ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="gerenciar_produtos.php?action=edit&id=<?= $row['idProduto'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="gerenciar_produtos.php?action=delete&id=<?= $row['idProduto'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">
                                            <i class="fas fa-trash-alt"></i> Excluir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h4>Nenhum produto encontrado</h4>
                    <p>Adicione novos produtos ou ajuste sua busca</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($produto)): ?>
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color), #4a6b5b);">
                    <h5 class="modal-title">Editar Produto</h5>
                    <a href="gerenciar_produtos.php" class="btn-close btn-close-white"></a>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $produto['idProduto'] ?>">
                    <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nome do Produto</label>
                                <input type="text" name="nomeProduto" class="form-control" value="<?= htmlspecialchars($produto['nomeProduto']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Marca</label>
                                <input type="text" name="marcaProduto" class="form-control" value="<?= htmlspecialchars($produto['marcaProduto']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Valor (R$)</label>
                                <input type="number" step="0.01" name="valorProduto" class="form-control" value="<?= htmlspecialchars($produto['valorProduto']) ?>" required>
                            </div>
                            <!-- NOVO CAMPO ESTOQUE -->
                            <div class="mb-3">
                                <label class="form-label">Estoque</label>
                                <input type="number" name="estoque" class="form-control" min="0" value="<?= htmlspecialchars($produto['estoque']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Imagem Atual</label><br>
                                <img src="data:image/jpeg;base64,<?= base64_encode($produto['imagemProduto']) ?>" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nova Imagem (opcional)</label>
                                <input type="file" name="imagemProduto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricaoProduto" class="form-control" rows="3" required><?= htmlspecialchars($produto['descricaoProduto']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Produto</label>
                            <select name="tipoProduto" class="form-select" required>
                                <?php
                                $tipos = $conn->query("SELECT * FROM tb_tipoproduto");
                                while ($tipo = $tipos->fetch_assoc()):
                                ?>
                                <option value="<?= $tipo['idTipoProduto'] ?>" <?= $tipo['idTipoProduto'] == $produto['tipoProduto'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['tipo']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="gerenciar_produtos.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" name="update" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('userToggle').addEventListener('click', function() {
            document.getElementById('userDropdown').classList.toggle('show');
        });

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