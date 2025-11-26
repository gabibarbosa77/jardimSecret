<?php
// Verificar se está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao/conecta.php';

// Processar busca e filtro por categoria
$termoBusca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$categoriaFiltro = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$produtosPorCategoria = [];

// Buscar promoções ativas
$promocoes = [];
$query_promocoes = $conn->query("SELECT idProduto, valorPromocional, percentualPromocao FROM tb_promocao");
while ($promocao = $query_promocoes->fetch_assoc()) {
    $promocoes[$promocao['idProduto']] = $promocao;
}

if (!empty($termoBusca)) {
    // Busca produtos que correspondam ao termo de busca (código existente)
    $termoLike = "%" . $conn->real_escape_string($termoBusca) . "%";
    $query = $conn->prepare("SELECT p.*, t.tipo 
                            FROM tb_produto p
                            JOIN tb_tipoproduto t ON p.tipoProduto = t.idTipoProduto
                            WHERE p.nomeProduto LIKE ? OR p.descricaoProduto LIKE ? OR t.tipo LIKE ?");
    $query->bind_param("sss", $termoLike, $termoLike, $termoLike);
    $query->execute();
    $result = $query->get_result();
    
    // Organizar por categoria
    while ($produto = $result->fetch_assoc()) {
        $produtosPorCategoria[$produto['tipo']][] = $produto;
    }
} elseif (!empty($categoriaFiltro)) {
    // Filtro por categoria específica
    $query = $conn->prepare("SELECT p.*, t.tipo 
                            FROM tb_produto p
                            JOIN tb_tipoproduto t ON p.tipoProduto = t.idTipoProduto
                            WHERE t.tipo = ?");
    $query->bind_param("s", $categoriaFiltro);
    $query->execute();
    $result = $query->get_result();
    
    // Organizar por categoria (só uma categoria)
    while ($produto = $result->fetch_assoc()) {
        $produtosPorCategoria[$produto['tipo']][] = $produto;
    }
} else {
    // Buscar todas as categorias e produtos (código existente)
    $categorias = $conn->query("SELECT * FROM tb_tipoproduto");
    
    while ($categoria = $categorias->fetch_assoc()) {
        $query = $conn->prepare("SELECT * FROM tb_produto WHERE tipoProduto = ?");
        $query->bind_param("i", $categoria['idTipoProduto']);
        $query->execute();
        $result = $query->get_result();
        $produtos = [];
        
        while ($produto = $result->fetch_assoc()) {
            $produtos[] = $produto;
        }
        
        if (!empty($produtos)) {
            $produtosPorCategoria[$categoria['tipo']] = $produtos;
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
    <title>Produtos | Jardim Secret</title>
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
        --verde: #28a745;
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
    }

    main {
        padding: 40px 10%;
    }

    .category-section {
        margin-bottom: 50px;
    }

    .category-title {
        font-size: 1.8rem;
        color: var(--destaque);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--rosa-claro);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .category-title i {
        color: var(--rosa-primario);
    }

    .products-container {
        position: relative;
        padding: 0 40px;
    }

    .products-carousel {
        display: flex;
        gap: 25px;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding: 20px 0;
        scrollbar-width: none;
    }

    .products-carousel::-webkit-scrollbar {
        display: none;
    }

    .product-card {
        min-width: 280px;
        width: 280px;
        background: var(--branco);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--sombra);
        transition: var(--transicao);
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .promo-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: var(--verde);
        color: var(--branco);
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-name {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--destaque);
    }

    .product-description {
        font-size: 0.9rem;
        color: var(--cinza);
        margin-bottom: 15px;
        flex-grow: 1;
        overflow: hidden;
        position: relative;
    }

    .product-description.collapsed {
        max-height: 60px;
        -webkit-line-clamp: 3;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        text-overflow: ellipsis;
    }

    .product-price-container {
        margin-bottom: 15px;
    }

    .price-original {
        font-size: 0.9rem;
        color: var(--cinza);
        text-decoration: line-through;
        margin-right: 8px;
    }

    .price-promotional {
        color: var(--verde);
        font-weight: 700;
        font-size: 1.3rem;
    }

    .product-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--rosa-escuro);
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

    .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: var(--branco);
        color: var(--rosa-primario);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: var(--sombra);
        z-index: 10;
        transition: var(--transicao);
        border: none;
        font-size: 1.2rem;
    }

    .carousel-btn:hover {
        background: var(--rosa-primario);
        color: var(--branco);
        transform: translateY(-50%) scale(1.1);
    }

    .carousel-btn.prev {
        left: 0;
    }

    .carousel-btn.next {
        right: 0;
    }

    footer {
        background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
        color: var(--branco);
        text-align: center;
        padding: 30px 10%;
        margin-top: 50px;
    }

    .footer-content {
        max-width: 800px;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        main {
            padding: 30px 5%;
        }
        
        .products-container {
            padding: 0 20px;
        }
        
        .carousel-btn {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .product-card {
            min-width: 220px;
            width: 220px;
        }
    }

    /* Modal para descrição completa */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 100;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: var(--branco);
        padding: 30px;
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }

    .close-modal {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--cinza);
    }

    .modal-title {
        font-size: 1.5rem;
        color: var(--destaque);
        margin-bottom: 15px;
    }

    .modal-description {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--cinza);
    }

    /* Estilos da barra de pesquisa */
    .search-container {
        margin: 30px 10% 40px;
        display: flex;
        justify-content: center;
    }
    
    .search-box {
        position: relative;
        width: 100%;
        max-width: 600px;
    }
    
    .search-input {
        width: 100%;
        padding: 12px 20px;
        padding-right: 50px;
        border: 2px solid var(--rosa-primario);
        border-radius: 30px;
        font-size: 1rem;
        outline: none;
        transition: var(--transicao);
        box-shadow: var(--sombra);
    }
    
    .search-input:focus {
        border-color: var(--rosa-escuro);
        box-shadow: 0 0 10px rgba(212, 165, 165, 0.3);
    }
    
    .search-button {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--rosa-primario);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        transition: var(--transicao);
    }
    
    .search-button:hover {
        background: var(--rosa-escuro);
    }
    
    .search-results-info {
        text-align: center;
        margin-bottom: 30px;
        color: var(--destaque);
        font-size: 1.1rem;
    }
    
    .no-results {
        text-align: center;
        padding: 50px;
        color: var(--cinza);
        font-size: 1.2rem;
    }

    .no-results a {
        color: var(--rosa-primario);
        text-decoration: none;
        font-weight: 600;
    }

    .no-results a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="search-container">
        <form class="search-box" action="produtos.php" method="get">
            <input type="text" class="search-input" name="busca" placeholder="Buscar produtos..." value="<?php echo htmlspecialchars($termoBusca); ?>">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <main>
    <?php if (!empty($termoBusca)): ?>
        <div class="search-results-info">
            Resultados da busca por: <strong>"<?php echo htmlspecialchars($termoBusca); ?>"</strong>
        </div>
    <?php elseif (!empty($categoriaFiltro)): ?>
        <div class="search-results-info">
            Produtos da categoria: <strong>"<?php echo htmlspecialchars($categoriaFiltro); ?>"</strong>
            <a href="produtos.php" style="margin-left: 15px; color: var(--verde-primario); text-decoration: none;">
                <i class="fas fa-times"></i> Limpar filtro
            </a>
        </div>
    <?php endif; ?>
    
    <?php if (empty($produtosPorCategoria)): ?>
        <div class="no-results">
            <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px;"></i>
            <p>Nenhum produto encontrado<?php 
                if (!empty($termoBusca)) {
                    echo ' para "' . htmlspecialchars($termoBusca) . '"';
                } elseif (!empty($categoriaFiltro)) {
                    echo ' na categoria "' . htmlspecialchars($categoriaFiltro) . '"';
                }
            ?>.</p>
            <?php if (!empty($termoBusca) || !empty($categoriaFiltro)): ?>
                <p>Tente usar termos diferentes ou <a href="produtos.php">ver todos os produtos</a>.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($produtosPorCategoria as $categoria => $produtos): ?>
        <section class="category-section">
            <h2 class="category-title">
                <i class="fas fa-tag"></i>
                <?php echo htmlspecialchars($categoria); ?>
            </h2>
            
            <div class="products-container">
                <button class="carousel-btn prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="products-carousel" id="carousel-<?php echo preg_replace('/\s+/', '-', strtolower($categoria)); ?>">
                    <?php foreach ($produtos as $produto): ?>
                    <?php 
                    $temPromocao = isset($promocoes[$produto['idProduto']]);
                    $precoOriginal = floatval($produto['valorProduto']);
                    $precoPromocional = $temPromocao ? floatval($promocoes[$produto['idProduto']]['valorPromocional']) : $precoOriginal;
                    $percentualDesconto = $temPromocao ? $promocoes[$produto['idProduto']]['percentualPromocao'] : 0;
                    ?>
                    <div class="product-card">
                        <?php if ($temPromocao): ?>
                        <div class="promo-badge">
                            <i class="fas fa-tag"></i> PROMOÇÃO
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-image-container">
                            <img class="product-image" src="data:image/jpeg;base64,<?php echo base64_encode($produto['imagemProduto']); ?>" alt="<?php echo htmlspecialchars($produto['nomeProduto']); ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($produto['nomeProduto']); ?></h3>
                            <div class="product-description collapsed">
                                <?php echo htmlspecialchars($produto['descricaoProduto']); ?>
                            </div>
                            <span class="read-more">Leia mais</span>
                            
                            <div class="product-price-container">
                                <?php if ($temPromocao): ?>
                                    <span class="price-original">R$ <?php echo number_format($precoOriginal, 2, ',', '.'); ?></span>
                                    <span class="price-promotional">R$ <?php echo number_format($precoPromocional, 2, ',', '.'); ?></span>
                                    <small style="color: var(--verde); font-weight: 600;">
                                        -<?php echo number_format($percentualDesconto, 1, ',', '.'); ?>% OFF
                                    </small>
                                <?php else: ?>
                                    <span class="product-price">R$ <?php echo number_format($precoOriginal, 2, ',', '.'); ?></span>
                                <?php endif; ?>
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
                    <?php endforeach; ?>
                </div>
                
                <button class="carousel-btn next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

    <!-- Modal para descrição completa -->
    <div class="modal" id="descriptionModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3 class="modal-title" id="modalProductName"></h3>
            <p class="modal-description" id="modalProductDescription"></p>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <p>© 2025 Jardim Secret - Todos os direitos reservados</p>
            <p>CNPJ: 00.000.000/0001-00 | Rua Exemplo, 123 - Centro, Bauru/SP</p>
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

    // Controle do carrossel
    document.querySelectorAll('.products-container').forEach(container => {
        const carousel = container.querySelector('.products-carousel');
        const prevBtn = container.querySelector('.prev');
        const nextBtn = container.querySelector('.next');
        const productWidth = 280 + 25;
        const scrollAmount = productWidth * 2;
        
        nextBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
        
        prevBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        
        const updateButtons = () => {
            prevBtn.style.display = carousel.scrollLeft > 0 ? 'flex' : 'none';
            nextBtn.style.display = carousel.scrollLeft < (carousel.scrollWidth - carousel.clientWidth - 1) ? 'flex' : 'none';
        };
        
        carousel.addEventListener('scroll', updateButtons);
        updateButtons();
    });

    // Controle do "Leia mais"
    document.querySelectorAll('.read-more').forEach(btn => {
        btn.addEventListener('click', function() {
            const descriptionContainer = this.previousElementSibling;
            const productName = this.closest('.product-info').querySelector('.product-name').textContent;
            const productDescription = descriptionContainer.textContent;
            
            const modal = document.getElementById('descriptionModal');
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductDescription').textContent = productDescription;
            modal.style.display = 'flex';
        });
    });

    // Fechar modal
    document.querySelector('.close-modal').addEventListener('click', function() {
        document.getElementById('descriptionModal').style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('descriptionModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>
</body>
</html>