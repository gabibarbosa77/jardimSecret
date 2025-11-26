<?php
session_start();
require 'conexao/conecta.php';

// Verificar se está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Verificar se veio da etapa de endereço
if (!isset($_SESSION['checkout_endereco'])) {
    header("Location: checkout_endereco.php");
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

// Buscar cartões do usuário
$cartoesQuery = $conn->prepare("
    SELECT * FROM tb_cartao 
    WHERE idUsuario = ? 
    ORDER BY principal DESC, data_cadastro DESC
");
$cartoesQuery->bind_param("i", $idUsuario);
$cartoesQuery->execute();
$cartoesUsuario = $cartoesQuery->get_result();

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

// Variáveis para cupom
$cupom_aplicado = false;
$desconto_cupom = 0;
$cupom_codigo = '';
$cupom_info = null;

// Verificar se já tem cupom aplicado na sessão
if (isset($_SESSION['cupom_aplicado'])) {
    $cupom_aplicado = true;
    $cupom_info = $_SESSION['cupom_aplicado'];
    $desconto_cupom = $cupom_info['valor_desconto'];
    $cupom_codigo = $cupom_info['codigo'];
}

// Processar aplicação de cupom
if (isset($_POST['aplicar_cupom'])) {
    $codigo_cupom = trim($_POST['codigo_cupom']);
    
    if (!empty($codigo_cupom)) {
        // Buscar cupom no banco
        $cupomQuery = $conn->prepare("
            SELECT * FROM tc_cupons 
            WHERE codigo = ? 
            AND status = 'ativo'
            AND data_inicio <= CURDATE() 
            AND data_fim >= CURDATE()
            AND (usos_maximos = 0 OR usos_atuais < usos_maximos)
        ");
        $cupomQuery->bind_param("s", $codigo_cupom);
        $cupomQuery->execute();
        $cupomResult = $cupomQuery->get_result();
        
        if ($cupomResult->num_rows > 0) {
            $cupom = $cupomResult->fetch_assoc();
            
            // Verificar valor mínimo
            if ($subtotal >= $cupom['valor_minimo']) {
                $cupom_aplicado = true;
                $cupom_info = $cupom;
                $desconto_cupom = $cupom['valor_desconto'];
                $cupom_codigo = $cupom['codigo'];
                
                // Salvar na sessão
                $_SESSION['cupom_aplicado'] = [
                    'id' => $cupom['id'],
                    'codigo' => $cupom['codigo'],
                    'valor_desconto' => $cupom['valor_desconto'],
                    'tipo_desconto' => $cupom['tipo_desconto']
                ];
                
                $mensagemCupom = "Cupom aplicado com sucesso! Desconto de R$ " . number_format($desconto_cupom, 2, ',', '.');
            } else {
                $erroCupom = "Valor mínimo para este cupom: R$ " . number_format($cupom['valor_minimo'], 2, ',', '.');
            }
        } else {
            $erroCupom = "Cupom inválido, expirado ou já utilizado.";
        }
    } else {
        $erroCupom = "Por favor, digite um código de cupom.";
    }
}

// Processar remoção de cupom
if (isset($_POST['remover_cupom'])) {
    unset($_SESSION['cupom_aplicado']);
    $cupom_aplicado = false;
    $desconto_cupom = 0;
    $cupom_codigo = '';
    $cupom_info = null;
    $mensagemCupom = "Cupom removido com sucesso.";
}

// Cálculo do frete e totais
$frete = $subtotal > 100 ? 0 : 15.00;
$total_sem_desconto = $subtotal + $frete;
$total = $total_sem_desconto - $desconto_cupom;

// Garantir que o total não seja negativo
if ($total < 0) {
    $total = 0;
}

// Processar cadastro de novo cartão
if (isset($_POST['salvar_cartao'])) {
    $numero_cartao = str_replace(' ', '', $_POST['numero_cartao']);
    $nome_titular = $_POST['nome_titular'];
    $validade = explode('/', $_POST['validade_cartao']);
    $mes_validade = $validade[0];
    $ano_validade = $validade[1];
    $cvv = $_POST['cvv_cartao'];
    $apelido = $_POST['apelido_cartao'];
    $principal = isset($_POST['principal_cartao']) ? 'sim' : 'nao';
    
    // Detectar bandeira do cartão
    $bandeira = detectarBandeira($numero_cartao);
    
    // Se marcar como principal, desmarca outros
    if ($principal == 'sim') {
        $updatePrincipal = $conn->prepare("UPDATE tb_cartao SET principal = 'nao' WHERE idUsuario = ?");
        $updatePrincipal->bind_param("i", $idUsuario);
        $updatePrincipal->execute();
    }
    
    // Inserir novo cartão
    $insertCartao = $conn->prepare("
        INSERT INTO tb_cartao (idUsuario, numero_cartao, nome_titular, mes_validade, ano_validade, cvv, bandeira, apelido, principal) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insertCartao->bind_param("issssssss", $idUsuario, $numero_cartao, $nome_titular, $mes_validade, $ano_validade, $cvv, $bandeira, $apelido, $principal);
    
    if ($insertCartao->execute()) {
        $mensagemCartao = "Cartão salvo com sucesso!";
        // Recarregar cartões
        $cartoesQuery->execute();
        $cartoesUsuario = $cartoesQuery->get_result();
    } else {
        $erroCartao = "Erro ao salvar cartão: " . $insertCartao->error;
    }
}

// Processar seleção de método de pagamento
if (isset($_POST['continuar_confirmacao'])) {
    $metodo_pagamento = $_POST['metodo_pagamento'];
    
    // Validar método de pagamento
    $metodosValidos = ['cartao', 'pix', 'boleto'];
    if (in_array($metodo_pagamento, $metodosValidos)) {
        
        // PROCESSAR PEDIDO - ADICIONAR ESTA PARTE
        try {
            $conn->begin_transaction();
            
            // 1. Calcular totais (recalcular para garantir precisão)
            $cartQuery = $conn->prepare("
                SELECT c.quantProduto, 
                       COALESCE(pr.valorPromocional, p.valorProduto) as precoFinal
                FROM tb_carrinho c
                INNER JOIN tb_produto p ON c.idProduto = p.idProduto
                LEFT JOIN tb_promocao pr ON p.idProduto = pr.idProduto
                WHERE c.idUsuario = ?
            ");
            $cartQuery->bind_param("i", $idUsuario);
            $cartQuery->execute();
            $cartResult = $cartQuery->get_result();
            
            $subtotal_calc = 0;
            while ($item = $cartResult->fetch_assoc()) {
                $subtotal_calc += $item['precoFinal'] * $item['quantProduto'];
            }
            
            $frete_calc = $subtotal_calc > 100 ? 0 : 15.00;
            $total_calc = $subtotal_calc + $frete_calc - $desconto_cupom;
            
            // 2. Criar pedido
            $insertCompra = $conn->prepare("
                INSERT INTO tb_compra (modoPagamento, dataCompra, idUsuario, valorTotal, valorFrete, valorDesconto, idStatus, idCupom, valorDescontoCupom) 
                VALUES (?, NOW(), ?, ?, ?, ?, 1, ?, ?)
            ");
            
            $idCupom = $cupom_aplicado ? $cupom_info['id'] : null;
            $insertCompra->bind_param("sidddii", $metodo_pagamento, $idUsuario, $total_calc, $frete_calc, $desconto_cupom, $idCupom, $desconto_cupom);
            $insertCompra->execute();
            $pedido_id = $conn->insert_id;
            
            // 3. Atualizar uso do cupom se aplicado
            if ($cupom_aplicado) {
                $updateCupom = $conn->prepare("
                    UPDATE tc_cupons 
                    SET usos_atuais = usos_atuais + 1 
                    WHERE id = ?
                ");
                $updateCupom->bind_param("i", $cupom_info['id']);
                $updateCupom->execute();
            }
            
            // 4. Salvar itens do pedido
            $itensQuery = $conn->prepare("
                SELECT c.idProduto, c.quantProduto
                FROM tb_carrinho c
                WHERE c.idUsuario = ?
            ");
            $itensQuery->bind_param("i", $idUsuario);
            $itensQuery->execute();
            $itensResult = $itensQuery->get_result();
            
            $stmtItens = $conn->prepare("
                INSERT INTO tb_itemcompra (idCompra, idProduto, quantProduto) 
                VALUES (?, ?, ?)
            ");
            
            while ($item = $itensResult->fetch_assoc()) {
                $stmtItens->bind_param("iii", $pedido_id, $item['idProduto'], $item['quantProduto']);
                $stmtItens->execute();
            }
            
            // 5. Limpar carrinho
            $deleteCart = $conn->prepare("DELETE FROM tb_carrinho WHERE idUsuario = ?");
            $deleteCart->bind_param("i", $idUsuario);
            $deleteCart->execute();
            
            // 6. Confirmar transação
            $conn->commit();
            
            // Limpar cupom da sessão
            unset($_SESSION['cupom_aplicado']);
            
            // Salvar ID do pedido na sessão
            $_SESSION['pedido_id'] = $pedido_id;
            
        } catch (Exception $e) {
            $conn->rollback();
            $erroPagamento = "Erro ao processar pedido: " . $e->getMessage();
        }
        
        // Salvar método de pagamento na sessão
        $_SESSION['checkout_pagamento'] = [
            'metodo_pagamento' => $metodo_pagamento,
            'cartao_selecionado' => $_POST['cartao_selecionado'] ?? null,
            'parcelas' => $_POST['parcelas'] ?? 1
        ];
        
        // Redirecionar para confirmação
        header("Location: checkout_confirmacao.php");
        exit();
    } else {
        $erroPagamento = "Método de pagamento inválido.";
    }
}

// Função para detectar bandeira do cartão
function detectarBandeira($numero) {
    $numero = preg_replace('/\D/', '', $numero);
    
    if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $numero)) {
        return 'Visa';
    } elseif (preg_match('/^5[1-5][0-9]{14}$/', $numero)) {
        return 'Mastercard';
    } elseif (preg_match('/^3[47][0-9]{13}$/', $numero)) {
        return 'American Express';
    } elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $numero)) {
        return 'Diners Club';
    } elseif (preg_match('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $numero)) {
        return 'Discover';
    } elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/', $numero)) {
        return 'JCB';
    } else {
        return 'Outra';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento | Jardim Secret</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="csscheckout.css">
    <style>
        .cupom-section {
            background: var(--branco);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cupom-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .cupom-form input {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--rosa-claro);
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .cupom-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cupom-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cupom-info {
            background: var(--rosa-claro);
            padding: 15px;
            border-radius: 6px;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .cupom-remove {
            background: var(--vermelho);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .cupom-remove:hover {
            background: #c82333;
        }
        
        .btn-cupom {
            background: var(--verde);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-cupom:hover {
            background: #218838;
        }
        
        .summary-line.cupom {
            color: var(--verde);
            font-weight: 600;
        }
        
        .summary-line.cupom span:last-child {
            color: var(--verde);
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <main>
        <div class="page-header">
            <h1 class="page-title">Finalizar Pedido</h1>
            <p class="page-subtitle">Escolha a forma de pagamento</p>
        </div>

        <div class="checkout-steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-label">Endereço</div>
            </div>
            <div class="step active">
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
                <!-- Seção de Cupom -->
                <div class="cupom-section">
                    <h3 style="color: var(--destaque); margin-bottom: 15px;">
                        <i class="fas fa-tag"></i> Cupom de Desconto
                    </h3>
                    
                    <?php if (isset($mensagemCupom)): ?>
                        <div class="cupom-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $mensagemCupom; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($erroCupom)): ?>
                        <div class="cupom-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo $erroCupom; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($cupom_aplicado): ?>
                        <div class="cupom-info">
                            <div>
                                <strong>Cupom aplicado: <?php echo $cupom_codigo; ?></strong>
                                <br>
                                <small>Desconto: R$ <?php echo number_format($desconto_cupom, 2, ',', '.'); ?></small>
                            </div>
                            <form method="POST" style="margin: 0;">
                                <button type="submit" name="remover_cupom" class="cupom-remove">
                                    <i class="fas fa-times"></i> Remover
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="cupom-form">
                            <input type="text" name="codigo_cupom" placeholder="Digite o código do cupom" required>
                            <button type="submit" name="aplicar_cupom" class="btn-cupom">
                                <i class="fas fa-tag"></i> Aplicar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Seção de Pagamento -->
                <div class="checkout-section">
                    <h2 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Método de Pagamento
                    </h2>
                    
                    <?php if (isset($erroPagamento)): ?>
                        <div class="cupom-error" style="margin-bottom: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo $erroPagamento; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="pagamentoForm">
                        <div class="payment-methods">
                            <div class="payment-method selected" data-method="cartao">
                                <div class="payment-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div>Cartão de Crédito</div>
                            </div>
                            <div class="payment-method" data-method="pix">
                                <div class="payment-icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div>PIX</div>
                            </div>
                            <div class="payment-method" data-method="boleto">
                                <div class="payment-icon">
                                    <i class="fas fa-barcode"></i>
                                </div>
                                <div>Boleto</div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="metodo_pagamento" id="metodoPagamentoInput" value="cartao">
                        
                        <div id="paymentDetails" style="margin-top: 20px;">
                            <!-- Detalhes do pagamento serão carregados aqui -->
                        </div>
                        
                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" name="continuar_confirmacao" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-arrow-right"></i>
                                Continuar para Confirmação
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
                
                <?php if ($cupom_aplicado): ?>
                <div class="summary-line cupom">
                    <span>Desconto (Cupom <?php echo $cupom_codigo; ?>)</span>
                    <span>- R$ <?php echo number_format($desconto_cupom, 2, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="summary-line total">
                    <span>Total</span>
                    <span>R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                </div>
                
                <?php if ($cupom_aplicado): ?>
                <div style="background: var(--rosa-claro); padding: 10px; border-radius: 6px; margin-top: 10px; text-align: center;">
                    <small style="color: var(--verde);">
                        <i class="fas fa-gift"></i> 
                        Você economizou R$ <?php echo number_format($desconto_cupom, 2, ',', '.'); ?> com o cupom!
                    </small>
                </div>
                <?php endif; ?>
                
                <div class="security-info">
                    <i class="fas fa-lock"></i>
                    <strong>Compra 100% segura</strong> - Seus dados estão protegidos
                </div>
                
                <div class="save-for-later">
                    <a href="checkout_endereco.php">
                        <i class="fas fa-arrow-left"></i>
                        Voltar para Endereço
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        let metodoPagamentoSelecionado = 'cartao';

        // Seleção de método de pagamento
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                metodoPagamentoSelecionado = this.dataset.method;
                document.getElementById('metodoPagamentoInput').value = metodoPagamentoSelecionado;
                carregarDetalhesPagamento(this.dataset.method);
            });
        });

        function carregarDetalhesPagamento(metodo) {
            const container = document.getElementById('paymentDetails');
            
            switch(metodo) {
                case 'cartao':
                    container.innerHTML = `
                        <?php if ($cartoesUsuario->num_rows > 0): ?>
                            <div class="cartoes-salvos">
                                <h4 style="color: var(--destaque); margin-bottom: 15px;">
                                    <i class="fas fa-credit-card"></i> Cartões Salvos
                                </h4>
                                <?php 
                                // Reset pointer para poder usar fetch_assoc novamente
                                $cartoesUsuario->data_seek(0);
                                while ($cartao = $cartoesUsuario->fetch_assoc()): ?>
                                    <div class="cartao-item" onclick="selecionarCartao(<?php echo $cartao['id']; ?>)">
                                        <div class="cartao-info">
                                            <div class="cartao-bandeira">
                                                <i class="fab fa-cc-<?php echo strtolower($cartao['bandeira']); ?>"></i>
                                            </div>
                                            <div>
                                                <div class="cartao-numero">**** **** **** <?php echo substr($cartao['numero_cartao'], -4); ?></div>
                                                <div style="font-size: 0.8rem; color: var(--cinza-claro);">
                                                    <?php echo $cartao['nome_titular']; ?> - 
                                                    <?php echo $cartao['mes_validade']; ?>/<?php echo $cartao['ano_validade']; ?>
                                                    <?php if ($cartao['apelido']): ?>
                                                        - <?php echo $cartao['apelido']; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($cartao['principal'] == 'sim'): ?>
                                            <div class="cartao-principal">Principal</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- FORMULÁRIO INDEPENDENTE PARA CARTÃO -->
                        <div class="novo-cartao-form">
                            <h4 style="color: var(--destaque); margin-bottom: 15px;">
                                <i class="fas fa-plus-circle"></i> 
                                <?php echo $cartoesUsuario->num_rows > 0 ? 'Adicionar Novo Cartão' : 'Cadastrar Cartão'; ?>
                            </h4>
                            
                            <?php if (isset($mensagemCartao)): ?>
                                <div class="cartao-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $mensagemCartao; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($erroCartao)): ?>
                                <div class="cartao-error">
                                    <i class="fas fa-times-circle"></i>
                                    <?php echo $erroCartao; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- FORMULÁRIO SEPARADO PARA CARTÃO -->
                            <form method="POST" id="cartaoForm">
                                <div class="form-group">
                                    <label for="numero_cartao">Número do Cartão</label>
                                    <input type="text" id="numero_cartao" name="numero_cartao" 
                                           placeholder="0000 0000 0000 0000" maxlength="19" required
                                           oninput="formatarCartao(this)">
                                </div>
                                
                                <div class="form-group">
                                    <label for="nome_titular">Nome do Titular</label>
                                    <input type="text" id="nome_titular" name="nome_titular" 
                                           placeholder="Como está no cartão" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="validade_cartao">Validade</label>
                                        <input type="text" id="validade_cartao" name="validade_cartao" 
                                               placeholder="MM/AA" maxlength="5" required
                                               oninput="formatarValidade(this)">
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv_cartao">CVV</label>
                                        <input type="text" id="cvv_cartao" name="cvv_cartao" 
                                               placeholder="000" maxlength="4" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="apelido_cartao">Apelido do Cartão (opcional)</label>
                                    <input type="text" id="apelido_cartao" name="apelido_cartao" 
                                           placeholder="Ex: Cartão Principal, Nubank, etc.">
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="principal_cartao" name="principal_cartao">
                                    <label for="principal_cartao">Definir como cartão principal</label>
                                </div>
                                
                                <button type="submit" name="salvar_cartao" class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Salvar Cartão
                                </button>
                            </form>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <div class="form-group">
                                <label for="parcelas">Parcelas</label>
                                <select id="parcelas" name="parcelas">
                                    <option value="1">1x de R$ <?php echo number_format($total, 2, ',', '.'); ?></option>
                                    <option value="2">2x de R$ <?php echo number_format($total/2, 2, ',', '.'); ?></option>
                                    <option value="3">3x de R$ <?php echo number_format($total/3, 2, ',', '.'); ?></option>
                                    <option value="4">4x de R$ <?php echo number_format($total/4, 2, ',', '.'); ?></option>
                                    <option value="5">5x de R$ <?php echo number_format($total/5, 2, ',', '.'); ?></option>
                                    <option value="6">6x de R$ <?php echo number_format($total/6, 2, ',', '.'); ?></option>
                                </select>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'pix':
                    container.innerHTML = `
                        <div style="text-align: center; padding: 20px; background: var(--rosa-claro); border-radius: 8px;">
                            <i class="fas fa-qrcode" style="font-size: 3rem; color: var(--rosa-primario); margin-bottom: 15px;"></i>
                            <p><strong>PIX Copia e Cola</strong></p>
                            <p style="font-size: 0.9rem; color: var(--cinza-claro); margin-bottom: 15px;">
                                O QR Code será gerado após a confirmação do pedido
                            </p>
                        </div>
                    `;
                    break;
                    
                case 'boleto':
                    container.innerHTML = `
                        <div style="text-align: center; padding: 20px; background: var(--rosa-claro); border-radius: 8px;">
                            <i class="fas fa-barcode" style="font-size: 3rem; color: var(--rosa-primario); margin-bottom: 15px;"></i>
                            <p><strong>Boleto Bancário</strong></p>
                            <p style="font-size: 0.9rem; color: var(--cinza-claro); margin-bottom: 15px;">
                                O boleto será gerado após a confirmação do pedido e enviado para seu e-mail
                            </p>
                            <p style="font-size: 0.8rem; color: var(--cinza-claro);">
                                Vencimento: <?php echo date('d/m/Y', strtotime('+3 days')); ?>
                            </p>
                        </div>
                    `;
                    break;
            }
        }

        // Função para selecionar cartão
        function selecionarCartao(idCartao) {
            document.querySelectorAll('.cartao-item').forEach(item => {
                item.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Adicionar input hidden para o cartão selecionado
            let existingInput = document.querySelector('input[name="cartao_selecionado"]');
            if (existingInput) {
                existingInput.remove();
            }
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cartao_selecionado';
            input.value = idCartao;
            document.getElementById('pagamentoForm').appendChild(input);
        }

        // Funções de formatação
        function formatarCartao(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            input.value = value.substring(0, 19);
        }

        function formatarValidade(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            input.value = value;
        }

        // Carregar detalhes do cartão por padrão
        document.addEventListener('DOMContentLoaded', function() {
            carregarDetalhesPagamento('cartao');
        });

        // Validação do formulário principal
        document.getElementById('pagamentoForm').addEventListener('submit', function(e) {
            if (metodoPagamentoSelecionado === 'cartao') {
                const cartaoSelecionado = document.querySelector('input[name="cartao_selecionado"]');
                const temCartoesSalvos = <?php echo $cartoesUsuario->num_rows > 0 ? 'true' : 'false'; ?>;
                
                if (temCartoesSalvos && (!cartaoSelecionado || !cartaoSelecionado.value)) {
                    e.preventDefault();
                    alert('Por favor, selecione um cartão para pagamento.');
                    return;
                }
            }
        });
    </script>
</body>
</html>