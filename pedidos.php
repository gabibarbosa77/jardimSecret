<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id'];

// // Mostrar mensagem de sucesso se existir
// if (isset($_SESSION['mensagem_sucesso'])) {
//     echo '<div class="alert-success jardim-theme alert-dismissible fade show" role="alert">
//             <i class="fas fa-check-circle"></i>
//             ' . $_SESSION['mensagem_sucesso'] . '
//             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//           </div>';
//     unset($_SESSION['mensagem_sucesso']);
// }

// PRIMEIRO: Buscar todos os status disponíveis do banco
$statusQuery = $conn->prepare("SELECT idStatus, status FROM tb_statuspedido ORDER BY idStatus");
$statusQuery->execute();
$statusResult = $statusQuery->get_result();

$statusMap = [];
$statusIds = [];
while ($status = $statusResult->fetch_assoc()) {
    $statusMap[$status['idStatus']] = strtolower($status['status']);
    $statusIds[strtolower($status['status'])] = $status['idStatus'];
}

// Buscar pedidos do usuário com status real
$pedidosQuery = $conn->prepare("
    SELECT 
        c.idCompra,
        c.modoPagamento,
        c.dataCompra,
        c.valorTotal,
        c.idStatus,
        s.status as nomeStatus,
        c.confirmacao_recebimento,
        c.codigoRastreio,
        COUNT(ic.idItem) as totalItens
    FROM tb_compra c
    LEFT JOIN tb_itemcompra ic ON c.idCompra = ic.idCompra
    JOIN tb_statuspedido s ON c.idStatus = s.idStatus
    WHERE c.idUsuario = ?
    GROUP BY c.idCompra
    ORDER BY c.dataCompra DESC
");
$pedidosQuery->bind_param("i", $idUsuario);
$pedidosQuery->execute();
$pedidosResult = $pedidosQuery->get_result();

$pedidos = [];
while ($pedido = $pedidosResult->fetch_assoc()) {
    $pedidos[] = $pedido;
}

// Buscar dados do usuário para o perfil
$userQuery = $conn->prepare("SELECT nomeCompleto FROM tb_usuario WHERE id = ?");
$userQuery->bind_param("i", $idUsuario);
$userQuery->execute();
$usuario = $userQuery->get_result()->fetch_assoc();

$conn->close();

// Função para determinar a classe CSS do status
function getStatusClass($status, $confirmacao_recebimento = 'pendente') {
    $statusLower = strtolower($status);
    
    $statusMap = [
        'entregue' => $confirmacao_recebimento === 'confirmado' ? 'status-entregue' : 'status-processando',
        'processando' => 'status-processando',
        'em transporte' => 'status-transporte',
        'cancelado' => 'status-cancelado',
        'pendente' => 'status-pendente',
        'pagamento confirmado' => 'status-confirmado'
    ];
    
    return $statusMap[$statusLower] ?? 'status-processando';
}

// Função para determinar o texto do status
function getStatusText($status, $confirmacao_recebimento = 'pendente') {
    $statusLower = strtolower($status);
    
    if ($statusLower === 'entregue' && $confirmacao_recebimento === 'pendente') {
        return 'Aguardando Confirmação';
    }
    
    return ucfirst($status);
}

// Função para determinar o filtro do status
function getStatusFilter($status, $confirmacao_recebimento = 'pendente') {
    $statusLower = strtolower($status);
    
    if ($statusLower === 'entregue' && $confirmacao_recebimento === 'pendente') {
        return 'entregue';
    }
    
    return str_replace(' ', '_', $statusLower);
}

// Função para determinar o progresso da timeline (COM OS STATUS REAIS)
function getTimelineProgress($idStatus, $confirmacao_recebimento = 'pendente', $statusIds) {
    // Se for cancelado, não mostra timeline
    if ($idStatus == ($statusIds['cancelado'] ?? 5)) return 0;
    
    // Mapeamento baseado na ordem natural dos status REAIS
    $progressMap = [
        $statusIds['pendente'] ?? 1 => 1,                    // Pendente
        $statusIds['pagamento confirmado'] ?? 12 => 2,       // Pagamento Confirmado
        $statusIds['processando'] ?? 7 => 3,                 // Processando
        $statusIds['em transporte'] ?? 8 => 4,               // Em Transporte
        $statusIds['entregue'] ?? 9 => $confirmacao_recebimento === 'confirmado' ? 5 : 4, // Entregue
    ];
    
    return $progressMap[$idStatus] ?? 1;
}

// Função para verificar se pode cancelar (COM STATUS REAIS)
function canCancelOrder($idStatus, $statusIds) {
    $cancelableStatuses = [
        $statusIds['pendente'] ?? 1,
        $statusIds['pagamento confirmado'] ?? 12
    ];
    return in_array($idStatus, $cancelableStatuses);
}

// Função para verificar se pode confirmar recebimento - AGORA PARA "EM TRANSPORTE"
function canConfirmReceipt($idStatus, $confirmacao_recebimento, $statusIds) {
    $emTransporteId = $statusIds['em transporte'] ?? 8;
    $entregueId = $statusIds['entregue'] ?? 9;
    
    // Permite confirmar se: status é "em transporte" OU "entregue" E confirmação está pendente
    $result = (($idStatus == $emTransporteId || $idStatus == $entregueId) && $confirmacao_recebimento == 'pendente');
    
    return $result;
}

// Função para verificar se pode avaliar (COM STATUS REAIS)
function canRateOrder($idStatus, $confirmacao_recebimento, $statusIds) {
    return ($idStatus == ($statusIds['entregue'] ?? 9) && $confirmacao_recebimento == 'confirmado');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos | Jardim Secret</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="csspedidos.css">
    <style>
        /* Estilos adicionais para o botão de confirmação */
        .btn-success {
            background: #28a745;
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-success i {
            margin-right: 5px;
        }
        
        .order-actions form {
            display: inline-block;
            margin: 0 5px;
        }
        
        /* Destaque para pedidos em transporte */
        .status-transporte {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .btn-warning {
            background: #ffc107;
            border: none;
            color: #212529;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            color: #212529;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <main>
        <div class="page-header">
            <h1 class="page-title">Meus Pedidos</h1>
            <p class="page-subtitle">Acompanhe seus pedidos e histórico de compras</p>
        </div>

        <!-- Mostrar mensagem de sucesso se existir -->
        <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
            <div class="alert-success jardim-theme alert-dismissible fade show" role="alert" style="margin: 20px auto; max-width: 1200px;">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['mensagem_sucesso']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensagem_sucesso']); ?>
        <?php endif; ?>

        <div class="user-welcome">
            <div class="welcome-icon">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="welcome-text">
                Olá, <span class="user-name"><?php echo htmlspecialchars($usuario['nomeCompleto']); ?></span>! 
                Aqui você pode acompanhar todos os seus pedidos.
            </div>
        </div>

        <?php if (!empty($pedidos)): ?>
            <div class="filters">
                <h3 class="filter-title">
                    <i class="fas fa-filter"></i>
                    Filtrar pedidos
                </h3>
                <div class="filter-options">
                    <button class="filter-btn active" onclick="filterOrders('all')">Todos</button>
                    <button class="filter-btn" onclick="filterOrders('entregue')">Entregues</button>
                    <button class="filter-btn" onclick="filterOrders('processando')">Processando</button>
                    <button class="filter-btn" onclick="filterOrders('em_transporte')">Em Transporte</button>
                    <button class="filter-btn" onclick="filterOrders('pagamento_confirmado')">Pagamento Confirmado</button>
                    <button class="filter-btn" onclick="filterOrders('cancelado')">Cancelados</button>
                </div>
            </div>

            <div class="orders-container" id="ordersContainer">
                <?php foreach ($pedidos as $pedido): ?>
                    <?php
                    $statusClass = getStatusClass($pedido['nomeStatus'], $pedido['confirmacao_recebimento']);
                    $statusText = getStatusText($pedido['nomeStatus'], $pedido['confirmacao_recebimento']);
                    $statusFilter = getStatusFilter($pedido['nomeStatus'], $pedido['confirmacao_recebimento']);
                    $timelineProgress = getTimelineProgress($pedido['idStatus'], $pedido['confirmacao_recebimento'], $statusIds);
                    $canCancel = canCancelOrder($pedido['idStatus'], $statusIds);
                    $canConfirm = canConfirmReceipt($pedido['idStatus'], $pedido['confirmacao_recebimento'], $statusIds);
                    $canRate = canRateOrder($pedido['idStatus'], $pedido['confirmacao_recebimento'], $statusIds);
                    
                    // Determinar qual botão mostrar baseado no status
                    $isEmTransporte = ($pedido['idStatus'] == ($statusIds['em transporte'] ?? 8));
                    $isEntregue = ($pedido['idStatus'] == ($statusIds['entregue'] ?? 9));
                    ?>
                    <div class="order-card" data-status="<?php echo $statusFilter; ?>">
                        <div class="order-header">
                            <div class="order-info">
                                <div class="order-number">Pedido #<?php echo str_pad($pedido['idCompra'], 6, '0', STR_PAD_LEFT); ?></div>
                                <div class="order-date">Realizado em: <?php echo date('d/m/Y H:i', strtotime($pedido['dataCompra'])); ?></div>
                                <?php if ($pedido['codigoRastreio']): ?>
                                    <div class="order-tracking">
                                        <i class="fas fa-truck"></i>
                                        Código Rastreio: <?php echo $pedido['codigoRastreio']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="order-status <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="order-summary">
                                <div class="summary-item">
                                    <span class="summary-label">Valor Total</span>
                                    <span class="summary-value">R$ <?php echo number_format($pedido['valorTotal'], 2, ',', '.'); ?></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Itens</span>
                                    <span class="summary-value"><?php echo $pedido['totalItens']; ?> produtos</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Pagamento</span>
                                    <div class="payment-method">
                                        <i class="fas fa-credit-card payment-icon"></i>
                                        <span class="summary-value"><?php echo ucfirst($pedido['modoPagamento']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($pedido['idStatus'] != ($statusIds['cancelado'] ?? 5) && $timelineProgress > 0): ?>
                                <div class="order-timeline">
                                    <h4 class="timeline-title">
                                        <i class="fas fa-shipping-fast"></i>
                                        Acompanhamento do Pedido
                                    </h4>
                                    <div class="timeline-steps">
                                        <div class="timeline-step <?php echo $timelineProgress >= 1 ? 'completed' : ''; ?> <?php echo $timelineProgress == 1 ? 'active' : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                            <div class="step-label">Pedido Realizado</div>
                                        </div>
                                        <div class="timeline-step <?php echo $timelineProgress >= 2 ? 'completed' : ''; ?> <?php echo $timelineProgress == 2 ? 'active' : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="step-label">Pagamento Confirmado</div>
                                        </div>
                                        <div class="timeline-step <?php echo $timelineProgress >= 3 ? 'completed' : ''; ?> <?php echo $timelineProgress == 3 ? 'active' : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div class="step-label">Processando</div>
                                        </div>
                                        <div class="timeline-step <?php echo $timelineProgress >= 4 ? 'completed' : ''; ?> <?php echo $timelineProgress == 4 ? 'active' : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                            <div class="step-label">Em Transporte</div>
                                        </div>
                                        <div class="timeline-step <?php echo $timelineProgress >= 5 ? 'completed' : ''; ?> <?php echo $timelineProgress == 5 ? 'active' : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="step-label">Entregue</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="order-actions">
                                <a href="detalhes_pedido.php?id=<?php echo $pedido['idCompra']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    Ver Detalhes
                                </a>
                                
                                <?php if ($canConfirm): ?>
                                    <?php if ($isEmTransporte): ?>
                                        <!-- Botão para pedidos EM TRANSPORTE -->
                                        <form method="POST" action="confirmar_recebimento.php" style="display: inline;">
                                            <input type="hidden" name="id_compra" value="<?php echo $pedido['idCompra']; ?>">
                                            <button type="submit" name="confirmar_recebimento" class="btn btn-warning">
                                                <i class="fas fa-truck-loading"></i>
                                                Confirmar Entrega
                                            </button>
                                        </form>
                                    <?php elseif ($isEntregue): ?>
                                        <!-- Botão para pedidos ENTREGUE -->
                                        <form method="POST" action="confirmar_recebimento.php" style="display: inline;">
                                            <input type="hidden" name="id_compra" value="<?php echo $pedido['idCompra']; ?>">
                                            <button type="submit" name="confirmar_recebimento" class="btn btn-success">
                                                <i class="fas fa-check-circle"></i>
                                                Confirmar Recebimento
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($canRate): ?>
                                    <button class="btn btn-outline" onclick="rateOrder(<?php echo $pedido['idCompra']; ?>)">
                                        <i class="fas fa-star"></i>
                                        Avaliar Pedido
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($canCancel): ?>
                                    <button class="btn btn-secondary" onclick="cancelOrder(<?php echo $pedido['idCompra']; ?>)">
                                        <i class="fas fa-times"></i>
                                        Cancelar Pedido
                                    </button>
                                <?php endif; ?>
                                
                                <a href="nota-fiscal.php?pedido=<?php echo $pedido['idCompra']; ?>" class="btn btn-outline">
                                    <i class="fas fa-file-invoice"></i>
                                    Nota Fiscal
                                </a>
                            </div>
                            
                            <!-- Informações do status atual -->
                            <div style="margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 5px; font-size: 12px; color: #666;">
                                <strong>Status:</strong> <?php echo $pedido['nomeStatus']; ?> | 
                                <strong>Confirmação:</strong> <?php echo $pedido['confirmacao_recebimento']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-orders">
                <div class="empty-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3 class="empty-title">Nenhum pedido encontrado</h3>
                <p class="empty-description">
                    Você ainda não realizou nenhum pedido. Que tal explorar nossos produtos 
                    e fazer sua primeira compra?
                </p>
                <a href="produtos.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i>
                    Fazer Minha Primeira Compra
                </a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function filterOrders(status) {
            const orders = document.querySelectorAll('.order-card');
            const filterBtns = document.querySelectorAll('.filter-btn');
            
            // Atualizar botões ativos
            filterBtns.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filtrar pedidos
            orders.forEach(order => {
                if (status === 'all' || order.dataset.status === status) {
                    order.style.display = 'block';
                } else {
                    order.style.display = 'none';
                }
            });
        }

        function rateOrder(orderId) {
            const rating = prompt('De 1 a 5 estrelas, como você avalia este pedido?');
            if (rating && rating >= 1 && rating <= 5) {
                alert(`Obrigado! Você avaliou o pedido com ${rating} estrela(s).`);
                // Aqui você enviaria a avaliação para o servidor
            }
        }

        function cancelOrder(orderId) {
            if (confirm('Tem certeza que deseja cancelar este pedido?')) {
                // Botão de loading
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelando...';
                btn.disabled = true;
                
                // Fazer a requisição
                fetch('cancelar_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_pedido=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pedido cancelado com sucesso!');
                        location.reload(); // Recarregar a página
                    } else {
                        alert('Erro: ' + data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    alert('Erro de conexão');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            }
        }
    </script>
</body>
</html>