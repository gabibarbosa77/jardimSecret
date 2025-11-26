<?php
require '../../conexao/conecta.php';

$compras = [];

// PRIMEIRO: Buscar TODOS os status disponíveis do banco para mapeamento correto
$sql_status = "SELECT idStatus, status FROM tb_statuspedido ORDER BY idStatus";
$result_status = $conn->query($sql_status);
$status_disponiveis = [];
$status_ids = []; // Mapeamento de status para IDs

if ($result_status && $result_status->num_rows > 0) {
    while($row = $result_status->fetch_assoc()) {
        $status_disponiveis[] = $row;
        $status_ids[strtolower($row['status'])] = $row['idStatus'];
    }
}

// Buscar apenas os status que o admin pode alterar (usando os status reais do seu banco)
$status_admin_permitidos = ['pendente', 'pagamento confirmado', 'processando', 'em transporte'];
$status_disponiveis_admin = array_filter($status_disponiveis, function($status) use ($status_admin_permitidos) {
    return in_array(strtolower($status['status']), $status_admin_permitidos);
});

// Buscar também o ID do status "em transporte" para usar no botão (equivalente a "enviado")
$id_status_enviado = $status_ids['em transporte'] ?? 8; // valor padrão baseado no seu banco

// Consulta SQL corrigida - usando tb_usuario em vez de tc_usuario
$sql = "
    SELECT
        c.idCompra,
        u.nomeCompleto,
        u.emailUsuario,
        u.telefoneUsuario,
        c.dataCompra,
        c.modoPagamento,
        c.valorTotal,
        c.valorFrete,
        c.valorDesconto,
        c.codigoRastreio,
        c.idStatus,
        s.status as nome_status,
        c.confirmacao_recebimento,
        c.data_confirmacao,
        GROUP_CONCAT(
            CONCAT(p.nomeProduto, ' (', ic.quantProduto, 'x)')
            SEPARATOR '<br>'
        ) AS itensCompra
    FROM
        tb_compra c
    JOIN
        tb_usuario u ON c.idUsuario = u.id
    JOIN
        tb_itemcompra ic ON c.idCompra = ic.idCompra
    JOIN
        tb_produto p ON ic.idProduto = p.idProduto
    JOIN
        tb_statuspedido s ON c.idStatus = s.idStatus
    GROUP BY
        c.idCompra
    ORDER BY
        c.dataCompra DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $compras[] = $row;
    }
}

// Processar atualizações de status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['atualizar_status'])) {
        $idCompra = $_POST['id_compra'];
        $novoStatus = $_POST['novo_status'];
        $codigoRastreio = $_POST['codigo_rastreio'] ?? '';
        
        // Verificar se o novo status é válido
        $status_valido = false;
        foreach ($status_disponiveis as $status) {
            if ($status['idStatus'] == $novoStatus) {
                $status_valido = true;
                break;
            }
        }
        
        if (!$status_valido) {
            $erro = "Status inválido selecionado.";
        } else {
            // Se foi fornecido código de rastreio, atualiza também
            if (!empty($codigoRastreio)) {
                $update = $conn->prepare("UPDATE tb_compra SET idStatus = ?, codigoRastreio = ? WHERE idCompra = ?");
                $update->bind_param("isi", $novoStatus, $codigoRastreio, $idCompra);
            } else {
                $update = $conn->prepare("UPDATE tb_compra SET idStatus = ? WHERE idCompra = ?");
                $update->bind_param("ii", $novoStatus, $idCompra);
            }
            
            if ($update->execute()) {
                $mensagem = "Status atualizado com sucesso!";
                // Recarregar a página apenas uma vez após a atualização
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $erro = "Erro ao atualizar status: " . $conn->error;
            }
        }
    }
    
    if (isset($_POST['adicionar_rastreio'])) {
        $idCompra = $_POST['id_compra'];
        $codigoRastreio = $_POST['codigo_rastreio'];
        
        if (empty($codigoRastreio)) {
            $erro = "Por favor, informe o código de rastreio.";
        } else {
            $update = $conn->prepare("UPDATE tb_compra SET codigoRastreio = ?, idStatus = ? WHERE idCompra = ?");
            $update->bind_param("sii", $codigoRastreio, $id_status_enviado, $idCompra);
            
            if ($update->execute()) {
                $mensagem = "Código de rastreio adicionado com sucesso!";
                // Recarregar a página apenas uma vez após a atualização
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $erro = "Erro ao adicionar código de rastreio: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Compras | Jardim Secret</title>
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

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pendente { background-color: #fff3cd; color: #856404; }
        .status-pagamento_confirmado { background-color: #d1ecf1; color: #0c5460; }
        .status-processando { background-color: #cce7ff; color: #004085; }
        .status-em_transporte { background-color: #d4edda; color: #155724; }
        .status-entregue { background-color: #28a745; color: white; }
        .status-cancelado { background-color: #f8d7da; color: #721c24; }
        
        .confirmacao-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        
        .confirmacao-pendente { background-color: #fff3cd; color: #856404; }
        .confirmacao-confirmado { background-color: #d4edda; color: #155724; }
        
        .btn-status {
            margin: 2px;
            font-size: 0.8rem;
        }
        
        .compra-detalhes {
            background-color: var(--secondary-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: var(--secondary-color);
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: var(--primary-color);
            color: white;
        }

        .card {
            box-shadow: var(--shadow);
            border: none;
        }

        .admin-only-status {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    
<?php include 'menu.php' ?>

    <?php if (isset($mensagem)): ?>
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($erro)): ?>
        <div class="container mt-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-shopping-basket"></i> Gerenciar Compras</h2>
        
        <div class="admin-only-status">
            <small><i class="fas fa-info-circle"></i> <strong>Status disponíveis para alteração:</strong> Pendente, Pagamento Confirmado, Processando e Em Transporte. Status "Entregue" e "Cancelado" são gerenciados pelo usuário.</small>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Pagamento</th>
                        <th>Status</th>
                        <th>Confirmação</th>
                        <th>Valor Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($compras)): ?>
                        <?php foreach ($compras as $compra): ?>
                            <?php
                            $status_class = str_replace(' ', '_', strtolower($compra['nome_status']));
                            ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($compra['idCompra']) ?></strong></td>
                                <td>
                                    <div><strong><?= htmlspecialchars($compra['nomeCompleto']) ?></strong></div>
                                    <small class="text-muted"><?= htmlspecialchars($compra['emailUsuario']) ?></small>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($compra['dataCompra'])) ?></td>
                                <td><?= ucfirst(htmlspecialchars($compra['modoPagamento'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $status_class ?>">
                                        <?= ucfirst(htmlspecialchars($compra['nome_status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="confirmacao-badge confirmacao-<?= htmlspecialchars($compra['confirmacao_recebimento']) ?>">
                                        <?= $compra['confirmacao_recebimento'] === 'confirmado' ? '✅ Confirmado' : '⏳ Pendente' ?>
                                        <?php if ($compra['data_confirmacao']): ?>
                                            <br><small><?= date('d/m/Y', strtotime($compra['data_confirmacao'])) ?></small>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><strong>R$ <?= number_format($compra['valorTotal'], 2, ',', '.') ?></strong></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detalhesModal<?= $compra['idCompra'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <?php if (!in_array(strtolower($compra['nome_status']), ['entregue', 'cancelado'])): ?>
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#statusModal<?= $compra['idCompra'] ?>">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Status finalizado - não pode ser alterado">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            
                            <!-- Modal de Detalhes -->
                            <div class="modal fade" id="detalhesModal<?= $compra['idCompra'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalhes da Compra #<?= $compra['idCompra'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-user"></i> Dados do Cliente</h6>
                                                    <p><strong>Nome:</strong> <?= htmlspecialchars($compra['nomeCompleto']) ?><br>
                                                    <strong>Email:</strong> <?= htmlspecialchars($compra['emailUsuario']) ?><br>
                                                    <strong>Telefone:</strong> <?= htmlspecialchars($compra['telefoneUsuario']) ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-info-circle"></i> Informações da Compra</h6>
                                                    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($compra['dataCompra'])) ?><br>
                                                    <strong>Pagamento:</strong> <?= ucfirst($compra['modoPagamento']) ?><br>
                                                    <strong>Status:</strong> <span class="status-badge status-<?= $status_class ?>"><?= ucfirst($compra['nome_status']) ?></span></p>
                                                </div>
                                            </div>
                                            
                                            <h6><i class="fas fa-boxes"></i> Itens da Compra</h6>
                                            <div class="compra-detalhes">
                                                <?= $compra['itensCompra'] ?>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-receipt"></i> Valores</h6>
                                                    <p><strong>Subtotal:</strong> R$ <?= number_format($compra['valorTotal'] - $compra['valorFrete'] + $compra['valorDesconto'], 2, ',', '.') ?><br>
                                                    <strong>Frete:</strong> R$ <?= number_format($compra['valorFrete'], 2, ',', '.') ?><br>
                                                    <strong>Desconto:</strong> R$ <?= number_format($compra['valorDesconto'], 2, ',', '.') ?><br>
                                                    <strong>Total:</strong> R$ <?= number_format($compra['valorTotal'], 2, ',', '.') ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6><i class="fas fa-truck"></i> Entrega</h6>
                                                    <?php if ($compra['codigoRastreio']): ?>
                                                        <p><strong>Código Rastreio:</strong> <?= htmlspecialchars($compra['codigoRastreio']) ?><br>
                                                        <strong>Confirmação:</strong> 
                                                            <span class="confirmacao-badge confirmacao-<?= $compra['confirmacao_recebimento'] ?>">
                                                                <?= $compra['confirmacao_recebimento'] === 'confirmado' ? '✅ Confirmado' : '⏳ Pendente' ?>
                                                            </span>
                                                        </p>
                                                    <?php else: ?>
                                                        <p class="text-muted">Aguardando envio</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal de Status CORRIGIDO - apenas status que admin pode alterar -->
                            <?php if (!in_array(strtolower($compra['nome_status']), ['entregue', 'cancelado'])): ?>
                            <div class="modal fade" id="statusModal<?= $compra['idCompra'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Gerenciar Status #<?= $compra['idCompra'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="id_compra" value="<?= $compra['idCompra'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Atualizar Status:</label>
                                                    <select name="novo_status" class="form-select" required>
                                                        <?php if (!empty($status_disponiveis_admin)): ?>
                                                            <?php foreach ($status_disponiveis_admin as $status): ?>
                                                                <option value="<?= $status['idStatus'] ?>" 
                                                                    <?= $compra['idStatus'] == $status['idStatus'] ? 'selected' : '' ?>>
                                                                    <?= ucfirst(htmlspecialchars($status['status'])) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="">Nenhum status disponível</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <small class="text-muted">Status "Entregue" e "Cancelado" são gerenciados pelo usuário.</small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Código de Rastreio:</label>
                                                    <input type="text" name="codigo_rastreio" class="form-control" 
                                                           value="<?= htmlspecialchars($compra['codigoRastreio'] ?? '') ?>" 
                                                           placeholder="Digite o código de rastreio">
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <button type="submit" name="atualizar_status" class="btn btn-primary">
                                                        <i class="fas fa-save"></i> Atualizar Status
                                                    </button>
                                                    <?php if (!$compra['codigoRastreio'] && strtolower($compra['nome_status']) != 'em transporte'): ?>
                                                    <button type="submit" name="adicionar_rastreio" class="btn btn-success">
                                                        <i class="fas fa-truck"></i> Marcar como Em Transporte + Rastreio
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-2x text-muted mb-3"></i><br>
                                Nenhuma compra encontrada.
                            </td>
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