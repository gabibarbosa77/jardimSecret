<?php
// checkout_confirmacao.php
session_start();

// Verificar se completou todas as etapas
if (!isset($_SESSION['id']) || !isset($_SESSION['checkout_pagamento']) || !isset($_SESSION['checkout_endereco'])) {
    header('Location: carrinho.php');
    exit();
}

require 'conexao/conecta.php';

$idUsuario = $_SESSION['id'];
$pedido_id = $_SESSION['pedido_id'] ?? null;

// Se não tem pedido ID, significa que ainda não processou o pedido
if (!$pedido_id) {
    header('Location: checkout_pagamento.php');
    exit();
}

try {
    // Buscar dados da compra
    $stmt = $conn->prepare("
        SELECT c.*, u.nomeCompleto, u.emailUsuario, u.telefoneUsuario, s.status
        FROM tb_compra c
        INNER JOIN tb_usuario u ON c.idUsuario = u.id
        INNER JOIN tb_statuspedido s ON c.idStatus = s.idStatus
        WHERE c.idCompra = ? AND c.idUsuario = ?
    ");
    $stmt->bind_param("ii", $pedido_id, $idUsuario);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();
    
    if (!$pedido) {
        throw new Exception("Pedido não encontrado");
    }
    
    // Buscar itens do pedido
    $stmt_itens = $conn->prepare("
        SELECT ic.*, p.nomeProduto, p.imagemProduto, p.valorProduto,
               COALESCE(pr.valorPromocional, p.valorProduto) as precoFinal
        FROM tb_itemcompra ic
        INNER JOIN tb_produto p ON ic.idProduto = p.idProduto
        LEFT JOIN tb_promocao pr ON p.idProduto = pr.idProduto
        WHERE ic.idCompra = ?
    ");
    $stmt_itens->bind_param("i", $pedido_id);
    $stmt_itens->execute();
    $itens_pedido = $stmt_itens->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $erro = "Erro ao carregar dados do pedido: " . $e->getMessage();
}

// Limpar sessões do checkout (opcional)
// unset($_SESSION['checkout_endereco']);
// unset($_SESSION['checkout_pagamento']);
// unset($_SESSION['pedido_id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Pedido - Jardim Secret</title>
    <style>
        :root {
            --rosa-primario: #d4a5a5;
            --rosa-escuro: #b87c7c;
            --verde: #28a745;
            --vermelho: #dc3545;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .sucesso {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .detalhes-pedido {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .item-pedido {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .total {
            font-weight: bold;
            font-size: 1.2em;
            text-align: right;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--rosa-primario);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: var(--rosa-escuro);
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container">
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger">
                <?php echo $erro; ?>
            </div>
            <a href="index.php" class="btn">Voltar para a Loja</a>
        <?php else: ?>
            
            <div class="sucesso">
                <h1>✅ Pedido Confirmado!</h1>
                <p>Obrigado por sua compra! Seu pedido foi recebido com sucesso.</p>
            </div>

            <div class="detalhes-pedido">
                <h2>Detalhes do Pedido</h2>
                <p><strong>Número do Pedido:</strong> #<?php echo str_pad($pedido['idCompra'], 6, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['dataCompra'])); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($pedido['status']); ?></p>
                <p><strong>Método de Pagamento:</strong> <?php echo ucfirst($pedido['modoPagamento']); ?></p>
                
                <h3>Itens do Pedido</h3>
                <?php foreach ($itens_pedido as $item): ?>
                    <div class="item-pedido">
                        <div>
                            <strong><?php echo $item['nomeProduto']; ?></strong>
                            <br>
                            <small>Quantidade: <?php echo $item['quantProduto']; ?></small>
                        </div>
                        <div>R$ <?php echo number_format($item['precoFinal'] * $item['quantProduto'], 2, ',', '.'); ?></div>
                    </div>
                <?php endforeach; ?>
                
                <div class="total">
                    Total: R$ <?php echo number_format($pedido['valorTotal'], 2, ',', '.'); ?>
                </div>
            </div>

            <div class="detalhes-pedido">
                <h3>Informações de Entrega</h3>
                <?php $endereco = $_SESSION['checkout_endereco']; ?>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($endereco['nome']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($endereco['telefone']); ?></p>
                <p><strong>Endereço:</strong> 
                    <?php echo htmlspecialchars($endereco['endereco']); ?>, 
                    <?php echo htmlspecialchars($endereco['numero']); ?> - 
                    <?php echo htmlspecialchars($endereco['bairro']); ?>
                </p>
                <p><strong>CEP:</strong> <?php echo htmlspecialchars($endereco['cep']); ?></p>
                <?php if (!empty($endereco['complemento'])): ?>
                    <p><strong>Complemento:</strong> <?php echo htmlspecialchars($endereco['complemento']); ?></p>
                <?php endif; ?>
            </div>

            <div class="acoes">
                <a href="produtos.php" class="btn">Continuar Comprando</a>
                <a href="pedidos.php" class="btn" style="background-color: var(--verde);">Ver Meus Pedidos</a>
                <button onclick="window.print()" class="btn" style="background-color: #6c757d;">Imprimir Comprovante</button>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>