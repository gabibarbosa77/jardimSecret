<?php
session_start();
require 'conexao/conecta.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['id'];
$idPedido = $_GET['id'] ?? null;

if (!$idPedido) {
    header("Location: pedidos.php");
    exit();
}

// Buscar dados do pedido
$pedidoQuery = $conn->prepare("
    SELECT 
        c.idCompra,
        c.modoPagamento,
        c.dataCompra,
        c.valorTotal,
        c.valorFrete,
        c.valorDesconto,
        c.codigoRastreio,
        c.idStatus,
        s.status as nomeStatus,
        c.confirmacao_recebimento,
        u.nomeCompleto,
        u.enderecoUsuario,
        u.numUsuario,
        u.bairroUsuario,
        u.cepUsuario,
        u.complemento,
        u.telefoneUsuario
    FROM tb_compra c
    JOIN tb_statuspedido s ON c.idStatus = s.idStatus
    JOIN tb_usuario u ON c.idUsuario = u.id
    WHERE c.idCompra = ? AND c.idUsuario = ?
");
$pedidoQuery->bind_param("ii", $idPedido, $idUsuario);
$pedidoQuery->execute();
$pedido = $pedidoQuery->get_result()->fetch_assoc();

if (!$pedido) {
    header("Location: pedidos.php");
    exit();
}

// Buscar itens do pedido
$itensQuery = $conn->prepare("
    SELECT 
        ic.quantProduto,
        p.nomeProduto,
        p.marcaProduto,
        p.valorProduto,
        pr.valorPromocional
    FROM tb_itemcompra ic
    JOIN tb_produto p ON ic.idProduto = p.idProduto
    LEFT JOIN tb_promocao pr ON p.idProduto = pr.idProduto
    WHERE ic.idCompra = ?
");
$itensQuery->bind_param("i", $idPedido);
$itensQuery->execute();
$itens = $itensQuery->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido | Jardim Secret</title>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bege);
            color: var(--cinza);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--destaque);
            margin-bottom: 10px;
        }

        .back-link {
            color: var(--rosa-primario);
            text-decoration: none;
            font-weight: 600;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--branco);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.3rem;
            color: var(--destaque);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--rosa-claro);
            padding-bottom: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid var(--rosa-claro);
        }

        .info-label {
            font-weight: 600;
            color: var(--cinza);
        }

        .info-value {
            color: var(--destaque);
            font-weight: 500;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items-table th {
            background: var(--rosa-claro);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--destaque);
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid var(--rosa-claro);
        }

        .total-row {
            font-weight: 600;
            background: var(--rosa-claro);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-entregue { background: var(--verde); color: var(--branco); }
        .status-processando { background: var(--azul); color: var(--branco); }
        .status-transporte { background: var(--laranja); color: var(--branco); }
        .status-cancelado { background: var(--vermelho); color: var(--branco); }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--rosa-primario);
            color: var(--branco);
        }

        .btn-primary:hover {
            background: var(--rosa-escuro);
        }

        @media (max-width: 768px) {
            .order-info-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container">
        <div class="header">
            <h1 class="page-title">Detalhes do Pedido</h1>
            <a href="pedidos.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar para Meus Pedidos
            </a>
        </div>

        <div class="order-info-grid">
            <div>
                <!-- Informações do Pedido -->
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-bag"></i>
                        Pedido #<?php echo str_pad($pedido['idCompra'], 6, '0', STR_PAD_LEFT); ?>
                    </h3>
                    <div class="info-item">
                        <span class="info-label">Data do Pedido:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($pedido['dataCompra'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $pedido['nomeStatus'])); ?>">
                            <?php 
                            if ($pedido['idStatus'] == 4 && $pedido['confirmacao_recebimento'] == 'pendente') {
                                echo 'Aguardando Confirmação';
                            } else {
                                echo $pedido['nomeStatus'];
                            }
                            ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Método de Pagamento:</span>
                        <span class="info-value"><?php echo ucfirst($pedido['modoPagamento']); ?></span>
                    </div>
                    <?php if ($pedido['codigoRastreio']): ?>
                    <div class="info-item">
                        <span class="info-label">Código de Rastreio:</span>
                        <span class="info-value"><?php echo $pedido['codigoRastreio']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Itens do Pedido -->
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-boxes"></i>
                        Itens do Pedido
                    </h3>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor Unitário</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalItens = 0;
                            while ($item = $itens->fetch_assoc()): 
                                $preco = $item['valorPromocional'] ? $item['valorPromocional'] : $item['valorProduto'];
                                $subtotal = $preco * $item['quantProduto'];
                                $totalItens += $subtotal;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $item['nomeProduto']; ?></strong><br>
                                    <small>Marca: <?php echo $item['marcaProduto']; ?></small>
                                </td>
                                <td><?php echo $item['quantProduto']; ?></td>
                                <td>R$ <?php echo number_format($preco, 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="total-row">
                                <td colspan="3"><strong>Subtotal</strong></td>
                                <td><strong>R$ <?php echo number_format($totalItens, 2, ',', '.'); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <!-- Resumo do Pedido -->
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i>
                        Resumo do Pedido
                    </h3>
                    <div class="info-item">
                        <span class="info-label">Subtotal:</span>
                        <span class="info-value">R$ <?php echo number_format($totalItens, 2, ',', '.'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Frete:</span>
                        <span class="info-value">R$ <?php echo number_format($pedido['valorFrete'], 2, ',', '.'); ?></span>
                    </div>
                    <?php if ($pedido['valorDesconto'] > 0): ?>
                    <div class="info-item">
                        <span class="info-label">Desconto:</span>
                        <span class="info-value">- R$ <?php echo number_format($pedido['valorDesconto'], 2, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item" style="border-top: 2px solid var(--destaque); padding-top: 15px;">
                        <span class="info-label" style="font-size: 1.1rem;">Total:</span>
                        <span class="info-value" style="font-size: 1.1rem; color: var(--destaque);">
                            R$ <?php echo number_format($pedido['valorTotal'], 2, ',', '.'); ?>
                        </span>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Endereço de Entrega
                    </h3>
                    <div class="info-item">
                        <span class="info-label">Destinatário:</span>
                        <span class="info-value"><?php echo $pedido['nomeCompleto']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Endereço:</span>
                        <span class="info-value">
                            <?php echo $pedido['enderecoUsuario']; ?>, <?php echo $pedido['numUsuario']; ?><br>
                            <?php echo $pedido['bairroUsuario']; ?> - CEP: <?php echo $pedido['cepUsuario']; ?>
                            <?php if ($pedido['complemento']): ?><br>Complemento: <?php echo $pedido['complemento']; ?><?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Telefone:</span>
                        <span class="info-value"><?php echo $pedido['telefoneUsuario']; ?></span>
                    </div>
                </div>

                <!-- Ações -->
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i>
                        Ações
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="nota-fiscal.php?pedido=<?php echo $pedido['idCompra']; ?>" class="btn btn-primary">
                            <i class="fas fa-file-invoice"></i>
                            Nota Fiscal
                        </a>
                        <?php if ($pedido['idStatus'] == 4 && $pedido['confirmacao_recebimento'] == 'pendente'): ?>
                        <a href="confirmar_recebimento.php" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i>
                            Confirmar Recebimento
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>