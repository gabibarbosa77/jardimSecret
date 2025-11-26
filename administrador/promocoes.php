<?php
include '../conexao/conecta.php';

// Processar remoção de promoção
if (isset($_POST['remover_promocao'])) {
    $idPromocao = $_POST['idPromocao'];
    $sql = "DELETE FROM tb_promocao WHERE idPromocao = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $idPromocao);
        
        if ($stmt->execute()) {
            $mensagem = "Promoção removida com sucesso!";
        } else {
            $erro = "Erro ao remover promoção: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $erro = "Erro na preparação da query: " . $conn->error;
    }
}

// Processar adição de promoção
if (isset($_POST['adicionar_promocao'])) {
    $idProduto = $_POST['idProduto'];
    $valorPromocional = $_POST['valorPromocional'];
    
    // Buscar dados do produto para calcular o percentual
    $sql_produto = "SELECT valorProduto FROM tb_produto WHERE idProduto = ? AND ativo = 1";
    $stmt_produto = $conn->prepare($sql_produto);
    
    if ($stmt_produto) {
        $stmt_produto->bind_param("i", $idProduto);
        $stmt_produto->execute();
        $result_produto = $stmt_produto->get_result();
        
        if ($result_produto->num_rows > 0) {
            $produto = $result_produto->fetch_assoc();
            $valorOriginal = floatval($produto['valorProduto']);
            $percentualPromocao = (($valorOriginal - $valorPromocional) / $valorOriginal) * 100;
            
            // Inserir na tabela de promoções - APENAS COLUNAS EXISTENTES
            $sql_insert = "INSERT INTO tb_promocao (idProduto, valorPromocional, percentualPromocao) 
                          VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            
            if ($stmt_insert) {
                $stmt_insert->bind_param("idd", $idProduto, $valorPromocional, $percentualPromocao);
                
                if ($stmt_insert->execute()) {
                    $mensagem = "Produto colocado em promoção com sucesso!";
                } else {
                    $erro = "Erro ao adicionar promoção: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            } else {
                $erro = "Erro na preparação da query de inserção: " . $conn->error;
            }
        } else {
            $erro = "Produto não encontrado ou inativo!";
        }
        $stmt_produto->close();
    } else {
        $erro = "Erro na preparação da query do produto: " . $conn->error;
    }
}

// Buscar produtos em promoção com JOIN para obter dados do produto
$sql_promocoes = "SELECT p.*, pr.nomeProduto, pr.valorProduto as valorOriginal, t.tipo as categoriaProduto 
                 FROM tb_promocao p 
                 INNER JOIN tb_produto pr ON p.idProduto = pr.idProduto 
                 LEFT JOIN tb_tipoproduto t ON pr.tipoProduto = t.idTipoProduto 
                 ORDER BY p.idPromocao DESC";
$result_promocoes = $conn->query($sql_promocoes);

// Buscar produtos disponíveis para promoção
$sql_produtos = "SELECT p.idProduto, p.nomeProduto, p.valorProduto, p.tipoProduto 
                FROM tb_produto p 
                WHERE p.ativo = 1 AND p.idProduto NOT IN (SELECT idProduto FROM tb_promocao)";
$result_produtos = $conn->query($sql_produtos);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Promoções | Jardim Secret</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a8d73;
            --secondary-color: #f4f4f4;
            --accent-color: #ff7e5f;
            --dark-color: #2c3e50;
            --light-color: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: var(--dark-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* --- Estilos do header --- */
        header {
            background: linear-gradient(135deg, var(--primary-color), #4a6b5b);
            color: white;
            padding: 10px 20px;
            box-shadow: var(--shadow);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo img {
            height: 50px;
        }

        .site-title {
            margin: 0;
            font-size: 1.5rem;
            flex-grow: 1;
            text-align: center;
        }

        .user-menu {
            position: relative;
        }

        .user-toggle {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 8px 12px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .user-toggle:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--light-color);
            min-width: 200px;
            box-shadow: var(--shadow);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
        }

        .user-dropdown.show {
            display: block;
        }

        .user-dropdown a {
            color: var(--dark-color);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: var(--transition);
        }

        .user-dropdown a:hover {
            background-color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .site-title {
                display: none;
            }
        }
        /* --- Fim dos estilos do header --- */
        
        .container {
            max-width: 1200px;
            margin-top: 20px;
        }

        .card {
            box-shadow: var(--shadow);
            border: none;
        }

        /* Estilos específicos para promoções */
        .card-promocao {
            border-left: 4px solid #dc3545;
            margin-bottom: 15px;
            transition: var(--transition);
        }
        
        .card-promocao:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .percentual-badge {
            font-size: 0.9em;
        }
        
        .promo-header {
            background: linear-gradient(135deg, var(--primary-color), #4a6b5b);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .desconto-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .btn-desconto {
            flex: 1;
            min-width: 60px;
            font-size: 0.85rem;
        }
        </style>
</head>
<body>
    
<?php include 'menu.php' ?>

    <?php if (isset($mensagem)): ?>
    <div class="container mt-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($erro)): ?>
    <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mt-4">
        <div class="promo-header">
            <h2><i class="fas fa-tag me-2"></i>Gerenciar Promoções</h2>
            <p class="mb-0">Adicione ou remova promoções dos produtos</p>
        </div>

        <!-- Lista de Produtos em Promoção -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4><i class="fas fa-fire text-danger me-2"></i>Produtos em Promoção</h4>
                    <span class="badge bg-danger"><?php echo $result_promocoes ? $result_promocoes->num_rows : 0; ?> produto(s)</span>
                </div>
                
                <?php if ($result_promocoes && $result_promocoes->num_rows > 0): ?>
                    <?php while ($promocao = $result_promocoes->fetch_assoc()): ?>
                        <div class="card card-promocao">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 class="card-title"><?php echo htmlspecialchars($promocao['nomeProduto'] ?? ''); ?></h5>
                                        <p class="card-text mb-1">
                                            <strong><i class="fas fa-tags me-1"></i>Categoria:</strong> <?php echo htmlspecialchars($promocao['categoriaProduto'] ?? 'Sem categoria'); ?>
                                        </p>
                                        <p class="card-text mb-1">
                                            <strong>De:</strong> <span class="text-decoration-line-through text-muted">R$ <?php echo isset($promocao['valorOriginal']) ? number_format($promocao['valorOriginal'], 2, ',', '.') : '0,00'; ?></span>
                                            <strong>Por:</strong> <span class="text-success fw-bold">R$ <?php echo isset($promocao['valorPromocional']) ? number_format($promocao['valorPromocional'], 2, ',', '.') : '0,00'; ?></span>
                                        </p>
                                        <span class="badge bg-danger percentual-badge">
                                            <i class="fas fa-arrow-down me-1"></i><?php echo isset($promocao['percentualPromocao']) ? number_format($promocao['percentualPromocao'], 1, ',', '.') : '0,0'; ?>% OFF
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle me-1"></i>Economia: R$ <?php 
                                            if (isset($promocao['valorOriginal']) && isset($promocao['valorPromocional'])) {
                                                echo number_format($promocao['valorOriginal'] - $promocao['valorPromocional'], 2, ',', '.');
                                            } else {
                                                echo '0,00';
                                            }
                                            ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="idPromocao" value="<?php echo $promocao['idPromocao']; ?>">
                                            <button type="submit" name="remover_promocao" class="btn btn-outline-danger btn-sm" onclick="return confirm('Tem certeza que deseja remover esta promoção?')">
                                                <i class="fas fa-times me-1"></i>Tirar Promoção
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h5>Nenhum produto em promoção no momento.</h5>
                        <p class="mb-0">Adicione promoções usando o formulário ao lado.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulário para adicionar promoção -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Adicionar Promoção</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result_produtos && $result_produtos->num_rows > 0): ?>
                            <form method="POST" id="formPromocao">
                                <div class="mb-3">
                                    <label for="idProduto" class="form-label"><i class="fas fa-box me-1"></i>Selecionar Produto:</label>
                                    <select class="form-select" id="idProduto" name="idProduto" required>
                                        <option value="">Selecione um produto</option>
                                        <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                                            <option value="<?php echo $produto['idProduto']; ?>" 
                                                    data-valor="<?php echo $produto['valorProduto']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($produto['nomeProduto']); ?>"
                                                    data-tipo="<?php echo $produto['tipoProduto']; ?>">
                                                <?php echo htmlspecialchars($produto['nomeProduto']); ?> - R$ <?php echo number_format($produto['valorProduto'], 2, ',', '.'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-percentage me-1"></i>Selecionar Desconto:</label>
                                    <div class="desconto-buttons" id="descontoButtons">
                                        <!-- Botões de desconto serão gerados via JavaScript -->
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="valorPromocional" class="form-label"><i class="fas fa-dollar-sign me-1"></i>Valor Promocional:</label>
                                    <input type="number" class="form-control" id="valorPromocional" name="valorPromocional" 
                                           step="0.01" min="0.01" required placeholder="0,00">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-info-circle me-1"></i>Informações do Produto:</label>
                                    <div id="infoProduto" class="text-muted small p-2 bg-light rounded">
                                        <i class="fas fa-mouse-pointer me-1"></i>Selecione um produto para ver as informações
                                    </div>
                                </div>

                                <div class="mb-3" id="calculosPromocao" style="display: none;">
                                    <label class="form-label"><i class="fas fa-calculator me-1"></i>Resumo da Promoção:</label>
                                    <div class="p-2 bg-light rounded small">
                                        <div id="detalhesCalculo"></div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="adicionar_promocao" class="btn btn-success w-100">
                                    <i class="fas fa-tag me-1"></i>Colocar em Promoção
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <h6>Todos os produtos já estão em promoção!</h6>
                                <p class="small mb-0">Remova algumas promoções para adicionar novas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gerar botões de desconto de 5% a 95%
        function gerarBotoesDesconto() {
            const container = document.getElementById('descontoButtons');
            container.innerHTML = '';
            
            for (let i = 5; i <= 95; i += 5) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-primary btn-sm btn-desconto';
                button.textContent = i + '%';
                button.dataset.desconto = i;
                button.addEventListener('click', function() {
                    aplicarDesconto(this.dataset.desconto);
                    // Destacar botão selecionado
                    document.querySelectorAll('.btn-desconto').forEach(btn => {
                        btn.classList.remove('btn-primary', 'active');
                        btn.classList.add('btn-outline-primary');
                    });
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary', 'active');
                });
                container.appendChild(button);
            }
        }

        // Aplicar desconto selecionado
        function aplicarDesconto(percentual) {
            const select = document.getElementById('idProduto');
            const selectedOption = select.options[select.selectedIndex];
            const valorOriginal = parseFloat(selectedOption.getAttribute('data-valor'));
            
            if (valorOriginal && percentual) {
                const valorComDesconto = valorOriginal * (1 - percentual / 100);
                document.getElementById('valorPromocional').value = valorComDesconto.toFixed(2);
                atualizarCalculos();
            }
        }

        document.getElementById('idProduto').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var valorOriginal = selectedOption.getAttribute('data-valor');
            var nomeProduto = selectedOption.getAttribute('data-nome');
            var categoria = selectedOption.getAttribute('data-categoria');
            
            if (valorOriginal) {
                var infoDiv = document.getElementById('infoProduto');
                infoDiv.innerHTML = `
                    <strong>Produto:</strong> ${nomeProduto}<br>
                    <strong>Categoria:</strong> ${categoria}<br>
                    <strong>Valor Original:</strong> R$ ${parseFloat(valorOriginal).toFixed(2).replace('.', ',')}
                `;
                
                // Limpar seleção de botões de desconto
                document.querySelectorAll('.btn-desconto').forEach(btn => {
                    btn.classList.remove('btn-primary', 'active');
                    btn.classList.add('btn-outline-primary');
                });
                
                // Aplicar desconto padrão de 10%
                aplicarDesconto(10);
                
                // Mostrar cálculos
                document.getElementById('calculosPromocao').style.display = 'block';
                atualizarCalculos();
            } else {
                document.getElementById('infoProduto').innerHTML = '<i class="fas fa-mouse-pointer me-1"></i>Selecione um produto para ver as informações';
                document.getElementById('calculosPromocao').style.display = 'none';
            }
        });

        document.getElementById('valorPromocional').addEventListener('input', atualizarCalculos);

        function atualizarCalculos() {
            var valorOriginal = parseFloat(document.getElementById('idProduto').options[document.getElementById('idProduto').selectedIndex].getAttribute('data-valor'));
            var valorPromocional = parseFloat(document.getElementById('valorPromocional').value);
            
            if (valorPromocional && valorOriginal) {
                var desconto = valorOriginal - valorPromocional;
                var percentual = (desconto / valorOriginal) * 100;
                
                document.getElementById('detalhesCalculo').innerHTML = `
                    <strong>Desconto:</strong> R$ ${desconto.toFixed(2).replace('.', ',')}<br>
                    <strong>Percentual:</strong> ${percentual.toFixed(1).replace('.', ',')}% OFF<br>
                    <strong>Economia:</strong> ${percentual.toFixed(1).replace('.', ',')}%
                `;
                
                // Atualizar botão de desconto ativo baseado no percentual calculado
                atualizarBotaoDescontoAtivo(percentual);
            }
        }

        function atualizarBotaoDescontoAtivo(percentualAtual) {
            const botoes = document.querySelectorAll('.btn-desconto');
            let botaoMaisProximo = null;
            let diferencaMaisProxima = Infinity;
            
            botoes.forEach(botao => {
                const percentualBotao = parseInt(botao.dataset.desconto);
                const diferenca = Math.abs(percentualBotao - percentualAtual);
                
                if (diferenca < diferencaMaisProxima) {
                    diferencaMaisProxima = diferenca;
                    botaoMaisProximo = botao;
                }
                
                // Remover classe ativa de todos os botões
                botao.classList.remove('btn-primary', 'active');
                botao.classList.add('btn-outline-primary');
            });
            
            // Adicionar classe ativa ao botão mais próximo
            if (botaoMaisProximo && diferencaMaisProxima <= 2.5) { // Margem de 2.5%
                botaoMaisProximo.classList.remove('btn-outline-primary');
                botaoMaisProximo.classList.add('btn-primary', 'active');
            }
        }

        // Validação do formulário
        document.getElementById('formPromocao').addEventListener('submit', function(e) {
            var valorOriginal = parseFloat(document.getElementById('idProduto').options[document.getElementById('idProduto').selectedIndex].getAttribute('data-valor'));
            var valorPromocional = parseFloat(document.getElementById('valorPromocional').value);
            
            if (valorPromocional >= valorOriginal) {
                e.preventDefault();
                alert('O valor promocional deve ser menor que o valor original!');
                return false;
            }
            
            if (valorPromocional <= 0) {
                e.preventDefault();
                alert('O valor promocional deve ser maior que zero!');
                return false;
            }
        });

        // Script do menu
        document.getElementById('userToggle').addEventListener('click', function() {
            document.getElementById('userDropdown').classList.toggle('show');
        });

        window.addEventListener('click', function(event) {
            if (!event.target.matches('.user-toggle') && !event.target.closest('.user-toggle')) {
                var dropdowns = document.getElementsByClassName("user-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        });

        // Inicializar botões de desconto quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            gerarBotoesDesconto();
        });
    </script>
</body>
</html>
<?php
if ($conn) {
    $conn->close();
}