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
            max-width: 800px;
        }

        .profile-card {
            background-color: var(--branco);
            border-radius: 12px;
            padding: 40px;
            box-shadow: var(--sombra);
            margin-bottom: 30px;
        }

        .profile-title {
            font-size: 1.8rem;
            color: var(--destaque);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--rosa-claro);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-title i {
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
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>

    <main>
        <div class="profile-container">
            <div class="profile-card">
                <h1 class="profile-title">
                    <i class="fas fa-user-circle"></i>
                    Meu Perfil
                </h1>
                
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
                            <label for="senha" class="form-label">Nova Senha</label>
                            <div class="password-wrapper">
                                <input type="password" id="senha" name="senha" class="form-input" placeholder="********">
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('senha')"></i>
                            </div>
                            <small style="color: var(--cinza-escuro); font-size: 0.8rem; margin-top: 5px; display: block;">
                                Deixe em branco para manter a senha atual
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-input" placeholder="Confirme a nova senha">
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmar_senha')"></i>
                            </div>
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

                        <!-- NOVO CAMPO: Instruções de Entrega -->
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
                        
                        <button type="button" class="btn btn-secondary" onclick="abrirCartoesModal()">
                            <i class="fas fa-credit-card"></i> Meus Cartões
                        </button>
                        
                        <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
                            <i class="fas fa-trash-alt"></i> Excluir Conta
                        </button>
                        
                        <a href="sair.php" class="btn btn-secondary">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </div>
                </form>
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

    <!-- Modal de Gerenciamento de Cartões -->
    <div id="cartoesModal" class="modal">
        <div class="modal-content large">
            <span class="close" onclick="fecharCartoesModal()">&times;</span>
            
            <h3 class="modal-title">
                <i class="fas fa-credit-card"></i>
                Meus Cartões
            </h3>
            
            <!-- Lista de Cartões -->
            <div class="cartoes-lista" id="listaCartoes">
                <p style="text-align: center; color: var(--cinza-escuro);">Carregando cartões...</p>
            </div>
            
            <!-- Formulário para Adicionar Novo Cartão -->
            <div id="formNovoCartao" style="border-top: 1px solid var(--cinza-medio); padding-top: 20px;">
                <h4 style="margin-bottom: 15px; color: var(--destaque);">
                    <i class="fas fa-plus-circle"></i> Adicionar Novo Cartão
                </h4>
                
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
                    
                    <div class="button-group" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Cartão
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="fecharCartoesModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
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

    // ========== FUNÇÕES DO MODAL DE CARTÕES ==========

    let cartoes = [];
    let cartaoParaExcluir = null;

    // Abrir modal de cartões
    function abrirCartoesModal() {
        document.getElementById('cartoesModal').style.display = 'block';
        carregarCartoes();
    }

    // Fechar modal de cartões
    function fecharCartoesModal() {
        document.getElementById('cartoesModal').style.display = 'none';
    }

    // Fechar modal de exclusão de cartão
    function fecharExcluirCartaoModal() {
        document.getElementById('confirmExcluirCartaoModal').style.display = 'none';
        cartaoParaExcluir = null;
    }

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
                    '<p style="text-align: center; color: var(--cinza-escuro);">Erro ao carregar cartões.</p>';
            });
    }

    // Exibir cartões na lista
    function exibirCartoes() {
        const listaCartoes = document.getElementById('listaCartoes');
        
        if (cartoes.length === 0) {
            listaCartoes.innerHTML = 
                '<p style="text-align: center; color: var(--cinza-escuro);">Nenhum cartão cadastrado.</p>';
            return;
        }
        
        let html = '';
        cartoes.forEach(cartao => {
            const numeroMascarado = '**** **** **** ' + (cartao.numero_cartao ? cartao.numero_cartao.slice(-4) : '****');
            const principal = cartao.principal === 'sim' ? '<span class="cartao-principal">(Principal)</span>' : '';
            
            html += `
                <div class="cartao-item">
                    <div class="cartao-info">
                        <div class="cartao-numero">${cartao.bandeira} ${principal}</div>
                        <div class="cartao-detalhes">
                            ${numeroMascarado} | ${cartao.nome_titular} | ${cartao.mes_validade}/${cartao.ano_validade}
                            ${cartao.apelido ? ` | ${cartao.apelido}` : ''}
                        </div>
                    </div>
                    <div class="cartao-acoes">
                        <button onclick="definirComoPrincipal(${cartao.id})" class="btn btn-small" ${cartao.principal === 'sim' ? 'disabled' : ''}>
                            <i class="fas fa-star"></i>
                        </button>
                        <button onclick="confirmarExcluirCartao(${cartao.id})" class="btn btn-danger btn-small">
                            <i class="fas fa-trash-alt"></i>
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
                alert('Cartão excluído com sucesso!');
            } else {
                alert('Erro ao excluir cartão: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir cartão.');
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
                alert('Cartão definido como principal com sucesso!');
            } else {
                alert('Erro ao definir cartão principal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao definir cartão principal.');
        });
    }

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

    // ========== INICIALIZAÇÃO ==========

    document.addEventListener('DOMContentLoaded', function() {
        // Preencher anos de validade
        preencherAnosValidade();
        
        // Adicionar máscaras aos campos do cartão
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
                        alert('Cartão com data de validade expirada!');
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
                        alert('Cartão adicionado com sucesso!');
                    } else {
                        alert('Erro ao adicionar cartão: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao adicionar cartão.');
                });
            });
        }
    });

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