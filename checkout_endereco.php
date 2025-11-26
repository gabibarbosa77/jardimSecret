<?php
session_start();
require 'conexao/conecta.php';

// Verificar se está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Verificar se há itens no carrinho
$idUsuario = $_SESSION['id'];
$cartQuery = $conn->prepare("SELECT COUNT(*) as total FROM tb_carrinho WHERE idUsuario = ?");
$cartQuery->bind_param("i", $idUsuario);
$cartQuery->execute();
$cartResult = $cartQuery->get_result()->fetch_assoc();

if ($cartResult['total'] == 0) {
    header("Location: carrinho.php");
    exit();
}

// Buscar dados do usuário
$userQuery = $conn->prepare("SELECT * FROM tb_usuario WHERE id = ?");
$userQuery->bind_param("i", $idUsuario);
$userQuery->execute();
$usuario = $userQuery->get_result()->fetch_assoc();

$complemento = isset($usuario['complemento']) ? $usuario['complemento'] : '';

// Buscar itens do carrinho para o resumo
$cartQuery = $conn->prepare("
    SELECT c.idCarrinho, c.idProduto, c.quantProduto, 
           p.nomeProduto, p.marcaProduto, p.valorProduto, p.imagemProduto,
           pr.valorPromocional
    FROM tb_carrinho c
    INNER JOIN tb_produto p ON c.idProduto = p.idProduto
    LEFT JOIN tb_promocao pr ON p.idProduto = pr.idProduto
    WHERE c.idUsuario = ?
");
$cartQuery->bind_param("i", $idUsuario);
$cartQuery->execute();
$cartResult = $cartQuery->get_result();

$itensCarrinho = [];
$subtotal = 0;
$totalItens = 0;

while ($item = $cartResult->fetch_assoc()) {
    $precoOriginal = $item['valorProduto'];
    $precoFinal = isset($item['valorPromocional']) ? $item['valorPromocional'] : $precoOriginal;
    $precoTotalItem = $precoFinal * $item['quantProduto'];
    
    $itensCarrinho[] = $item;
    $subtotal += $precoTotalItem;
    $totalItens += $item['quantProduto'];
}

// Cálculo do frete
$frete = $subtotal > 100 ? 0 : 15.00;
$total = $subtotal + $frete;

// Processar envio do formulário de endereço
if (isset($_POST['continuar_pagamento'])) {
    // Validar dados obrigatórios
    $requiredFields = ['nome', 'email', 'telefone', 'cep', 'endereco', 'numero', 'bairro'];
    $isValid = true;
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $isValid = false;
            break;
        }
    }
    
    if ($isValid) {
        // Salvar dados na sessão
        $_SESSION['checkout_endereco'] = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'],
            'cep' => $_POST['cep'],
            'endereco' => $_POST['endereco'],
            'numero' => $_POST['numero'],
            'bairro' => $_POST['bairro'],
            'complemento' => $_POST['complemento'] ?? ''
        ];
        
        // Redirecionar para próxima etapa
        header("Location: checkout_pagamento.php");
        exit();
    } else {
        $erroEndereco = "Por favor, preencha todos os campos obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Endereço de Entrega | Jardim Secret</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="csscheckout.css">
</head>
<body>
    <?php include 'menu.php'; ?>

    <main>
        <div class="page-header">
            <h1 class="page-title">Finalizar Pedido</h1>
            <p class="page-subtitle">Revise suas informações antes de confirmar a compra</p>
        </div>

        <div class="checkout-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Endereço</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Pagamento</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Confirmação</div>
            </div>
        </div>

        <div class="checkout-container">
            <div class="checkout-content">
                <!-- Seção de Entrega -->
                <div class="checkout-section">
                    <h2 class="section-title">
                        <i class="fas fa-truck"></i>
                        Endereço de Entrega
                    </h2>
                    
                    <?php if (isset($erroEndereco)): ?>
                        <div class="cupom-error" style="margin-bottom: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo $erroEndereco; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="enderecoForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nomeCompleto']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="cpf">CPF *</label>
                                <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($usuario['cpfUsuario']); ?>" required readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['emailUsuario']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefone">Telefone *</label>
                            <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefoneUsuario']); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cep">CEP *</label>
                                <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($usuario['cepUsuario']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="numero">Número *</label>
                                <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($usuario['numUsuario']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="endereco">Endereço *</label>
                            <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($usuario['enderecoUsuario']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="bairro">Bairro *</label>
                            <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($usuario['bairroUsuario']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento" name="complemento" 
                                   value="<?php echo htmlspecialchars($complemento); ?>" 
                                   placeholder="Apartamento, bloco, etc.">
                        </div>
                        
                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" name="continuar_pagamento" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-arrow-right"></i>
                                Continuar para Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="order-summary">
                <h3 class="summary-title">Resumo do Pedido</h3>
                
                <div class="order-items">
                    <?php foreach ($itensCarrinho as $item): ?>
                        <?php 
                        $precoOriginal = $item['valorProduto'];
                        $precoFinal = isset($item['valorPromocional']) ? $item['valorPromocional'] : $precoOriginal;
                        ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($item['imagemProduto']); ?>" alt="<?php echo htmlspecialchars($item['nomeProduto']); ?>">
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['nomeProduto']); ?></div>
                                <div class="item-quantity">Qtd: <?php echo $item['quantProduto']; ?></div>
                                <?php if (isset($item['valorPromocional'])): ?>
                                    <div style="font-size: 0.8rem; color: var(--verde);">
                                        <s>R$ <?php echo number_format($precoOriginal, 2, ',', '.'); ?></s>
                                        R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-price">
                                R$ <?php echo number_format($precoFinal * $item['quantProduto'], 2, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-line">
                    <span>Subtotal (<?php echo $totalItens; ?> itens)</span>
                    <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                </div>
                
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
                
                <div class="security-info">
                    <i class="fas fa-lock"></i>
                    <strong>Compra 100% segura</strong> - Seus dados estão protegidos
                </div>
                
                <div class="save-for-later">
                    <a href="carrinho.php">
                        <i class="fas fa-arrow-left"></i>
                        Voltar para o Carrinho
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Formatação de campos
        document.addEventListener('DOMContentLoaded', function() {
            // Formatar CPF
            const cpf = document.getElementById('cpf');
            if (cpf) {
                cpf.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    }
                });
            }

            // Formatar CEP
            const cep = document.getElementById('cep');
            if (cep) {
                cep.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 8) {
                        value = value.replace(/(\d{5})(\d)/, '$1-$2');
                        e.target.value = value;
                    }
                });
            }

            // Formatar Telefone
            const telefone = document.getElementById('telefone');
            if (telefone) {
                telefone.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        if (value.length <= 10) {
                            value = value.replace(/(\d{2})(\d)/, '($1) $2')
                                        .replace(/(\d{4})(\d)/, '$1-$2');
                        } else {
                            value = value.replace(/(\d{2})(\d)/, '($1) $2')
                                        .replace(/(\d{5})(\d)/, '$1-$2');
                        }
                        e.target.value = value;
                    }
                });
            }
        });

        // Validação do formulário
        document.getElementById('enderecoForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--vermelho)';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    </script>
</body>
</html>
[file content end]