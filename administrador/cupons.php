<?php
include '../conexao/conecta.php';

// Código temporário para atualizar datas dos cupons (remover depois de usar)
if (isset($_GET['atualizar_datas'])) {
    $ano_atual = date('Y');
    $data_inicio_nova = $ano_atual . '-12-01';
    $data_fim_nova = $ano_atual . '-12-31';
    
    $sql_update = "UPDATE tc_cupons SET 
                    data_inicio = ?,
                    data_fim = ?
                   WHERE data_fim < CURDATE() OR status = 'inativo'";
    
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("ss", $data_inicio_nova, $data_fim_nova);
        
        if ($stmt_update->execute()) {
            $mensagem = "Datas dos cupons atualizadas com sucesso para " . $ano_atual . "!";
        } else {
            $erro = "Erro ao atualizar datas: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        $erro = "Erro na preparação da query: " . $conn->error;
    }
}

// Processar remoção de cupom
if (isset($_POST['remover_cupom'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM tc_cupons WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $mensagem = "Cupom removido com sucesso!";
        } else {
            $erro = "Erro ao remover cupom: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $erro = "Erro na preparação da query: " . $conn->error;
    }
}

// Processar adição de cupom
if (isset($_POST['salvar_cupom'])) {
    $codigo = $_POST['codigo'];
    $descricao = $_POST['descricao'];
    $tipo_desconto = $_POST['tipo_desconto'];
    $valor_desconto = $_POST['valor_desconto'];
    $valor_minimo = $_POST['valor_minimo'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $usos_maximos = $_POST['usos_maximos'];
    $status = $_POST['status'];

    // Verificar se código já existe
    $sql_verifica = "SELECT id FROM tc_cupons WHERE codigo = ?";
    $stmt_verifica = $conn->prepare($sql_verifica);
    
    if ($stmt_verifica) {
        $stmt_verifica->bind_param("s", $codigo);
        $stmt_verifica->execute();
        $result_verifica = $stmt_verifica->get_result();
        
        if ($result_verifica->num_rows > 0) {
            $erro = "Já existe um cupom com este código!";
            $stmt_verifica->close();
        } else {
            $stmt_verifica->close();
            
            // Inserir novo cupom
            $sql = "INSERT INTO tc_cupons (codigo, descricao, tipo_desconto, valor_desconto, valor_minimo, 
                    data_inicio, data_fim, usos_maximos, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("sssddssis", $codigo, $descricao, $tipo_desconto, $valor_desconto, 
                                $valor_minimo, $data_inicio, $data_fim, $usos_maximos, $status);
                
                if ($stmt->execute()) {
                    $mensagem = "Cupom criado com sucesso!";
                } else {
                    $erro = "Erro ao criar cupom: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $erro = "Erro na preparação da query: " . $conn->error;
            }
        }
    }
}

// Buscar todos os cupons
$sql_cupons = "SELECT * FROM tc_cupons ORDER BY status DESC, data_fim ASC, id DESC";
$result_cupons = $conn->query($sql_cupons);

// Verificar se existem cupons expirados para mostrar o alerta
$sql_expirados = "SELECT COUNT(*) as total FROM tc_cupons WHERE data_fim < CURDATE()";
$result_expirados = $conn->query($sql_expirados);
$cupons_expirados = $result_expirados ? $result_expirados->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cupons | Jardim Secret</title>
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
            max-width: 1400px;
            margin-top: 20px;
        }

        .card {
            box-shadow: var(--shadow);
            border: none;
        }

        .cupom-header {
            background: linear-gradient(135deg, #8e44ad, #9b59b6);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .card-cupom {
            border-left: 4px solid #8e44ad;
            margin-bottom: 15px;
            transition: var(--transition);
        }
        
        .card-cupom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .cupom-ativo {
            border-left-color: #27ae60;
        }
        
        .cupom-inativo {
            border-left-color: #95a5a6;
            opacity: 0.8;
        }
        
        .cupom-expirado {
            border-left-color: #e74c3c;
            background-color: #fdf2f2;
        }
        
        .badge-cupom {
            font-size: 0.8em;
            font-family: 'Courier New', monospace;
        }
        
        .status-badge {
            font-size: 0.75em;
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

    <!-- Botão temporário para atualizar datas (remover depois) -->
    <?php if ($cupons_expirados > 0): ?>
    <div class="container mt-3">
        <div class="alert alert-warning">
            <p><strong>Atenção:</strong> <?php echo $cupons_expirados; ?> cupon(s) estão com datas expiradas.</p>
            <a href="?atualizar_datas=1" class="btn btn-warning btn-sm">
                <i class="fas fa-calendar-alt me-1"></i>Atualizar Datas dos Cupons para <?php echo date('Y'); ?>
            </a>
            <small class="d-block mt-2">As datas serão atualizadas para 01/12/<?php echo date('Y'); ?> à 31/12/<?php echo date('Y'); ?></small>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mt-4">
        <div class="cupom-header">
            <h2><i class="fas fa-sign-out-alt me-2"></i>Gerenciar Cupons de Desconto</h2>
            <p class="mb-0">Crie e gerencie cupons de desconto para seus clientes</p>
        </div>

        <div class="row mt-4">
            <!-- Lista de Cupons -->
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4><i class="fas fa-list me-2"></i>Cupons Cadastrados</h4>
                    <span class="badge bg-purple"><?php echo $result_cupons ? $result_cupons->num_rows : 0; ?> cupom(ns)</span>
                </div>
                
                <?php if ($result_cupons && $result_cupons->num_rows > 0): ?>
                    <?php while ($cupom = $result_cupons->fetch_assoc()): 
                        $hoje = date('Y-m-d');
                        $data_fim = $cupom['data_fim'];
                        $classe_status = '';
                        
                        if ($cupom['status'] == 'inativo') {
                            $classe_status = 'cupom-inativo';
                        } elseif ($data_fim < $hoje) {
                            $classe_status = 'cupom-expirado';
                        } else {
                            $classe_status = 'cupom-ativo';
                        }
                    ?>
                        <div class="card card-cupom <?php echo $classe_status; ?>">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title">
                                                <span class="badge bg-primary badge-cupom"><?php echo htmlspecialchars($cupom['codigo']); ?></span>
                                            </h5>
                                            <div>
                                                <?php if ($cupom['status'] == 'ativo' && $data_fim >= $hoje): ?>
                                                    <span class="badge bg-success status-badge">ATIVO</span>
                                                <?php elseif ($cupom['status'] == 'inativo'): ?>
                                                    <span class="badge bg-secondary status-badge">INATIVO</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger status-badge">EXPIRADO</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text mb-2"><?php echo htmlspecialchars($cupom['descricao']); ?></p>
                                        
                                        <div class="row small text-muted">
                                            <div class="col-6">
                                                <strong><i class="fas fa-tag me-1"></i>Desconto:</strong><br>
                                                R$ <?php echo number_format($cupom['valor_desconto'], 2, ',', '.'); ?>
                                            </div>
                                            <div class="col-6">
                                                <strong><i class="fas fa-shopping-cart me-1"></i>Mínimo:</strong><br>
                                                R$ <?php echo number_format($cupom['valor_minimo'], 2, ',', '.'); ?>
                                            </div>
                                            <div class="col-6">
                                                <strong><i class="fas fa-calendar me-1"></i>Validade:</strong><br>
                                                <?php echo date('d/m/Y', strtotime($cupom['data_inicio'])); ?> à <?php echo date('d/m/Y', strtotime($cupom['data_fim'])); ?>
                                            </div>
                                            <div class="col-6">
                                                <strong><i class="fas fa-chart-bar me-1"></i>Usos:</strong><br>
                                                <?php echo $cupom['usos_atuais']; ?>/<?php echo $cupom['usos_maximos'] == 0 ? '∞' : $cupom['usos_maximos']; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo $cupom['id']; ?>">
                                            <button type="submit" name="remover_cupom" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Tem certeza que deseja excluir este cupom?')">
                                                <i class="fas fa-trash me-1"></i>Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-sign-out-alt fa-2x mb-3"></i>
                        <h5>Nenhum cupom cadastrado.</h5>
                        <p class="mb-0">Crie seu primeiro cupom usando o formulário ao lado.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulário para adicionar cupom -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Novo Cupom
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="formCupom">
                            <div class="mb-3">
                                <label for="codigo" class="form-label"><i class="fas fa-code me-1"></i>Código do Cupom:</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" 
                                       required placeholder="Ex: VERÃO20" maxlength="50">
                                <div class="form-text">Código único que o cliente irá digitar</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descricao" class="form-label"><i class="fas fa-align-left me-1"></i>Descrição:</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="2" 
                                          placeholder="Descreva o propósito deste cupom"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_desconto" class="form-label"><i class="fas fa-percentage me-1"></i>Tipo:</label>
                                    <select class="form-select" id="tipo_desconto" name="tipo_desconto" required>
                                        <option value="valor_fixo" selected>Valor Fixo (R$)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="valor_desconto" class="form-label"><i class="fas fa-dollar-sign me-1"></i>Valor Desconto:</label>
                                    <input type="number" class="form-control" id="valor_desconto" name="valor_desconto" 
                                           step="0.01" min="0.01" required 
                                           placeholder="0,00">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="valor_minimo" class="form-label"><i class="fas fa-shopping-cart me-1"></i>Valor Mínimo da Compra:</label>
                                <input type="number" class="form-control" id="valor_minimo" name="valor_minimo" 
                                       step="0.01" min="0" 
                                       value="0" 
                                       placeholder="0,00">
                                <div class="form-text">Deixe 0 para não exigir valor mínimo</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="data_inicio" class="form-label"><i class="fas fa-calendar-plus me-1"></i>Data Início:</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                           value="<?php echo date('Y-m-d'); ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="data_fim" class="form-label"><i class="fas fa-calendar-minus me-1"></i>Data Fim:</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                           value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="usos_maximos" class="form-label"><i class="fas fa-users me-1"></i>Usos Máximos:</label>
                                    <input type="number" class="form-control" id="usos_maximos" name="usos_maximos" 
                                           min="0" 
                                           value="0">
                                    <div class="form-text">0 = ilimitado</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label"><i class="fas fa-power-off me-1"></i>Status:</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="ativo" selected>Ativo</option>
                                        <option value="inativo">Inativo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="salvar_cupom" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>Criar Cupom
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação das datas
        document.getElementById('formCupom').addEventListener('submit', function(e) {
            const dataInicio = new Date(document.getElementById('data_inicio').value);
            const dataFim = new Date(document.getElementById('data_fim').value);
            const codigo = document.getElementById('codigo').value.trim();
            
            if (dataFim < dataInicio) {
                e.preventDefault();
                alert('A data fim não pode ser anterior à data de início!');
                return false;
            }
            
            if (codigo === '') {
                e.preventDefault();
                alert('O código do cupom é obrigatório!');
                return false;
            }
        });

        // Validação em tempo real das datas
        document.getElementById('data_fim').addEventListener('change', function() {
            const dataInicio = new Date(document.getElementById('data_inicio').value);
            const dataFim = new Date(this.value);
            
            if (dataFim < dataInicio) {
                alert('A data fim não pode ser anterior à data de início!');
                this.value = document.getElementById('data_inicio').value;
            }
        });

        // Auto-gerar código sugerido
        document.getElementById('codigo').addEventListener('focus', function() {
            if (!this.value) {
                const sugestoes = ['VERAO20', 'PRIMEIRA', 'FRETEGRATIS', 'SUPER10', 'BLACKFRIDAY'];
                const sugestao = sugestoes[Math.floor(Math.random() * sugestoes.length)];
                this.placeholder = sugestao;
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

        // Adicionar estilo para badge purple
        const style = document.createElement('style');
        style.textContent = `
            .bg-purple { background-color: #8e44ad !important; }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
<?php
if ($conn) {
    $conn->close();
}