<?php
// Verificar se está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao/conecta.php';

$idUsuario = $_SESSION['id'];

// Buscar itens do carrinho (query atualizada)
$query = $conn->prepare("
    SELECT c.idCarrinho, c.idProduto, c.quantProduto, 
           p.nomeProduto, p.marcaProduto, p.valorProduto, p.imagemProduto,
           pr.valorPromocional, pr.percentualPromocao
    FROM tb_carrinho c
    INNER JOIN tb_produto p ON c.idProduto = p.idProduto
    LEFT JOIN tb_promocao pr ON p.idProduto = pr.idProduto
    WHERE c.idUsuario = ?
");
$query->bind_param("i", $idUsuario);
$query->execute();
$result = $query->get_result();

$itensCarrinho = [];
$subtotal = 0;
$totalDescontos = 0;
$totalItens = 0;
$subtotalSemDesconto = 0;

while ($item = $result->fetch_assoc()) {
    $precoOriginal = floatval($item['valorProduto']);
    
    // Verificar se tem promoção ativa
    if (isset($item['valorPromocional']) && $item['valorPromocional'] > 0) {
        $preco = floatval($item['valorPromocional']);
        $descontoItem = ($precoOriginal - $preco) * $item['quantProduto'];
    } else {
        $preco = $precoOriginal;
        $descontoItem = 0;
    }
    
    $precoTotalItem = $preco * $item['quantProduto'];
    $precoTotalSemDesconto = $precoOriginal * $item['quantProduto'];
    
    $itensCarrinho[] = $item;
    $subtotal += $precoTotalItem;
    $subtotalSemDesconto += $precoTotalSemDesconto;
    $totalDescontos += $descontoItem;
    $totalItens += $item['quantProduto'];
}

// CALCULAR FRETE DINAMICAMENTE
if ($subtotal >= 100) {
    $frete = 0.00; // Frete grátis para compras acima de R$ 100
} else {
    $frete = 15.50; // Frete fixo de R$ 15,50 para compras abaixo de R$ 100
}

$total = $subtotal + $frete;

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho | Jardim Secret</title>
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
            --vermelho: #dc3545;
            --dourado: #d4af37;
            --sombra: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transicao: all 0.3s ease;
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

        main {
            padding: 40px 10%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--destaque);
            margin-bottom: 10px;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: var(--cinza-claro);
        }

        .cart-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            align-items: start;
        }

        .cart-items {
            background: var(--branco);
            border-radius: 12px;
            box-shadow: var(--sombra);
            overflow: hidden;
        }

        .cart-header {
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            color: var(--branco);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h2 {
            font-size: 1.3rem;
        }

        .items-count {
            background: var(--destaque);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .cart-item {
            display: flex;
            padding: 20px;
            border-bottom: 1px solid var(--rosa-claro);
            gap: 20px;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 8px;
            overflow: hidden;
            background: var(--rosa-claro);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--destaque);
            margin-bottom: 5px;
        }

        .item-brand {
            font-size: 0.9rem;
            color: var(--cinza-claro);
            margin-bottom: 10px;
        }

        .item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--rosa-escuro);
            margin-bottom: 15px;
        }

        .price-original {
            font-size: 0.9rem;
            color: var(--cinza-claro);
            text-decoration: line-through;
            margin-right: 8px;
        }

        .price-promotional {
            color: var(--verde);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .discount-badge {
            background: var(--verde);
            color: var(--branco);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 8px;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--rosa-claro);
            border-radius: 8px;
            overflow: hidden;
        }

        .quantity-btn {
            background: var(--rosa-claro);
            border: none;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transicao);
            color: var(--rosa-primario);
        }

        .quantity-btn:hover {
            background: var(--rosa-primario);
            color: var(--branco);
        }

        .quantity-input {
            width: 50px;
            height: 35px;
            text-align: center;
            border: none;
            border-left: 1px solid var(--rosa-claro);
            border-right: 1px solid var(--rosa-claro);
            font-weight: 500;
            font-size: 1rem;
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--vermelho);
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: var(--transicao);
        }

        .remove-btn:hover {
            background: var(--rosa-claro);
        }

        .cart-summary {
            background: var(--branco);
            border-radius: 12px;
            box-shadow: var(--sombra);
            padding: 30px;
            position: sticky;
            top: 20px;
        }

        .summary-title {
            font-size: 1.5rem;
            color: var(--destaque);
            margin-bottom: 20px;
            text-align: center;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--rosa-claro);
        }

        .summary-line.total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--destaque);
            border-bottom: none;
            margin-top: 10px;
        }

        .summary-line.discount {
            color: var(--verde);
            font-weight: 600;
        }

        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            color: var(--branco);
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicao);
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 165, 165, 0.4);
        }

        .checkout-btn:disabled {
            background: var(--cinza-claro);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .continue-shopping {
            text-align: center;
            margin-top: 15px;
        }

        .continue-shopping a {
            color: var(--rosa-primario);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transicao);
        }

        .continue-shopping a:hover {
            text-decoration: underline;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart-icon {
            font-size: 4rem;
            color: var(--rosa-primario);
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            font-size: 1.5rem;
            color: var(--destaque);
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: var(--cinza-claro);
            margin-bottom: 30px;
        }

        .empty-cart-btn {
            display: inline-block;
            background: var(--rosa-primario);
            color: var(--branco);
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transicao);
        }

        .empty-cart-btn:hover {
            background: var(--rosa-escuro);
            transform: translateY(-2px);
        }

        .shipping-info {
            background: var(--rosa-claro);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }

        .shipping-info i {
            color: var(--verde);
            margin-right: 8px;
        }

        @media (max-width: 968px) {
            .cart-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            main {
                padding: 30px 5%;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                align-self: center;
            }
            
            .item-actions {
                justify-content: center;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <main>
        <div class="page-header">
            <h1 class="page-title">Meu Carrinho</h1>
            <p class="page-subtitle">Revise seus produtos antes de finalizar o pedido</p>
        </div>

        <?php if (empty($itensCarrinho)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Seu carrinho está vazio</h3>
                <p>Adicione alguns produtos incríveis ao seu carrinho</p>
                <a href="produtos.php" class="empty-cart-btn">
                    <i class="fas fa-shopping-bag"></i>
                    Continuar Comprando
                </a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-header">
                        <h2>Produtos no Carrinho</h2>
                        <span class="items-count"><?php echo $totalItens; ?> itens</span>
                    </div>
                    
                    <?php foreach ($itensCarrinho as $item): ?>
                        <div class="cart-item" data-item-id="<?php echo $item['idCarrinho']; ?>">
                            <div class="item-image">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($item['imagemProduto']); ?>" alt="<?php echo htmlspecialchars($item['nomeProduto']); ?>">
                            </div>
                            
                            <div class="item-details">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['nomeProduto']); ?></h3>
                                <p class="item-brand"><?php echo htmlspecialchars($item['marcaProduto']); ?></p>
                                
                                <div class="item-price">
                                    <?php 
                                    $precoOriginal = floatval($item['valorProduto']);
                                    $temPromocao = isset($item['valorPromocional']) && $item['valorPromocional'] > 0;
                                    $precoFinal = $temPromocao ? floatval($item['valorPromocional']) : $precoOriginal;
                                    ?>
                                    
                                    <?php if ($temPromocao): ?>
                                        <span class="price-original">R$ <?php echo number_format($precoOriginal, 2, ',', '.'); ?></span>
                                        <span class="price-promotional">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></span>
                                        <span class="discount-badge">
                                            -<?php 
                                                $percentualDesconto = (($precoOriginal - $precoFinal) / $precoOriginal) * 100;
                                                echo number_format($percentualDesconto, 0);
                                            ?>%
                                        </span>
                                    <?php else: ?>
                                        R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-actions">
                                    <div class="quantity-control">
                                        <button class="quantity-btn minus" onclick="updateQuantity(<?php echo $item['idCarrinho']; ?>, -1)">-</button>
                                        <input type="number" class="quantity-input" value="<?php echo $item['quantProduto']; ?>" min="1" readonly>
                                        <button class="quantity-btn plus" onclick="updateQuantity(<?php echo $item['idCarrinho']; ?>, 1)">+</button>
                                    </div>
                                    
                                    <button class="remove-btn" onclick="removeItem(<?php echo $item['idCarrinho']; ?>)">
                                        <i class="fas fa-trash"></i>
                                        Remover
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3 class="summary-title">Resumo do Pedido</h3>
                    
                    <div class="summary-line">
                        <span>Subtotal (<?php echo $totalItens; ?> itens)</span>
                        <span>R$ <?php echo number_format($subtotalSemDesconto, 2, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($totalDescontos > 0): ?>
                    <div class="summary-line discount">
                        <span>Descontos</span>
                        <span>-R$ <?php echo number_format($totalDescontos, 2, ',', '.'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-line">
                        <span>Frete</span>
                        <span>
                            <?php if ($frete == 0): ?>
                                <strong style="color: var(--verde);">Grátis</strong>
                            <?php else: ?>
                                R$ <?php echo number_format($frete, 2, ',', '.'); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="summary-line total">
                        <span>Total</span>
                        <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($totalDescontos > 0): ?>
                    <div class="shipping-info free-shipping">
                        <i class="fas fa-tag"></i>
                        <strong>Você economizou R$ <?php echo number_format($totalDescontos, 2, ',', '.'); ?> com descontos!</strong>
                    </div>
                    <?php endif; ?>
                    
                    <div class="shipping-info <?php echo $frete == 0 ? 'free-shipping' : ''; ?>">
                        <i class="fas fa-truck"></i>
                        <?php if ($frete == 0): ?>
                            <strong>Parabéns! Você ganhou frete grátis!</strong>
                        <?php else: ?>
                            <strong>Frete grátis</strong> para compras acima de R$ 100,00
                            <br>
                            <small>Compre mais R$ <?php echo number_format(100 - $subtotal, 2, ',', '.'); ?> para obter o frete grátis!</small>
                        <?php endif; ?>
                    </div>
                    
                    <button class="checkout-btn" onclick="proceedToCheckout()">
                        <i class="fas fa-lock"></i>
                        Finalizar Compra
                    </button>
                    
                    <div class="continue-shopping">
                        <a href="produtos.php">
                            <i class="fas fa-arrow-left"></i>
                            Continuar Comprando
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function updateQuantity(itemId, change) {
            const input = document.querySelector(`[data-item-id="${itemId}"] .quantity-input`);
            let newQuantity = parseInt(input.value) + change;
            
            if (newQuantity < 1) newQuantity = 1;
            
            // Atualizar via AJAX
            fetch('atualizar_carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `itemId=${itemId}&quantity=${newQuantity}&action=update`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = newQuantity;
                    location.reload(); // Recarregar para atualizar totais
                } else {
                    alert('Erro ao atualizar quantidade: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar quantidade');
            });
        }

        function removeItem(itemId) {
            if (!confirm('Tem certeza que deseja remover este item do carrinho?')) {
                return;
            }
            
            fetch('atualizar_carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `itemId=${itemId}&action=remove`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-item-id="${itemId}"]`).remove();
                    location.reload(); // Recarregar para atualizar contadores
                } else {
                    alert('Erro ao remover item: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao remover item');
            });
        }

        function proceedToCheckout() {
            // Redirecionar para página de checkout
            window.location.href = 'checkout_endereco.php';
        }

        // Atualizar contador do carrinho no menu
        document.addEventListener('DOMContentLoaded', function() {
            const cartCounts = document.querySelectorAll('.cart-count');
            const totalItens = <?php echo $totalItens; ?>;
            
            cartCounts.forEach(count => {
                count.textContent = totalItens;
            });
        });
    </script>
</body>
</html>