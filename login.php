<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jardim Secret</title>
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

.login-container {
    background: var(--branco);
    border-radius: 16px;
    box-shadow: var(--sombra);
    width: 100%;
    max-width: 420px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(212, 165, 165, 0.2);
}

.login-header {
    background: linear-gradient(135deg, var(--rosa-primario), var(--rosa-escuro));
    color: var(--branco);
    padding: 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.login-header::before {
    content: "";
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.login-header h2 {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 8px;
    position: relative;
}

.login-header p {
    opacity: 0.9;
    font-weight: 300;
}

.login-form {
    padding: 30px;
}

.input-group {
    margin-bottom: 20px;
    position: relative;
}

.input-group input {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid var(--cinza-medio);
    border-radius: 8px;
    font-size: 15px;
    transition: var(--transicao);
    background-color: var(--cinza-claro);
}

.input-group input:focus {
    border-color: var(--rosa-primario);
    box-shadow: 0 0 0 3px rgba(212, 165, 165, 0.2);
    outline: none;
    background-color: var(--branco);
}

.show-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--rosa-primario);
    transition: var(--transicao);
}

.show-password:hover {
    color: var(--rosa-escuro);
}

.btn-login {
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

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 165, 165, 0.3);
}

.bottom-text {
    text-align: center;
    margin-top: 24px;
    color: var(--cinza-escuro);
    font-size: 14px;
}

.bottom-text a {
    color: var(--rosa-primario);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transicao);
}

.bottom-text a:hover {
    color: var(--rosa-escuro);
    text-decoration: underline;
}

.divider {
    display: flex;
    align-items: center;
    margin: 20px 0;
    color: var(--cinza-escuro);
    font-size: 14px;
}

.divider::before, .divider::after {
    content: "";
    flex: 1;
    border-bottom: 1px solid var(--cinza-medio);
}

.divider::before {
    margin-right: 10px;
}

.divider::after {
    margin-left: 10px;
}

/* Efeitos decorativos */
.leaf-decoration {
    position: absolute;
    opacity: 0.05;
    z-index: 0;
}

.leaf-1 {
    top: -30px;
    left: -30px;
    width: 100px;
    height: 100px;
    background: var(--rosa-primario);
    border-radius: 50%;
}

.leaf-2 {
    bottom: -20px;
    right: -20px;
    width: 80px;
    height: 80px;
    background: var(--rosa-escuro);
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
    transform: rotate(30deg);
}

/* Responsividade */
@media (max-width: 480px) {
    .login-container {
        border-radius: 0;
    }
    
    .login-header {
        padding: 25px 20px;
    }
    
    .login-form {
        padding: 25px 20px;
    }
}
    </style>
</head>
<body>
    <div class="login-container">        
        <div class="login-header">
            <h2> Bem-vinda de volta!</h2>
            <p>Acesse sua conta Jardim Secret</p>
        </div>

        <form class="login-form" method="POST" action="exibeLogin.php">
            <div class="input-group">
                <input type="text" name="usuarioLogin" id="usuarioLogin" placeholder="Nome de usuário" required />
            </div>
            <div class="input-group">
                <input type="password" name="loginSenha" id="loginSenha" placeholder="Senha" required />
                <span class="show-password" onclick="togglePassword()">
                    <i class="fas fa-eye-slash"></i>
                </span>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
            
            <div class="bottom-text">
                <p>Não possui conta? <a href="cadastro1.php">Cadastre-se</a></p>
                <p><a href="index.html">Voltar para o início</a></p>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("loginSenha");
            const icon = document.querySelector(".show-password i");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>
</body>
</html>