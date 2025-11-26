<?php
// Verificar se a sessão já não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Buscar contador real do carrinho
require 'conexao/conecta.php';
$idUsuario = $_SESSION['id'];
$carrinhoCount = $conn->query("SELECT COALESCE(SUM(quantProduto), 0) as total FROM tb_carrinho WHERE idUsuario = $idUsuario");
$totalCarrinho = $carrinhoCount->fetch_assoc()['total'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo isset($base_url) ? $base_url : 'http://localhost/jardimSecret/'; ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
    --rosa-primario: #d4a5a5;
    --rosa-escuro: #b87c7c;
    --rosa-claro: #f9f0f0;
    --branco: #ffffff;
    --cinza-escuro: #495057;
    --destaque: #7b4c58;
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
}

header {
    background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
    color: var(--branco);
    padding: 20px 10%;
    box-shadow: var(--sombra);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.logo {
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--branco);
}

.logo i {
    font-size: 1.8rem;
}

.logo:hover {
    color: var(--branco);
}

.nav-links {
    display: flex;
    gap: 25px;
    align-items: center;
}

.nav-links a {
    color: var(--branco);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transicao);
    position: relative;
}

.nav-links a:hover {
    color: var(--rosa-claro);
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--rosa-claro);
    transition: var(--transicao);
}

.nav-links a:hover::after {
    width: 100%;
}

.cart-icon {
    position: relative;
    font-size: 1.3rem;
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: var(--destaque);
    color: var(--branco);
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
}

/* Menu Mobile */
.menu-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--branco);
    font-size: 1.5rem;
    cursor: pointer;
}

.mobile-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
    box-shadow: var(--sombra);
    z-index: 1000;
}

.mobile-menu.active {
    display: block;
}

.mobile-nav-links {
    display: flex;
    flex-direction: column;
    padding: 20px;
}

.mobile-nav-links a {
    color: var(--branco);
    text-decoration: none;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-weight: 500;
    transition: var(--transicao);
}

.mobile-nav-links a:hover {
    color: var(--rosa-claro);
    padding-left: 10px;
}

.mobile-nav-links a:last-child {
    border-bottom: none;
}

.mobile-cart {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 0;
}

@media (max-width: 768px) {
    header {
        padding: 15px 5%;
    }

    .nav-links {
        display: none;
    }

    .menu-toggle {
        display: block;
    }

    .logo {
        font-size: 1.3rem;
    }
}

@media (min-width: 769px) {
    .mobile-menu {
        display: none !important;
    }
}
    </style>
</head>
<body>
    <header>
        <a href="home.php" class="logo">
            <i class="fas fa-leaf"></i>
            <span>Jardim Secret</span>
        </a>
        
        <nav class="nav-links">
            <a href="home.php">Home</a>
            <a href="produtos.php">Produtos</a>
            <a href="perfil.php">Perfil</a>
            <a href="pedidos.php">Pedidos</a>
            <a href="sair.php">Sair</a>
            <a href="carrinho.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count"><?php echo $totalCarrinho; ?></span>
            </a>
        </nav>

        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-nav-links">
                <a href="home.php">Home</a>
                <a href="produtos.php">Produtos</a>
                <a href="perfil.php">Perfil</a>
                <a href="sair.php">Sair</a>
                <div class="mobile-cart">
                    <a href="carrinho.php" style="border-bottom: none; padding: 0;">
                        <i class="fas fa-shopping-cart"></i>
                        Carrinho
                    </a>
                    <span class="cart-count"><?php echo $totalCarrinho; ?></span>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Menu mobile toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
            
            // Alterar ícone
            const icon = this.querySelector('i');
            if (mobileMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Fechar menu ao clicar em um link (mobile)
        document.querySelectorAll('.mobile-nav-links a').forEach(link => {
            link.addEventListener('click', function() {
                document.getElementById('mobileMenu').classList.remove('active');
                document.getElementById('menuToggle').querySelector('i').classList.remove('fa-times');
                document.getElementById('menuToggle').querySelector('i').classList.add('fa-bars');
            });
        });

        // Fechar menu ao redimensionar a janela para desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('mobileMenu').classList.remove('active');
                document.getElementById('menuToggle').querySelector('i').classList.remove('fa-times');
                document.getElementById('menuToggle').querySelector('i').classList.add('fa-bars');
            }
        });
    </script>
</body>
</html>