<?php
session_start();
require 'conexao/conecta.php';
require 'status_config.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id'];
$mensagem = '';

// BUSCAR IDs DINAMICAMENTE
$statusIds = getStatusIds($conn);
$idEntregue = $statusIds['entregue'] ?? 9;
$idEmTransporte = $statusIds['em transporte'] ?? 8;

// Buscar compras com status "entregue" ou "em transporte" e recebimento pendente
$comprasQuery = $conn->prepare("
    SELECT 
        c.idCompra, 
        c.dataCompra, 
        c.valorTotal, 
        s.status, 
        c.confirmacao_recebimento,
        c.idStatus,
        COUNT(ic.idItem) as totalItens
    FROM tb_compra c
    JOIN tb_statuspedido s ON c.idStatus = s.idStatus
    LEFT JOIN tb_itemcompra ic ON c.idCompra = ic.idCompra
    WHERE c.idUsuario = ? AND c.idStatus IN (?, ?) AND c.confirmacao_recebimento = 'pendente'
    GROUP BY c.idCompra
    ORDER BY c.dataCompra DESC
");
$comprasQuery->bind_param("iii", $idUsuario, $idEmTransporte, $idEntregue);
$comprasQuery->execute();
$compras = $comprasQuery->get_result();

// Processar confirmação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_recebimento'])) {
    $idCompra = $_POST['id_compra'];
    
    $verifica = $conn->prepare("
        SELECT idCompra, idStatus FROM tb_compra 
        WHERE idCompra = ? AND idUsuario = ? AND idStatus IN (?, ?) AND confirmacao_recebimento = 'pendente'
    ");
    $verifica->bind_param("iiii", $idCompra, $idUsuario, $idEmTransporte, $idEntregue);
    $verifica->execute();
    $verificaResult = $verifica->get_result();
    
    if ($verificaResult->num_rows > 0) {
        $compraData = $verificaResult->fetch_assoc();
        $currentStatus = $compraData['idStatus'];
        
        // Se o status atual for "em transporte", muda para "entregue"
        // Se o status atual for "entregue", mantém como "entregue" (ou pode mudar para outro status se quiser)
        $novoStatus = ($currentStatus == $idEmTransporte) ? $idEntregue : $idEntregue;
        
        $update = $conn->prepare("
            UPDATE tb_compra SET 
                confirmacao_recebimento = 'confirmado', 
                data_confirmacao = NOW(), 
                idStatus = ? 
            WHERE idCompra = ?
        ");
        $update->bind_param("ii", $novoStatus, $idCompra);
        
        if ($update->execute()) {
            // Sucesso - redirecionar com mensagem
            $statusMessage = ($currentStatus == $idEmTransporte) 
            
                ? "Entrega confirmada com sucesso! O pedido foi marcado como entregue." 
                : "Recebimento confirmado com sucesso! O pedido foi finalizado.";
            
            $_SESSION['mensagem_sucesso'] = $statusMessage;
            header("Location: pedidos.php");
            exit();
        } else {
            $mensagem = "Erro ao confirmar recebimento.";
        }
    } else {
        $mensagem = "Compra não encontrada ou já confirmada.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Recebimento | Jardim Secret</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --rosa-primario: #d4a5a5;
            --rosa-escuro: #b87c7c;
            --rosa-claro: #f9f0f0;
            --destaque: #7b4c58;
            --bege: #f8f5f0;
            --branco: #ffffff;
            --cinza: #495057;
            --cinza-claro: #6c757d;
            --verde: #28a745;
            --laranja: #fd7e14;
            --azul: #007bff;
            --vermelho: #dc3545;
            --dourado: #d4af37;
            --sombra: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bege);
            color: var(--cinza);
        }

        .page-header {
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            color: var(--branco);
            padding: 40px 0;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--sombra);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: var(--rosa-claro);
            border-bottom: 1px solid var(--rosa-primario);
            font-weight: 600;
            color: var(--destaque);
        }

        .btn-success {
            background: var(--verde);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background: var(--laranja);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
            color: white;
        }

        .badge-confirmado {
            background: var(--verde);
            color: var(--branco);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }

        .order-number {
            color: var(--destaque);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .order-value {
            color: var(--verde);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--branco);
            border-radius: 12px;
            box-shadow: var(--sombra);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--rosa-primario);
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 1.5rem;
            color: var(--destaque);
            margin-bottom: 10px;
        }

        .empty-description {
            color: var(--cinza-claro);
            margin-bottom: 30px;
        }

        .alert-info {
            background: var(--rosa-claro);
            border: 1px solid var(--rosa-primario);
            color: var(--destaque);
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .status-transporte {
            background: #fff3cd;
            color: #856404;
        }

        .status-entregue {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-check-circle"></i>
                Confirmar Entrega/Recebimento
            </h1>
            <p class="page-subtitle">Confirme a entrega ou recebimento dos seus pedidos</p>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                Confirmação realizada com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($mensagem): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i>
                <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if ($compras->num_rows > 0): ?>
                <?php while ($compra = $compras->fetch_assoc()): ?>
                    <?php
                    $isEmTransporte = ($compra['idStatus'] == $idEmTransporte);
                    $isEntregue = ($compra['idStatus'] == $idEntregue);
                    $btnClass = $isEmTransporte ? 'btn-warning' : 'btn-success';
                    $btnIcon = $isEmTransporte ? 'fa-truck-loading' : 'fa-check-circle';
                    $btnText = $isEmTransporte ? 'Confirmar Entrega' : 'Confirmar Recebimento';
                    $statusClass = $isEmTransporte ? 'status-transporte' : 'status-entregue';
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="order-number">
                                        <i class="fas fa-shopping-bag"></i>
                                        Pedido #<?php echo str_pad($compra['idCompra'], 6, '0', STR_PAD_LEFT); ?>
                                    </span>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock"></i>
                                        Aguardando Confirmação
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>Data do Pedido:</strong><br>
                                        <?php echo date('d/m/Y', strtotime($compra['dataCompra'])); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Itens:</strong><br>
                                        <?php echo $compra['totalItens']; ?> produtos
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <strong>Valor Total:</strong><br>
                                        <span class="order-value">R$ <?php echo number_format($compra['valorTotal'], 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <strong>Status:</strong><br>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($compra['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <form method="POST">
                                        <input type="hidden" name="id_compra" value="<?php echo $compra['idCompra']; ?>">
                                        <button type="submit" name="confirmar_recebimento" class="btn <?php echo $btnClass; ?> w-100">
                                            <i class="fas <?php echo $btnIcon; ?>"></i>
                                            <?php echo $btnText; ?>
                                        </button>
                                    </form>
                                    <?php if ($isEmTransporte): ?>
                                        <div class="mt-2 text-center">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Ao confirmar, o pedido será marcado como "Entregue"
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <h3 class="empty-title">Nenhum pedido aguardando confirmação</h3>
                        <p class="empty-description">
                            Todos os seus pedidos em transporte ou entregues já foram confirmados ou não há pedidos aguardando confirmação no momento.
                        </p>
                        <a href="pedidos.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Voltar para Meus Pedidos
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>