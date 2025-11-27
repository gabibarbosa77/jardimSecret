<?php
// Definir base URL se necessário
$base_url = 'http://localhost/jardimSecret/';  

// Verificar se está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require 'conexao/conecta.php';

$id = $_SESSION["id"];

$stmt = $conn->prepare("SELECT * FROM tb_usuario WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | Jardim Secret</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --rosa-primario: #d4a5a5;
            --rosa-escuro: #b87c7c;
            --rosa-claro: #f9f0f0;
            --destaque: #7b4c58;
            --bege: #f8f5f0;
            --branco: #ffffff;
            --cinza-claro: #f8f9fa;
            --cinza-medio: #e9ecef;
            --cinza-escuro: #495057;
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
            color: var(--cinza-escuro);
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            color: var(--branco);
            padding: 20px 10%;
            box-shadow: var(--sombra);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            gap: 25px;
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

        main {
            padding: 40px 10%;
            display: flex;
            justify-content: center;
        }

        .profile-container {
            width: 100%;
            max-width: 1000px;
        }

        /* ESTILO PARA ABAS */
        .tabs-container {
            display: flex;
            gap: 0;
            background: var(--branco);
            border-radius: 12px 12px 0 0;
            box-shadow: var(--sombra);
            overflow: hidden;
        }

        .tab-sidebar {
            width: 250px;
            background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
            padding: 30px 0;
        }

        .tab-item {
            padding: 15px 25px;
            color: var(--branco);
            cursor: pointer;
            transition: var(--transicao);
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .tab-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .tab-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: var(--branco);
        }

        .tab-item i {
            width: 20px;
            text-align: center;
        }

        .tab-content {
            flex: 1;
            padding: 0;
        }

        .tab-pane {
            display: none;
            padding: 40px;
            min-height: 500px;
        }

        .tab-pane.active {
            display: block;
        }

        .tab-title {
            font-size: 1.8rem;
            color: var(--destaque);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--rosa-claro);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .tab-title i {
            color: var(--rosa-primario);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--destaque);
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--cinza-medio);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transicao);
            background-color: var(--cinza-claro);
        }

        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--cinza-medio);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transicao);
            background-color: var(--cinza-claro);
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .form-input:focus,
        .form-textarea:focus {
            border-color: var(--rosa-primario);
            outline: none;
            background-color: var(--branco);
            box-shadow: 0 0 0 3px rgba(212, 165, 165, 0.2);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--cinza-escuro);
            transition: var(--transicao);
        }

        .toggle-password:hover {
            color: var(--rosa-primario);
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transicao);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--rosa-primario);
            color: var(--branco);
        }

        .btn-primary:hover {
            background-color: var(--rosa-escuro);
            transform: translateY(-2px);
            box-shadow: var(--sombra);
        }

        .btn-danger {
            background-color: var(--destaque);
            color: var(--branco);
        }

        .btn-danger:hover {
            background-color: #6a3a46;
            transform: translateY(-2px);
            box-shadow: var(--sombra);
        }

        .btn-secondary {
            background-color: var(--cinza-medio);
            color: var(--cinza-escuro);
        }

        .btn-secondary:hover {
            background-color: var(--cinza-escuro);
            color: var(--branco);
            transform: translateY(-2px);
            box-shadow: var(--sombra);
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

        /* Modal de confirmação */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            padding: 20px 0;
        }

        .modal-content {
            background-color: var(--branco);
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--sombra);
            position: relative;
        }

        .modal-content.large {
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--destaque);
        }

        .modal-text {
            margin-bottom: 20px;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        /* Estilos específicos para o modal de cartões */
        .cartao-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: var(--cinza-claro);
            border-radius: 8px;
            margin-bottom: 10px;
            transition: var(--transicao);
        }

        .cartao-item:hover {
            background: var(--cinza-medio);
        }

        .cartao-info {
            flex: 1;
        }

        .cartao-numero {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .cartao-detalhes {
            font-size: 0.9rem;
            color: var(--cinza-escuro);
        }

        .cartao-principal {
            color: var(--rosa-primario);
            font-weight: bold;
            margin-left: 10px;
        }

        .cartao-acoes {
            display: flex;
            gap: 5px;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .close {
            color: var(--cinza-escuro);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transicao);
            position: absolute;
            right: 15px;
            top: 15px;
        }

        .close:hover {
            color: var(--rosa-primario);
        }

        .cartoes-lista {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 25px;
            border: 1px solid var(--cinza-medio);
            border-radius: 8px;
            padding: 10px;
        }

        .form-cartao-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        /* Estilos para cupons */
        .cupom-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--rosa-claro), var(--branco));
            border: 2px dashed var(--rosa-primario);
            border-radius: 12px;
            margin-bottom: 15px;
            transition: var(--transicao);
            position: relative;
            overflow: hidden;
        }

        .cupom-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 95%, var(--rosa-primario) 95%);
            opacity: 0.1;
        }

        .cupom-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--sombra);
            border-color: var(--rosa-escuro);
        }

        .cupom-info {
            flex: 1;
        }

        .cupom-codigo {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--destaque);
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        .cupom-descricao {
            color: var(--cinza-escuro);
            margin-bottom: 8px;
        }

        .cupom-detalhes {
            font-size: 0.9rem;
            color: var(--cinza-escuro);
            margin-bottom: 5px;
        }

        .cupom-validade {
            font-size: 0.85rem;
            color: var(--rosa-escuro);
            font-weight: 500;
        }

        .cupom-desconto {
            background: linear-gradient(135deg, var(--rosa-primario), var(--destaque));
            color: var(--branco);
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            min-width: 120px;
            margin-left: 15px;
        }

        .cupom-valor {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .cupom-tipo {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .cupom-condicao {
            font-size: 0.75rem;
            margin-top: 3px;
            opacity: 0.8;
        }

        .cupom-acoes {
            margin-left: 15px;
        }

        .cupom-copiado {
            background-color: var(--destaque);
            color: var(--branco);
        }

        .cupons-lista {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 25px;
            padding: 10px;
        }

        .empty-cupons {
            text-align: center;
            padding: 40px 20px;
            color: var(--cinza-escuro);
        }

        .empty-cupons i {
            font-size: 3rem;
            color: var(--rosa-primario);
            margin-bottom: 15px;
        }

        /* Estilos para a aba de cupons */
        .cupons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .cupons-info {
            margin-top: 20px;
            padding: 15px;
            background: var(--rosa-claro);
            border-radius: 8px;
            border-left: 4px solid var(--rosa-primario);
        }

        /* Estilos para a aba de cartões */
        .cartoes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .cartao-card {
            background: var(--branco);
            border: 2px solid var(--cinza-medio);
            border-radius: 12px;
            padding: 20px;
            transition: var(--transicao);
            position: relative;
        }

        .cartao-card:hover {
            border-color: var(--rosa-primario);
            transform: translateY(-2px);
            box-shadow: var(--sombra);
        }

        .cartao-card.principal {
            border-color: var(--rosa-primario);
            background: linear-gradient(135deg, var(--rosa-claro), var(--branco));
        }

        .cartao-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .cartao-bandeira {
            font-weight: bold;
            color: var(--destaque);
        }

        .cartao-principal-badge {
            background: var(--rosa-primario);
            color: var(--branco);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .cartao-numero-mascarado {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--cinza-escuro);
        }

        .cartao-detalhes-card {
            font-size: 0.9rem;
            color: var(--cinza-escuro);
            margin-bottom: 5px;
        }

        .cartao-acoes-card {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        /* ESTILOS PARA FAVORITOS - CORRIGIDOS */
        .favoritos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .favorito-card {
            background: var(--branco);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--sombra);
            transition: var(--transicao);
            position: relative;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .favorito-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--rosa-primario);
        }

        .favorito-imagem-container {
            position: relative;
            width: 100%;
            height: 200px;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            background: var(--cinza-claro);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .favorito-imagem {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: var(--transicao);
        }

        .favorito-promo-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--rosa-primario);
            color: var(--branco);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 2;
        }

        .favorito-remover-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transicao);
            z-index: 2;
        }

        .favorito-remover-btn:hover {
            background: #dc3545;
            transform: scale(1.1);
        }

        .favorito-info {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .favorito-nome {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--destaque);
            margin-bottom: 8px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .favorito-marca {
            font-size: 0.9rem;
            color: var(--cinza-escuro);
            margin-bottom: 10px;
        }

        .favorito-descricao {
            font-size: 0.85rem;
            color: var(--cinza-escuro);
            margin-bottom: 15px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .favorito-preco-container {
            margin-bottom: 15px;
        }

        .favorito-preco-atual {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--rosa-escuro);
            margin-bottom: 5px;
        }

        .favorito-preco-original {
            font-size: 1rem;
            color: var(--cinza-escuro);
            text-decoration: line-through;
            margin-right: 8px;
        }

        .favorito-desconto {
            background: var(--destaque);
            color: var(--branco);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .favorito-estoque {
            font-size: 0.85rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .favorito-estoque.disponivel {
            color: #28a745;
        }

        .favorito-estoque.ultimos {
            color: #ffc107;
        }

        .favorito-estoque.esgotado {
            color: #dc3545;
        }

        .favorito-acoes {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .favorito-btn {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transicao);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-weight: 500;
        }

        .favorito-btn.carrinho {
            background: var(--rosa-primario);
            color: var(--branco);
        }

        .favorito-btn.carrinho:hover {
            background: var(--rosa-escuro);
            transform: translateY(-2px);
        }

        .favorito-btn.carrinho:disabled {
            background: var(--cinza-medio);
            color: var(--cinza-escuro);
            cursor: not-allowed;
            transform: none;
        }

        .favorito-btn.remover {
            background: var(--cinza-medio);
            color: var(--cinza-escuro);
            flex: 0 0 auto;
            width: 40px;
        }

        .favorito-btn.remover:hover {
            background: var(--cinza-escuro);
            color: var(--branco);
        }

        .favorito-data {
            font-size: 0.8rem;
            color: var(--cinza-escuro);
            margin-top: 10px;
            text-align: center;
            border-top: 1px solid var(--cinza-medio);
            padding-top: 10px;
        }

        .empty-favoritos {
            text-align: center;
            padding: 60px 20px;
            color: var(--cinza-escuro);
            grid-column: 1 / -1;
        }

        .empty-favoritos i {
            font-size: 4rem;
            color: var(--rosa-primario);
            margin-bottom: 20px;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .form-cartao-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 10% auto;
                padding: 20px;
            }
            
            .modal-content.large {
                max-width: 95%;
            }
            
            .tabs-container {
                flex-direction: column;
            }
            
            .tab-sidebar {
                width: 100%;
                padding: 0;
            }
            
            .tab-item {
                padding: 12px 20px;
                border-left: none;
                border-bottom: 3px solid transparent;
            }
            
            .tab-item.active {
                border-left: none;
                border-bottom-color: var(--branco);
            }

            .favoritos-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }

            .favorito-imagem-container {
                height: 180px;
            }
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>

    <main>
        <div class="profile-container">
            <!-- LAYOUT COM ABAS -->
            <div class="tabs-container">
                <div class="tab-sidebar">
                    <div class="tab-item active" data-tab="perfil">
                        <i class="fas fa-user"></i>
                        Perfil
                    </div>
                    <div class="tab-item" data-tab="favoritos">
                        <i class="fas fa-heart"></i>
                        Meus Favoritos
                    </div>
                    <div class="tab-item" data-tab="cartoes">
                        <i class="fas fa-credit-card"></i>
                        Meus Cartões
                    </div>
                    <div class="tab-item" data-tab="cupons">
                        <i class="fas fa-ticket-alt"></i>
                        Cupons Disponíveis
                    </div>
                    <div class="tab-item" data-tab="seguranca">
                        <i class="fas fa-shield-alt"></i>
                        Segurança
                    </div>
                </div>
                
                <div class="tab-content">
                    <!-- ABA PERFIL -->
                    <div class="tab-pane active" id="perfil">
                        <h2 class="tab-title">
                            <i class="fas fa-user-edit"></i>
                            Meu Perfil
                        </h2>
                        
                        <form method="POST" action="updatePerfil.php" id="profileForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nomeCompleto" class="form-label">Nome completo</label>
                                    <input type="text" id="nomeCompleto" name="nomeCompleto" class="form-input" required value="<?php echo htmlspecialchars($usuario['nomeCompleto']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="nomeUsuario" class="form-label">Nome de usuário</label>
                                    <input type="text" id="nomeUsuario" name="nomeUsuario" class="form-input" required value="<?php echo htmlspecialchars($usuario['nomeUsuario']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($usuario['emailUsuario']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" id="telefone" name="telefone" class="form-input" required value="<?php echo htmlspecialchars($usuario['telefoneUsuario']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="dataNasc" class="form-label">Data de Nascimento</label>
                                    <input type="text" id="dataNasc" name="dataNasc" class="form-input" required value="<?php echo htmlspecialchars($usuario['dataNasc']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="cpf" class="form-label">CPF</label>
                                    <input type="text" id="cpf" name="cpf" class="form-input" required value="<?php echo htmlspecialchars($usuario['cpfUsuario']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="cep" class="form-label">CEP</label>
                                    <input type="text" id="cep" name="cep" class="form-input" required value="<?php echo htmlspecialchars($usuario['cepUsuario']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="endereco" class="form-label">Endereço</label>
                                    <input type="text" id="endereco" name="endereco" class="form-input" required value="<?php echo htmlspecialchars($usuario['enderecoUsuario']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="text" id="numero" name="numero" class="form-input" required value="<?php echo htmlspecialchars($usuario['numUsuario']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="bairro" class="form-label">Bairro</label>
                                    <input type="text" id="bairro" name="bairro" class="form-input" required value="<?php echo htmlspecialchars($usuario['bairroUsuario']); ?>">
                                </div>
                            
                                <div class="form-group">
                                    <label for="complemento" class="form-label">Complemento</label>
                                    <input type="text" id="complemento" name="complemento" class="form-input" required value="<?php echo htmlspecialchars($usuario['complemento']); ?>">
                                </div>

                                <!-- Instruções de Entrega -->
                                <div class="form-group form-group-full">
                                    <label for="instrucaoEntrega" class="form-label">
                                        <i class="fas fa-truck"></i>
                                        Instruções de Entrega
                                    </label>
                                    <textarea 
                                        id="instrucaoEntrega" 
                                        name="instrucaoEntrega" 
                                        class="form-textarea" 
                                        placeholder="Ex: Deixar na portaria, Entregar com o zelador, Tocar campainha da casa dos fundos, etc."
                                        maxlength="535"
                                    ><?php echo htmlspecialchars($usuario['instrucaoEntrega'] ?? ''); ?></textarea>
                                    <small style="color: var(--cinza-escuro); font-size: 0.8rem; margin-top: 5px; display: block;">
                                        Instruções especiais para a entrega dos seus pedidos (opcional)
                                    </small>
                                </div>
                            </div>
                            
                            <div class="button-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Atualizar Perfil
                                </button>
                                
                                <a href="sair.php" class="btn btn-secondary">
                                    <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- ABA FAVORITOS - CORRIGIDA -->
                    <div class="tab-pane" id="favoritos">
                        <h2 class="tab-title">
                            <i class="fas fa-heart"></i>
                            Meus Favoritos
                        </h2>
                        
                        <div class="cupons-info">
                            <p style="color: var(--cinza-escuro); margin-bottom: 20px;">
                                Aqui estão todos os produtos que você salvou para comprar depois. 
                                Clique em "Adicionar ao Carrinho" para comprar ou em "Remover" para tirar da lista.
                            </p>
                        </div>
                        
                        <div class="favoritos-grid" id="listaFavoritos">
                            <p style="text-align: center; color: var(--cinza-escuro); grid-column: 1 / -1;">
                                Carregando favoritos...
                            </p>
                        </div>
                    </div>

                    <!-- ABA CARTÕES -->
                    <div class="tab-pane" id="cartoes">
                        <h2 class="tab-title">
                            <i class="fas fa-credit-card"></i>
                            Meus Cartões
                        </h2>
                        
                        <div class="cartoes-grid" id="listaCartoes">
                            <p style="text-align: center; color: var(--cinza-escuro); grid-column: 1 / -1;">
                                Carregando cartões...
                            </p>
                        </div>
                        
                        <div style="border-top: 2px solid var(--rosa-claro); padding-top: 30px; margin-top: 30px;">
                            <h3 style="color: var(--destaque); margin-bottom: 20px;">
                                <i class="fas fa-plus-circle"></i> Adicionar Novo Cartão
                            </h3>
                            
                            <form id="formCartao">
                                <div class="form-cartao-grid">
                                    <div class="form-group">
                                        <label for="numero_cartao" class="form-label">Número do Cartão</label>
                                        <input type="text" id="numero_cartao" name="numero_cartao" class="form-input" maxlength="19" placeholder="0000 0000 0000 0000" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="nome_titular" class="form-label">Nome do Titular</label>
                                        <input type="text" id="nome_titular" name="nome_titular" class="form-input" placeholder="Como está no cartão" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mes_validade" class="form-label">Mês de Validade</label>
                                        <select id="mes_validade" name="mes_validade" class="form-input" required>
                                            <option value="">Mês</option>
                                            <option value="01">01</option>
                                            <option value="02">02</option>
                                            <option value="03">03</option>
                                            <option value="04">04</option>
                                            <option value="05">05</option>
                                            <option value="06">06</option>
                                            <option value="07">07</option>
                                            <option value="08">08</option>
                                            <option value="09">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="ano_validade" class="form-label">Ano de Validade</label>
                                        <select id="ano_validade" name="ano_validade" class="form-input" required>
                                            <option value="">Ano</option>
                                            <!-- Os anos serão preenchidos via JavaScript -->
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" id="cvv" name="cvv" class="form-input" maxlength="4" placeholder="123" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="bandeira" class="form-label">Bandeira</label>
                                        <select id="bandeira" name="bandeira" class="form-input" required>
                                            <option value="">Selecione</option>
                                            <option value="Visa">Visa</option>
                                            <option value="Mastercard">Mastercard</option>
                                            <option value="American Express">American Express</option>
                                            <option value="Elo">Elo</option>
                                            <option value="Hipercard">Hipercard</option>
                                            <option value="Outro">Outro</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="apelido" class="form-label">Apelido (Opcional)</label>
                                        <input type="text" id="apelido" name="apelido" class="form-input" placeholder="Ex: Cartão Principal">
                                    </div>
                                    
                                    <div class="form-group" style="grid-column: span 2;">
                                        <label class="form-label" style="display: flex; align-items: center; gap: 8px;">
                                            <input type="checkbox" id="principal" name="principal" value="sim">
                                            Definir como cartão principal
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="button-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salvar Cartão
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ABA CUPONS -->
                    <div class="tab-pane" id="cupons">
                        <h2 class="tab-title">
                            <i class="fas fa-ticket-alt"></i>
                            Cupons Disponíveis
                        </h2>
                        
                        <div class="cupons-info">
                            <h4 style="color: var(--destaque); margin-bottom: 10px;">
                                <i class="fas fa-info-circle"></i> Como usar os cupons?
                            </h4>
                            <p style="font-size: 0.9rem; color: var(--cinza-escuro);">
                                • Copie o código do cupom<br>
                                • No checkout, cole o código no campo "Cupom de desconto"<br>
                                • O desconto será aplicado automaticamente ao seu pedido
                            </p>
                        </div>
                        
                        <div class="cupons-grid" id="listaCupons">
                            <p style="text-align: center; color: var(--cinza-escuro); grid-column: 1 / -1;">
                                Carregando cupons...
                            </p>
                        </div>
                    </div>

                    <!-- ABA SEGURANÇA -->
                    <div class="tab-pane" id="seguranca">
                        <h2 class="tab-title">
                            <i class="fas fa-shield-alt"></i>
                            Segurança
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group form-group-full">
                                <label for="senha_atual" class="form-label">Senha Atual</label>
                                <div class="password-wrapper">
                                    <input type="password" id="senha_atual" name="senha_atual" class="form-input" placeholder="Digite sua senha atual" required>
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword('senha_atual')"></i>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                <div class="password-wrapper">
                                    <input type="password" id="nova_senha" name="nova_senha" class="form-input" placeholder="Digite a nova senha" required>
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword('nova_senha')"></i>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                                <div class="password-wrapper">
                                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-input" placeholder="Confirme a nova senha" required>
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmar_senha')"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="button-group">
                            <button type="button" class="btn btn-primary" onclick="alterarSenha()">
                                <i class="fas fa-key"></i> Alterar Senha
                            </button>
                        </div>
                        
                        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid var(--rosa-claro);">
                            <h3 style="color: var(--destaque); margin-bottom: 15px;">
                                <i class="fas fa-exclamation-triangle"></i> Zona de Perigo
                            </h3>
                            <p style="color: var(--cinza-escuro); margin-bottom: 20px;">
                                Esta ação não pode ser desfeita. Todas as suas informações serão permanentemente excluídas.
                            </p>
                            <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
                                <i class="fas fa-trash-alt"></i> Excluir Minha Conta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Confirmação de Exclusão de Conta -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h3 class="modal-title">Confirmar Exclusão</h3>
            <p class="modal-text">Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.</p>
            <div class="modal-buttons">
                <button onclick="excluirConta()" class="btn btn-danger">Confirmar</button>
                <button onclick="fecharModal()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão de Cartão -->
    <div id="confirmExcluirCartaoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharExcluirCartaoModal()">&times;</span>
            <h3 class="modal-title">Confirmar Exclusão</h3>
            <p class="modal-text">Tem certeza que deseja excluir este cartão? Esta ação não pode ser desfeita.</p>
            <div class="modal-buttons">
                <button onclick="excluirCartaoConfirmado()" class="btn btn-danger">Confirmar</button>
                <button onclick="fecharExcluirCartaoModal()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <p>© 2025 Jardim Secret - Todos os direitos reservados</p>
            <p>CNPJ: 00.000.000/0001-00 | Rua Exemplo, 123 - Centro, Bauru/SP</p>
        </div>
    </footer>

    <script>
    // ========== FUNÇÕES DE CONTROLE DAS ABAS ==========
    
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar abas
        const tabItems = document.querySelectorAll('.tab-item');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabItems.forEach(item => {
            item.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remover classe active de todas as abas e painéis
                tabItems.forEach(tab => tab.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Adicionar classe active à aba e painel selecionados
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                
                // Carregar conteúdo específico da aba
                if (tabId === 'cartoes') {
                    carregarCartoes();
                } else if (tabId === 'cupons') {
                    carregarCupons();
                } else if (tabId === 'favoritos') {
                    carregarFavoritos();
                }
            });
        });
        
        // Inicializar componentes
        preencherAnosValidade();
        configurarMascarasCartao();
    });

    // ========== FUNÇÕES BÁSICAS ==========
    
    // Alternar visibilidade da senha
    function togglePassword(fieldId) {
        const senhaInput = document.getElementById(fieldId);
        const icon = senhaInput.parentElement.querySelector('.toggle-password');
        
        if (senhaInput.type === 'password') {
            senhaInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            senhaInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Modal de confirmação de exclusão de conta
    function confirmarExclusao() {
        document.getElementById('confirmModal').style.display = 'block';
    }
    
    function fecharModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }
    
    function excluirConta() {
        window.location.href = 'excluirConta.php';
    }

    // Alterar senha
    function alterarSenha() {
        const senhaAtual = document.getElementById('senha_atual').value;
        const novaSenha = document.getElementById('nova_senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;
        
        if (!senhaAtual || !novaSenha || !confirmarSenha) {
            alert('Por favor, preencha todos os campos de senha.');
            return;
        }
        
        if (novaSenha !== confirmarSenha) {
            alert('As senhas não coincidem.');
            return;
        }
        
        // Aqui você implementaria a lógica para alterar a senha
        alert('Funcionalidade de alteração de senha será implementada aqui.');
    }

    // ========== FUNÇÕES DE FAVORITOS - CORRIGIDAS ==========

    // Carregar favoritos do usuário
    function carregarFavoritos() {
        fetch('buscarFavoritos.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    exibirFavoritos(data.favoritos);
                } else {
                    document.getElementById('listaFavoritos').innerHTML = 
                        '<div class="empty-favoritos">' +
                        '<i class="fas fa-exclamation-triangle"></i>' +
                        '<p>Erro ao carregar favoritos: ' + data.message + '</p>' +
                        '</div>';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar favoritos:', error);
                document.getElementById('listaFavoritos').innerHTML = 
                    '<div class="empty-favoritos">' +
                    '<i class="fas fa-exclamation-triangle"></i>' +
                    '<p>Erro ao carregar favoritos.</p>' +
                    '</div>';
            });
    }

    // Exibir favoritos na lista - CORRIGIDA
    function exibirFavoritos(favoritos) {
        const listaFavoritos = document.getElementById('listaFavoritos');
        
        if (favoritos.length === 0) {
            listaFavoritos.innerHTML = 
                '<div class="empty-favoritos">' +
                '<i class="fas fa-heart"></i>' +
                '<h4>Nenhum produto favoritado</h4>' +
                '<p>Você ainda não adicionou nenhum produto aos favoritos.</p>' +
                '<p style="margin-top: 10px; font-size: 0.9rem;">Explore nossa loja e salve seus produtos favoritos!</p>' +
                '</div>';
            return;
        }
        
        let html = '';
        favoritos.forEach(favorito => {
            const imagemSrc = favorito.imagem ? `data:image/jpeg;base64,${favorito.imagem}` : 'https://via.placeholder.com/280x200?text=Imagem+Indisponível';
            const dataAdicionado = new Date(favorito.dataAdicionado).toLocaleDateString('pt-BR');
            
            let estoqueClass = 'disponivel';
            let estoqueText = 'Em estoque';
            let estoqueIcon = 'fa-check';
            
            if (favorito.estoque === 0) {
                estoqueClass = 'esgotado';
                estoqueText = 'Esgotado';
                estoqueIcon = 'fa-times';
            } else if (favorito.estoque < 10) {
                estoqueClass = 'ultimos';
                estoqueText = `Últimas ${favorito.estoque} unidades`;
                estoqueIcon = 'fa-exclamation-triangle';
            }
            
            // Calcular desconto se houver promoção
            const temPromocao = favorito.temPromocao && favorito.valorOriginal > favorito.precoFinal;
            const percentualDesconto = temPromocao ? 
                Math.round(((favorito.valorOriginal - favorito.precoFinal) / favorito.valorOriginal) * 100) : 0;
            
            html += `
                <div class="favorito-card">
                    <div class="favorito-imagem-container">
                        <img src="${imagemSrc}" alt="${favorito.nomeProduto}" class="favorito-imagem" 
                             onerror="this.src='https://via.placeholder.com/280x200?text=Imagem+Indisponível'">
                        ${temPromocao ? `<div class="favorito-promo-badge">-${percentualDesconto}%</div>` : ''}
                        <button class="favorito-remover-btn" onclick="removerFavorito(${favorito.idProduto})" title="Remover dos favoritos">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="favorito-info">
                        <div class="favorito-nome">${favorito.nomeProduto}</div>
                        <div class="favorito-marca">${favorito.marcaProduto}</div>
                        <div class="favorito-descricao">${favorito.descricao || 'Descrição não disponível'}</div>
                        
                        <div class="favorito-preco-container">
                            <div class="favorito-preco-atual">
                                R$ ${favorito.precoFinal.toFixed(2)}
                            </div>
                            ${temPromocao ? `
                                <div>
                                    <span class="favorito-preco-original">
                                        R$ ${favorito.valorOriginal.toFixed(2)}
                                    </span>
                                    <span class="favorito-desconto">-${percentualDesconto}%</span>
                                </div>
                            ` : ''}
                        </div>
                        
                        <div class="favorito-estoque ${estoqueClass}">
                            <i class="fas ${estoqueIcon}"></i>
                            ${estoqueText}
                        </div>
                        
                        <div class="favorito-acoes">
                            <button class="favorito-btn carrinho" onclick="adicionarAoCarrinho(${favorito.idProduto}, this)" 
                                    ${favorito.estoque === 0 ? 'disabled' : ''}>
                                <i class="fas fa-shopping-cart"></i> 
                                ${favorito.estoque === 0 ? 'Esgotado' : 'Adicionar ao Carrinho'}
                            </button>
                            <button class="favorito-btn remover" onclick="removerFavorito(${favorito.idProduto})" title="Remover">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <div class="favorito-data">
                            Adicionado em ${dataAdicionado}
                        </div>
                    </div>
                </div>
            `;
        });
        
        listaFavoritos.innerHTML = html;
    }

    // ========== FUNÇÃO ADICIONAR AO CARRINHO - CORRIGIDA ==========
    
    // Adicionar produto ao carrinho - FUNCIONALIDADE REAL
    function adicionarAoCarrinho(idProduto, botao) {
        // Verificar se o produto está esgotado
        if (botao.disabled) {
            mostrarMensagem('Este produto está esgotado!', 'error');
            return;
        }

        const button = botao;
        const originalHTML = button.innerHTML;
        
        // Feedback visual imediato
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
        button.style.backgroundColor = '#6c757d';
        button.disabled = true;
        
        // Fazer requisição AJAX para adicionar ao carrinho
        fetch('adicionar_carrinho.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `productId=${idProduto}&quantity=1`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Atualizar contador do carrinho em ambos os menus
                const cartCounts = document.querySelectorAll('.cart-count');
                let currentTotal = 0;
                
                cartCounts.forEach(count => {
                    currentTotal = parseInt(count.textContent) || 0;
                    count.textContent = currentTotal + 1;
                });
                
                // Feedback visual de sucesso
                button.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
                button.style.backgroundColor = '#28a745';
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.backgroundColor = '';
                    button.disabled = false;
                }, 2000);
                
                mostrarMensagem('Produto adicionado ao carrinho!', 'success');
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
                mostrarMensagem('Erro ao adicionar ao carrinho: ' + data.message, 'error');
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
            
            mostrarMensagem('Erro ao adicionar ao carrinho. Tente novamente.', 'error');
        });
    }

    // Função para mostrar mensagens
    function mostrarMensagem(mensagem, tipo) {
        // Criar elemento de mensagem
        const mensagemDiv = document.createElement('div');
        mensagemDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            max-width: 300px;
        `;
        
        if (tipo === 'success') {
            mensagemDiv.style.backgroundColor = '#28a745';
        } else {
            mensagemDiv.style.backgroundColor = '#dc3545';
        }
        
        mensagemDiv.textContent = mensagem;
        document.body.appendChild(mensagemDiv);
        
        // Remover após 3 segundos
        setTimeout(() => {
            mensagemDiv.style.opacity = '0';
            mensagemDiv.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (mensagemDiv.parentNode) {
                    mensagemDiv.parentNode.removeChild(mensagemDiv);
                }
            }, 300);
        }, 3000);
    }

    // Remover produto dos favoritos
    function removerFavorito(idProduto) {
        if (!confirm('Tem certeza que deseja remover este produto dos favoritos?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('idProduto', idProduto);
        
        fetch('removerFavorito.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                carregarFavoritos();
                mostrarMensagem('Produto removido dos favoritos!', 'success');
            } else {
                mostrarMensagem('Erro ao remover favorito: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao remover favorito.', 'error');
        });
    }

    // ========== FUNÇÕES DE CARTÕES ==========

    let cartoes = [];
    let cartaoParaExcluir = null;

    // Carregar cartões do usuário
    function carregarCartoes() {
        fetch('buscarCartoes.php')
            .then(response => response.json())
            .then(data => {
                cartoes = data;
                exibirCartoes();
            })
            .catch(error => {
                console.error('Erro ao carregar cartões:', error);
                document.getElementById('listaCartoes').innerHTML = 
                    '<p style="text-align: center; color: var(--cinza-escuro); grid-column: 1 / -1;">Erro ao carregar cartões.</p>';
            });
    }

    // Exibir cartões na lista
    function exibirCartoes() {
        const listaCartoes = document.getElementById('listaCartoes');
        
        if (cartoes.length === 0) {
            listaCartoes.innerHTML = 
                '<div style="text-align: center; color: var(--cinza-escuro); grid-column: 1 / -1; padding: 40px;">' +
                '<i class="fas fa-credit-card" style="font-size: 3rem; color: var(--rosa-primario); margin-bottom: 15px;"></i>' +
                '<h4>Nenhum cartão cadastrado</h4>' +
                '<p>Adicione seu primeiro cartão para facilitar suas compras!</p>' +
                '</div>';
            return;
        }
        
        let html = '';
        cartoes.forEach(cartao => {
            const numeroMascarado = '**** **** **** ' + (cartao.numero_cartao ? cartao.numero_cartao.slice(-4) : '****');
            const isPrincipal = cartao.principal === 'sim';
            
            html += `
                <div class="cartao-card ${isPrincipal ? 'principal' : ''}">
                    <div class="cartao-header">
                        <div class="cartao-bandeira">${cartao.bandeira}</div>
                        ${isPrincipal ? '<div class="cartao-principal-badge">Principal</div>' : ''}
                    </div>
                    <div class="cartao-numero-mascarado">${numeroMascarado}</div>
                    <div class="cartao-detalhes-card">
                        <strong>Titular:</strong> ${cartao.nome_titular}
                    </div>
                    <div class="cartao-detalhes-card">
                        <strong>Validade:</strong> ${cartao.mes_validade}/${cartao.ano_validade}
                    </div>
                    ${cartao.apelido ? `<div class="cartao-detalhes-card"><strong>Apelido:</strong> ${cartao.apelido}</div>` : ''}
                    <div class="cartao-acoes-card">
                        ${!isPrincipal ? `
                            <button onclick="definirComoPrincipal(${cartao.id})" class="btn btn-primary btn-small">
                                <i class="fas fa-star"></i> Principal
                            </button>
                        ` : ''}
                        <button onclick="confirmarExcluirCartao(${cartao.id})" class="btn btn-danger btn-small">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </div>
                </div>
            `;
        });
        
        listaCartoes.innerHTML = html;
    }

    // Confirmar exclusão de cartão
    function confirmarExcluirCartao(idCartao) {
        cartaoParaExcluir = idCartao;
        document.getElementById('confirmExcluirCartaoModal').style.display = 'block';
    }

    function fecharExcluirCartaoModal() {
        document.getElementById('confirmExcluirCartaoModal').style.display = 'none';
        cartaoParaExcluir = null;
    }

    // Excluir cartão confirmado
    function excluirCartaoConfirmado() {
        if (!cartaoParaExcluir) return;
        
        fetch('excluirCartao.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + cartaoParaExcluir
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                carregarCartoes();
                fecharExcluirCartaoModal();
                mostrarMensagem('Cartão excluído com sucesso!', 'success');
            } else {
                mostrarMensagem('Erro ao excluir cartão: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao excluir cartão.', 'error');
        });
    }

    // Definir cartão como principal
    function definirComoPrincipal(idCartao) {
        fetch('definirCartaoPrincipal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + idCartao
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                carregarCartoes();
                mostrarMensagem('Cartão definido como principal!', 'success');
            } else {
                mostrarMensagem('Erro ao definir cartão principal: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao definir cartão principal.', 'error');
        });
    }

    // ========== FUNÇÕES DE CUPONS ==========

    // Carregar cupons disponíveis
    function carregarCupons() {
        fetch('buscarCupons.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    exibirCupons(data.cupons);
                } else {
                    document.getElementById('listaCupons').innerHTML = 
                        '<div class="empty-cupons">' +
                        '<i class="fas fa-exclamation-triangle"></i>' +
                        '<p>Erro ao carregar cupons: ' + data.message + '</p>' +
                        '</div>';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar cupons:', error);
                document.getElementById('listaCupons').innerHTML = 
                    '<div class="empty-cupons">' +
                    '<i class="fas fa-exclamation-triangle"></i>' +
                    '<p>Erro ao carregar cupons.</p>' +
                    '</div>';
            });
    }

    // Exibir cupons na lista
    function exibirCupons(cupons) {
        const listaCupons = document.getElementById('listaCupons');
        
        if (cupons.length === 0) {
            listaCupons.innerHTML = 
                '<div class="empty-cupons">' +
                '<i class="fas fa-ticket-alt"></i>' +
                '<h4>Nenhum cupom disponível no momento</h4>' +
                '<p>Volte sempre para conferir novas promoções!</p>' +
                '</div>';
            return;
        }
        
        let html = '';
        cupons.forEach(cupom => {
            const hoje = new Date();
            const dataFim = new Date(cupom.data_fim);
            const diasRestantes = Math.ceil((dataFim - hoje) / (1000 * 60 * 60 * 24));
            
            let textoValidade = `Válido até ${formatarData(cupom.data_fim)}`;
            if (diasRestantes === 1) {
                textoValidade += ' (Último dia!)';
            } else if (diasRestantes <= 7) {
                textoValidade += ` (${diasRestantes} dias restantes)`;
            }
            
            const textoDesconto = cupom.tipo_desconto === 'valor_fixo' 
                ? `R$ ${parseFloat(cupom.valor_desconto).toFixed(2)}` 
                : `${parseFloat(cupom.valor_desconto).toFixed(0)}%`;
                
            const condicaoMinima = cupom.valor_minimo > 0 
                ? `Mínimo: R$ ${parseFloat(cupom.valor_minimo).toFixed(2)}` 
                : 'Sem valor mínimo';
                
            const usosDisponiveis = cupom.usos_maximos > 0 
                ? `${cupom.usos_maximos - cupom.usos_atuais} usos restantes` 
                : 'Usos ilimitados';

            html += `
                <div class="cupom-item">
                    <div class="cupom-info">
                        <div class="cupom-codigo">${cupom.codigo}</div>
                        <div class="cupom-descricao">${cupom.descricao || 'Cupom de desconto'}</div>
                        <div class="cupom-detalhes">
                            <strong>Tipo:</strong> ${cupom.tipo_desconto === 'valor_fixo' ? 'Desconto Fixo' : 'Desconto Percentual'}
                        </div>
                        <div class="cupom-detalhes">
                            <strong>Condições:</strong> ${condicaoMinima} | ${usosDisponiveis}
                        </div>
                        <div class="cupom-validade">
                            <i class="fas fa-clock"></i> ${textoValidade}
                        </div>
                    </div>
                    <div class="cupom-desconto">
                        <div class="cupom-valor">${textoDesconto}</div>
                        <div class="cupom-tipo">OFF</div>
                    </div>
                    <div class="cupom-acoes">
                        <button onclick="copiarCodigo('${cupom.codigo}')" class="btn btn-primary" title="Copiar código">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
            `;
        });
        
        listaCupons.innerHTML = html;
    }

    // Formatar data para exibição
    function formatarData(dataString) {
        const data = new Date(dataString);
        return data.toLocaleDateString('pt-BR');
    }

    // Copiar código do cupom
    function copiarCodigo(codigo) {
        navigator.clipboard.writeText(codigo).then(function() {
            // Feedback visual
            const buttons = document.querySelectorAll('.cupom-acoes .btn');
            buttons.forEach(button => {
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                button.classList.add('cupom-copiado');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('cupom-copiado');
                }, 2000);
            });
            
            // Mostrar mensagem de sucesso
            mostrarMensagem('Código copiado: ' + codigo + '\nCole no checkout para usar o cupom!', 'success');
        }).catch(function(err) {
            console.error('Erro ao copiar código: ', err);
            mostrarMensagem('Erro ao copiar código. Tente selecionar e copiar manualmente: ' + codigo, 'error');
        });
    }

    // ========== FUNÇÕES AUXILIARES ==========

    // Preencher anos de validade
    function preencherAnosValidade() {
        const selectAno = document.getElementById('ano_validade');
        const anoAtual = new Date().getFullYear();
        
        // Limpar opções existentes (exceto a primeira)
        while (selectAno.children.length > 1) {
            selectAno.removeChild(selectAno.lastChild);
        }
        
        for (let i = 0; i < 15; i++) {
            const ano = anoAtual + i;
            const option = document.createElement('option');
            option.value = ano;
            option.textContent = ano;
            selectAno.appendChild(option);
        }
    }

    // Configurar máscaras para campos do cartão
    function configurarMascarasCartao() {
        const numeroCartaoInput = document.getElementById('numero_cartao');
        if (numeroCartaoInput) {
            numeroCartaoInput.addEventListener('input', function() {
                mascaraNumeroCartao(this);
                
                // Detectar bandeira automaticamente
                const bandeira = detectarBandeira(this.value);
                if (bandeira) {
                    document.getElementById('bandeira').value = bandeira;
                }
            });
        }
        
        const cvvInput = document.getElementById('cvv');
        if (cvvInput) {
            cvvInput.addEventListener('input', function() {
                mascaraCVV(this);
            });
        }
        
        // Adicionar evento de submit ao formulário de cartão
        const formCartao = document.getElementById('formCartao');
        if (formCartao) {
            formCartao.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validar data de validade
                const mes = document.getElementById('mes_validade').value;
                const ano = document.getElementById('ano_validade').value;
                
                if (mes && ano) {
                    const dataValidade = new Date(ano, mes - 1);
                    const hoje = new Date();
                    
                    if (dataValidade < hoje) {
                        mostrarMensagem('Cartão com data de validade expirada!', 'error');
                        return false;
                    }
                }
                
                // Enviar formulário via AJAX
                const formData = new FormData(this);
                
                fetch('adicionarCartao.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Limpar formulário
                        this.reset();
                        // Recarregar lista de cartões
                        carregarCartoes();
                        mostrarMensagem('Cartão adicionado com sucesso!', 'success');
                    } else {
                        mostrarMensagem('Erro ao adicionar cartão: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarMensagem('Erro ao adicionar cartão.', 'error');
                });
            });
        }
    }

    // Máscara para número do cartão
    function mascaraNumeroCartao(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length > 16) {
            value = value.substring(0, 16);
        }
        
        // Adicionar espaços a cada 4 dígitos
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        
        input.value = value;
    }

    // Máscara para CVV
    function mascaraCVV(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length > 4) {
            value = value.substring(0, 4);
        }
        
        input.value = value;
    }

    // Detectar bandeira do cartão
    function detectarBandeira(numero) {
        numero = numero.replace(/\D/g, '');
        
        if (/^4/.test(numero)) {
            return 'Visa';
        } else if (/^5[1-5]/.test(numero)) {
            return 'Mastercard';
        } else if (/^3[47]/.test(numero)) {
            return 'American Express';
        }
        
        return '';
    }

    // Fechar modal ao clicar fora dele
    window.onclick = function(event) {
        const modals = document.getElementsByClassName('modal');
        for (let modal of modals) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    }
    </script>
</body>
</html>
