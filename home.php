<?php
// Definir base URL se necessário
$base_url = 'http://localhost/jardimSecret/';

require 'conexao/conecta.php';

// Verificar se está logado
session_start();
if (isset($_SESSION['id'])) {
    $id = $_SESSION["id"];
    $nomeUsuario = $_SESSION["nomeUsuario"] ?? '';
} else {
    header("Location: login.php");
    exit();
}

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT nomeCompleto FROM tb_usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$nomeCompleto = $usuario['nomeCompleto'] ?? '';

// Buscar produtos em destaque (últimos 4 produtos)
$produtosDestaque = $conn->query("SELECT * FROM tb_produto ORDER BY idProduto DESC LIMIT 4");

// Buscar categorias populares
$categoriasPopulares = $conn->query("SELECT * FROM tb_tipoproduto LIMIT 3");

$produtosPromocao = $conn->query("
    SELECT p.*, pr.valorPromocional, pr.percentualPromocao 
    FROM tb_produto p 
    INNER JOIN tb_promocao pr ON p.idProduto = pr.idProduto 
    ORDER BY pr.percentualPromocao DESC 
    LIMIT 4
");

// Contar itens no carrinho
$carrinhoCount = $conn->query("SELECT SUM(quantProduto) as total FROM tb_carrinho WHERE idUsuario = $id");
$totalCarrinho = $carrinhoCount->fetch_assoc()['total'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início | Jardim Secret</title>
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
            --dourado: #d4af37;
            --verde-promocao: #28a745;
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

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            color: var(--branco);
            padding: 80px 10%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .welcome-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--branco);
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .welcome-section p {
            font-size: 1.2rem;
            margin-bottom: 25px;
            opacity: 0.9;
        }

        .user-name {
            color: var(--dourado);
            font-weight: 600;
        }

        .btn {
            display: inline-block;
            background: var(--branco);
            color: var(--rosa-primario);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transicao);
            box-shadow: var(--sombra);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* Quick Actions */
        .quick-actions {
            padding: 60px 10%;
            background: var(--branco);
        }

        .section-title {
            text-align: center;
            font-size: 2.2rem;
            color: var(--destaque);
            margin-bottom: 40px;
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: var(--rosa-primario);
            margin: 15px auto 0;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .action-card {
            background: var(--rosa-claro);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--sombra);
            transition: var(--transicao);
            text-align: center;
            padding: 40px 20px;
            border: 2px solid transparent;
        }

        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            border-color: var(--rosa-primario);
        }

        .action-icon {
            font-size: 2.5rem;
            color: var(--rosa-primario);
            margin-bottom: 20px;
        }

        .action-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--destaque);
            margin-bottom: 15px;
        }

        .action-description {
            font-size: 0.95rem;
            color: var(--cinza);
            margin-bottom: 20px;
        }

        .action-link {
            display: inline-block;
            color: var(--rosa-primario);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transicao);
        }

        .action-link:hover {
            color: var(--rosa-escuro);
            text-decoration: underline;
        }

        /* Featured Products */
        .featured-section {
            padding: 60px 10%;
            background: var(--rosa-claro);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: var(--branco);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--sombra);
            transition: var(--transicao);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .product-image-container {
            height: 200px;
            width: 100%;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f8f8;
        }

        .product-image {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: var(--transicao);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--destaque);
        }

        .product-description {
            font-size: 0.9rem;
            color: var(--cinza);
            margin-bottom: 15px;
            flex-grow: 1;
            overflow: hidden;
            position: relative;
            max-height: 60px;
            -webkit-line-clamp: 3;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            text-overflow: ellipsis;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--rosa-escuro);
            margin-bottom: 15px;
        }

        .product-price-original {
            font-size: 0.9rem;
            color: var(--cinza);
            text-decoration: line-through;
            margin-right: 10px;
        }

        .product-price-promotional {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--verde-promocao);
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--verde-promocao);
            color: var(--branco);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            align-items: center;
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
            width: 30px;
            height: 30px;
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
            width: 40px;
            height: 30px;
            text-align: center;
            border: none;
            border-left: 1px solid var(--rosa-claro);
            border-right: 1px solid var(--rosa-claro);
            font-weight: 500;
        }

        .add-to-cart {
            flex-grow: 1;
            background: var(--rosa-primario);
            color: var(--branco);
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transicao);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-to-cart:hover {
            background: var(--rosa-escuro);
            transform: translateY(-2px);
        }

        /* Promoções Section */
        .promotions-section {
            padding: 60px 10%;
            background: linear-gradient(135deg, var(--rosa-claro), var(--branco));
        }

        .promo-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .promo-icon {
            font-size: 3rem;
            color: var(--verde-promocao);
            margin-bottom: 15px;
        }

        .promo-title {
            font-size: 2.2rem;
            color: var(--destaque);
            margin-bottom: 10px;
        }

        .promo-subtitle {
            font-size: 1.1rem;
            color: var(--cinza);
        }

        /* Categories */
        .categories-section {
            padding: 60px 10%;
            background: var(--rosa-claro);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .category-card {
            background: var(--branco);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--sombra);
            transition: var(--transicao);
            text-align: center;
            padding: 30px 20px;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .category-icon {
            font-size: 2rem;
            color: var(--rosa-primario);
            margin-bottom: 15px;
        }

        .category-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--destaque);
        }

        /* Stats */
        .stats-section {
            padding: 60px 10%;
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            color: var(--branco);
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dourado);
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        footer {
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            color: var(--branco);
            text-align: center;
            padding: 30px 10%;
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .welcome-section, .quick-actions, .featured-section, .promotions-section, .categories-section, .stats-section {
                padding: 40px 5%;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .actions-grid, .products-grid, .categories-grid, .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="welcome-content">
            <div class="welcome-icon">
                <i class="fas fa-spa"></i>
            </div>
            <h1>Bem-vinda de volta, <span class="user-name"><?php echo htmlspecialchars($nomeCompleto); ?></span>!</h1>
            <p>Seu jardim de beleza pessoal está pronto para novas descobertas</p>
            <a href="produtos.php" class="btn">Continuar Comprando</a>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions">
        <h2 class="section-title">Acesso Rápido</h2>
        <div class="actions-grid">
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3 class="action-title">Meu Perfil</h3>
                <p class="action-description">Gerencie suas informações pessoais e preferências</p>
                <a href="perfil.php" class="action-link">Acessar Perfil</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="action-title">Meu Carrinho</h3>
                <p class="action-description">Veja e gerencie os produtos no seu carrinho</p>
                <a href="carrinho.php" class="action-link">Ver Carrinho (<?php echo $totalCarrinho; ?>)</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="action-title">Meus Pedidos</h3>
                <p class="action-description">Acompanhe seus pedidos e histórico de compras</p>
                <a href="pedidos.php" class="action-link">Ver Pedidos</a>
            </div>
        </div>
    </section>

    <!-- Promoções Section -->
    <section class="promotions-section">
        <div class="promo-header">
            <div class="promo-icon">
                <i class="fas fa-tag"></i>
            </div>
            <h2 class="promo-title">Promoções Imperdíveis</h2>
            <p class="promo-subtitle">Aproveite nossas ofertas especiais com descontos exclusivos</p>
        </div>
        <div class="products-grid">
            <?php if ($produtosPromocao->num_rows > 0): ?>
                <?php while ($produto = $produtosPromocao->fetch_assoc()): ?>
                    <?php 
                    $valorOriginal = $produto['valorProduto'];
                    $valorPromocional = $produto['valorPromocional'];
                    $percentual = $produto['percentualPromocao'];
                    ?>
                    <div class="product-card">
                        <div class="discount-badge">
                            -<?php echo number_format($percentual, 0); ?>%
                        </div>
                        <div class="product-image-container">
                            <img class="product-image" src="data:image/jpeg;base64,<?php echo base64_encode($produto['imagemProduto']); ?>" alt="<?php echo htmlspecialchars($produto['nomeProduto']); ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($produto['nomeProduto']); ?></h3>
                            <div class="product-description">
                                <?php echo htmlspecialchars($produto['descricaoProduto']); ?>
                            </div>
                            <div class="product-price">
                                <span class="product-price-original">R$ <?php echo number_format($valorOriginal, 2, ',', '.'); ?></span>
                                <span class="product-price-promotional">R$ <?php echo number_format($valorPromocional, 2, ',', '.'); ?></span>
                            </div>
                            <div class="product-actions">
                                <div class="quantity-control">
                                    <button class="quantity-btn minus">-</button>
                                    <input type="number" class="quantity-input" value="1" min="1">
                                    <button class="quantity-btn plus">+</button>
                                </div>
                                <button class="add-to-cart" data-product-id="<?php echo $produto['idProduto']; ?>">
                                    <i class="fas fa-cart-plus"></i>
                                    Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <i class="fas fa-tag" style="font-size: 3rem; color: var(--rosa-primario); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--destaque); margin-bottom: 10px;">Nenhuma promoção no momento</h3>
                    <p style="color: var(--cinza);">Volte em breve para conferir nossas ofertas especiais!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-section">
        <h2 class="section-title">Novidades para Você</h2>
        <div class="products-grid">
            <?php while ($produto = $produtosDestaque->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-image-container">
                    <img class="product-image" src="data:image/jpeg;base64,<?php echo base64_encode($produto['imagemProduto']); ?>" alt="<?php echo htmlspecialchars($produto['nomeProduto']); ?>">
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($produto['nomeProduto']); ?></h3>
                    <div class="product-description">
                        <?php echo htmlspecialchars($produto['descricaoProduto']); ?>
                    </div>
                    <div class="product-price">R$ <?php echo number_format($produto['valorProduto'], 2, ',', '.'); ?></div>
                    <div class="product-actions">
                        <div class="quantity-control">
                            <button class="quantity-btn minus">-</button>
                            <input type="number" class="quantity-input" value="1" min="1">
                            <button class="quantity-btn plus">+</button>
                        </div>
                        <button class="add-to-cart" data-product-id="<?php echo $produto['idProduto']; ?>">
                            <i class="fas fa-cart-plus"></i>
                            Adicionar
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Popular Categories -->
    <section class="categories-section">
        <h2 class="section-title">Categorias Populares</h2>
        <div class="categories-grid">
            <?php while ($categoria = $categoriasPopulares->fetch_assoc()): ?>
            <a href="produtos.php?categoria=<?php echo urlencode($categoria['tipo']); ?>" class="category-card" style="text-decoration: none;">
                <div class="category-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3 class="category-name"><?php echo htmlspecialchars($categoria['tipo']); ?></h3>
            </a>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- User Stats -->
    <section class="stats-section">
        <h2 class="section-title" style="color: var(--branco);">Sua Jornada no Jardim</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalCarrinho; ?></div>
                <div class="stat-label">Itens no Carrinho</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">0</div>
                <div class="stat-label">Pedidos Realizados</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">Membro</div>
                <div class="stat-label">Status</div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <p>© 2025 Jardim Secret - Todos os direitos reservados</p>
            <p>Seu refúgio de beleza e elegância</p>
        </div>
    </footer>

    <script>
    // Controle de quantidade
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            
            if (this.classList.contains('minus')) {
                if (value > 1) {
                    input.value = value - 1;
                }
            } else {
                input.value = value + 1;
            }
        });
    });

    // Adicionar ao carrinho - FUNCIONALIDADE REAL
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = this.closest('.product-actions').querySelector('.quantity-input').value;
            const button = this;
            const originalHTML = this.innerHTML;
            
            // Feedback visual imediato
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
            this.style.backgroundColor = '#6c757d';
            this.disabled = true;
            
            // Fazer requisição AJAX para adicionar ao carrinho
            fetch('adicionar_carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `productId=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar contador do carrinho em ambos os menus
                    const cartCounts = document.querySelectorAll('.cart-count');
                    let currentTotal = 0;
                    
                    cartCounts.forEach(count => {
                        currentTotal = parseInt(count.textContent) || 0;
                        count.textContent = currentTotal + parseInt(quantity);
                    });
                    
                    // Feedback visual de sucesso
                    button.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
                    button.style.backgroundColor = '#28a745';
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.style.backgroundColor = '';
                        button.disabled = false;
                    }, 2000);
                    
                    console.log(`Adicionado ao carrinho: Produto ID ${productId}, Quantidade: ${quantity}`);
                } else {
                    // Feedback visual de erro
                    button.innerHTML = '<i class="fas fa-times"></i> Erro';
                    button.style.backgroundColor = '#dc3545';
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.style.backgroundColor = '';
                        button.disabled = false;
                    }, 2000);
                    
                    console.error('Erro ao adicionar ao carrinho:', data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                button.innerHTML = '<i class="fas fa-times"></i> Erro';
                button.style.backgroundColor = '#dc3545';
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.backgroundColor = '';
                    button.disabled = false;
                }, 2000);
            });
        });
    });
</script>
</body>
</html>