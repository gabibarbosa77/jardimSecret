<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Jardim Secret</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
    --rosa-primario: #d4a5a5;
    --rosa-escuro: #b87c7c;
    --rosa-claro: #f9f0f0;
    --branco: #ffffff;
    --cinza-claro: #f8f9fa;
    --cinza-medio: #e9ecef;
    --cinza-escuro: #495057;
    --sombra: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transicao: all 0.3s ease;
    --vermelho-erro: #e74c3c;
    --laranja-alerta: #f39c12;
    --verde-sucesso: #2ecc71;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, var(--rosa-claro), var(--branco));
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.form-container {
    background: var(--branco);
    border-radius: 16px;
    box-shadow: var(--sombra);
    width: 100%;
    max-width: 600px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(212, 165, 165, 0.2);
}

.form-header {
    background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
    color: var(--branco);
    padding: 25px;
    text-align: center;
}

.form-header h2 {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.form-body {
    padding: 25px;
}

.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-error {
    background-color: #fee;
    border: 1px solid var(--vermelho-erro);
    color: var(--vermelho-erro);
}

.alert-success {
    background-color: #efef;
    border: 1px solid var(--verde-sucesso);
    color: var(--verde-sucesso);
}

.alert i {
    font-size: 16px;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: var(--cinza-escuro);
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 12px 40px 12px 15px;
    border: 1px solid var(--cinza-medio);
    border-radius: 8px;
    font-size: 15px;
    transition: var(--transicao);
    background-color: var(--cinza-claro);
}

.form-control:focus {
    border-color: var(--rosa-primario);
    box-shadow: 0 0 0 3px rgba(212, 165, 165, 0.2);
    outline: none;
    background-color: var(--branco);
}

.input-icon {
    position: absolute;
    right: 15px;
    top: 38px;
    cursor: pointer;
    color: var(--rosa-primario);
    transition: var(--transicao);
}

.input-icon:hover {
    color: var(--rosa-escuro);
}

.btn-submit {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
    color: var(--branco);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transicao);
    margin-top: 10px;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 165, 165, 0.3);
}

.form-footer {
    text-align: center;
    padding: 20px;
    border-top: 1px solid var(--cinza-medio);
    font-size: 14px;
    color: var(--cinza-escuro);
}

.form-footer a {
    color: var(--rosa-primario);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transicao);
}

.form-footer a:hover {
    color: var(--rosa-escuro);
    text-decoration: underline;
}

.password-strength {
    height: 4px;
    background: var(--cinza-medio);
    margin-top: 5px;
    border-radius: 2px;
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0;
    background: #e74c3c;
    transition: var(--transicao);
}

.leaf-decoration {
    position: absolute;
    opacity: 0.05;
    z-index: 0;
}

.leaf-1 {
    top: -40px;
    left: -40px;
    width: 120px;
    height: 120px;
    background: var(--rosa-primario);
    border-radius: 50%;
}

.leaf-2 {
    bottom: -30px;
    right: -30px;
    width: 100px;
    height: 100px;
    background: var(--rosa-escuro);
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
    transform: rotate(30deg);
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-container {
        max-width: 500px;
    }
}

@media (max-width: 480px) {
    .form-container {
        border-radius: 0;
    }
    
    .form-header {
        padding: 20px 15px;
    }
    
    .form-body {
        padding: 20px 15px;
    }
}
    </style>
</head>
<body>
    <div class="form-container">        
        <div class="form-header">
            <h2>Crie sua conta</h2>
        </div>

        <div class="form-body">
            <?php
            // Exibir mensagens de erro
            if (isset($_GET['error'])) {
                $error_type = $_GET['error'];
                if ($error_type === 'username') {
                    echo '<div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            Não é possível cadastrar, pois o nome de usuário já existe!
                          </div>';
                } elseif ($error_type === 'cpf') {
                    echo '<div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            Não é possível cadastrar, pois o CPF já está cadastrado!
                          </div>';
                } elseif ($error_type === 'database') {
                    echo '<div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            Erro ao cadastrar no banco de dados. Tente novamente.
                          </div>';
                }
            }

            // Exibir mensagem de sucesso
            if (isset($_GET['success'])) {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Cadastrado com sucesso! Redirecionando para o login...
                      </div>';
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "login.php";
                        }, 2000);
                      </script>';
            }
            ?>

            <form action="cadastro.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nomeCompleto">Nome Completo</label>
                        <input type="text" class="form-control" id="nomeCompleto" name="nomeCompleto" placeholder="Digite seu nome completo" 
                               value="<?php echo isset($_GET['nomeCompleto']) ? htmlspecialchars($_GET['nomeCompleto']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nomeUsuario">Nome de Usuário</label>
                        <input type="text" class="form-control" id="nomeUsuario" name="nomeUsuario" placeholder="Escolha um nome de usuário" 
                               value="<?php echo isset($_GET['nomeUsuario']) ? htmlspecialchars($_GET['nomeUsuario']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="emailUsuario">E-mail</label>
                        <input type="email" class="form-control" id="emailUsuario" name="emailUsuario" placeholder="Digite seu e-mail" 
                               value="<?php echo isset($_GET['emailUsuario']) ? htmlspecialchars($_GET['emailUsuario']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefoneUsuario">Telefone</label>
                        <input type="tel" class="form-control" id="telefoneUsuario" name="telefoneUsuario" oninput="mascTelefone(this)" placeholder="(00) 00000-0000" 
                               value="<?php echo isset($_GET['telefoneUsuario']) ? htmlspecialchars($_GET['telefoneUsuario']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cpfUsuario">CPF</label>
                        <input type="text" class="form-control" id="cpfUsuario" name="cpfUsuario" oninput="mascaraCPF(this)" placeholder="000.000.000-00" 
                               value="<?php echo isset($_GET['cpfUsuario']) ? htmlspecialchars($_GET['cpfUsuario']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="dataNasc">Data de Nascimento</label>
                        <input type="text" class="form-control" id="dataNasc" name="dataNasc" oninput="aplicarMascaraData(this)" placeholder="DD/MM/AAAA" 
                               value="<?php echo isset($_GET['dataNasc']) ? htmlspecialchars($_GET['dataNasc']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="senhaUsuario">Senha</label>
                        <input type="password" class="form-control" id="senhaUsuario" name="senhaUsuario" placeholder="Crie uma senha forte" required>
                        <i class="fas fa-eye-slash input-icon" onclick="togglePassword('senhaUsuario', this)"></i>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirmacaoSenha">Confirme a Senha</label>
                        <input type="password" class="form-control" id="confirmacaoSenha" name="confirmacaoSenha" placeholder="Repita sua senha" required>
                        <i class="fas fa-eye-slash input-icon" onclick="togglePassword('confirmacaoSenha', this)"></i>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cepUsuario">CEP</label>
                        <input type="text" class="form-control" id="cepUsuario" name="cepUsuario" oninput="mascaraCEP(this)" placeholder="00000-000" 
                               value="<?php echo isset($_GET['cepUsuario']) ? htmlspecialchars($_GET['cepUsuario']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="enderecoUsuario">Endereço</label>
                        <input type="text" class="form-control" id="enderecoUsuario" name="enderecoUsuario" placeholder="Rua, Avenida, etc." 
                               value="<?php echo isset($_GET['enderecoUsuario']) ? htmlspecialchars($_GET['enderecoUsuario']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="numUsuario">Número</label>
                        <input type="text" class="form-control" id="numUsuario" name="numUsuario" placeholder="Número" 
                               value="<?php echo isset($_GET['numUsuario']) ? htmlspecialchars($_GET['numUsuario']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="complementoUsuario">Complemento</label>
                        <input type="text" class="form-control" id="complemento" name="complemento" placeholder="Apto, Bloco, etc." 
                               value="<?php echo isset($_GET['complemento']) ? htmlspecialchars($_GET['complemento']) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bairroUsuario">Bairro</label>
                        <input type="text" class="form-control" id="bairroUsuario" name="bairroUsuario" placeholder="Bairro" 
                               value="<?php echo isset($_GET['bairroUsuario']) ? htmlspecialchars($_GET['bairroUsuario']) : ''; ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Cadastrar</button>
            </form>
        </div>

        <div class="form-footer">
            <p>Já possui conta? <a href="login.php">Faça login</a></p>
            <p><a href="index.html">Voltar para o início</a></p>
        </div>
    </div>

    <script>
        function mascTelefone(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.substring(0, 11);

            if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else {
                value = value.replace(/^(\d*)/, '($1');
            }

            input.value = value;
        }

        function mascaraCPF(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.substring(0, 11);
            
            if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{0,3})/, '$1.$2');
            }
            
            input.value = value;
        }

        function mascaraCEP(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.substring(0, 8);
            
            if (value.length > 5) {
                value = value.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
            }
            
            input.value = value;
        }

        function aplicarMascaraData(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length > 8) valor = valor.substring(0, 8);

            if (valor.length <= 2) {
                valor = valor.replace(/(\d{1,2})/, '$1');
            } else if (valor.length <= 4) {
                valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
            } else {
                valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
            }

            input.value = valor;
        }

        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                iconElement.classList.remove('fa-eye-slash');
                iconElement.classList.add('fa-eye');
            } else {
                input.type = 'password';
                iconElement.classList.remove('fa-eye');
                iconElement.classList.add('fa-eye-slash');
            }
        }

        document.getElementById('senhaUsuario').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            let strength = 0;
            
            if (password.length > 0) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            const width = strength * 20;
            strengthBar.style.width = width + '%';
            
            if (strength <= 2) {
                strengthBar.style.backgroundColor = '#e74c3c';
            } else if (strength <= 4) {
                strengthBar.style.backgroundColor = '#f39c12';
            } else {
                strengthBar.style.backgroundColor = '#2ecc71';
            }
        });

        document.getElementById('cepUsuario').addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('enderecoUsuario').value = data.logradouro;
                            document.getElementById('bairroUsuario').value = data.bairro;
                        }
                    })
                    .catch(error => console.error('Erro ao buscar CEP:', error));
            }
        });
    </script>
</body>
</html>