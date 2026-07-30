<?php
// =====================================================================
// login_page.php - Página de Login (HTML)
// =====================================================================
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFSertãoPE - SGE | Login</title>
    <link rel="stylesheet" href="../login.css">
</head>
<body>

    <div class="login-container">
        <div class="brand-section">
            <img src="../img/if.png" alt="Logo IFSertãoPE">
            <h1>SGE Estágios</h1>
            <p>Sistema de Gestão de Estágios</p>
        </div>

        <form id="loginForm">
            
            <div class="form-group">
                <label for="username">Email</label>
                <input type="email" id="username" name="username" placeholder="Digite o seu email" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Digite a sua senha" required autocomplete="current-password">
            </div>

            <div class="options-row">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Lembrar-me
                </label>
                <a href="recuperar_senha.php" class="forgot-password">Esqueceu-se da senha?</a>
            </div>

            <div id="error-message" style="color: #e74c3c; margin-bottom: 15px; font-size: 0.85rem; text-align: left; display: none; font-weight: 600;"></div>

            <button type="submit" id="btnSubmit" class="btn-login">Entrar</button>
        </form>

        <div class="footer-links">
            <p>Novo por aqui? <a href="cadastro.php">Cadastre o seu perfil</a></p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const errorDiv = document.getElementById('error-message');
            const btnSubmit = document.getElementById('btnSubmit');
            
            const email = document.getElementById('username').value.trim();
            const senha = document.getElementById('password').value;

            errorDiv.style.display = 'none';

            if(email === '' || senha === '') {
                errorDiv.innerText = 'Preencha todos os campos.';
                errorDiv.style.display = 'block';
                return;
            }

            // UX: Desativa o botão para evitar cliques múltiplos
            btnSubmit.disabled = true;
            btnSubmit.innerText = 'Autenticando...';
            btnSubmit.style.opacity = '0.7';

            try {
                const response = await fetch('../api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: email, 
                        password: senha
                    })
                });

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new TypeError("Resposta inválida");
                }

                const dados = await response.json();

                if(response.ok) {
                    // Guarda com segurança as informações essenciais vindas do banco PHP
                    localStorage.setItem('usuario_id', dados.usuario.id);
                    localStorage.setItem('usuario_nome', dados.usuario.nome);
                    localStorage.setItem('usuario_perfil', dados.usuario.perfil);
                    
                    // Salva o e-mail retornado pelo backend para alimentar os painéis específicos de cada perfil
                    if(dados.usuario.email) {
                        localStorage.setItem('usuario_email', dados.usuario.email);
                    }

                    // Define o nome de exibição no painel do professor
                    localStorage.setItem('sge_professor_logado', dados.usuario.nome);

                    // Redireciona para o arquivo PHP que vai direcionar baseado no perfil
                    window.location.href = '../redirect.php';
                } else {
                    errorDiv.innerText = dados.mensagem || 'Usuário ou senha incorretos.';
                    errorDiv.style.display = 'block';
                    
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = 'Entrar';
                    btnSubmit.style.opacity = '1';
                }

            } catch(error) {
                // Mensagem limpa e amigável em caso de qualquer falha estrutural ou de rede
                errorDiv.innerText = 'Usuário ou senha incorretos.';
                errorDiv.style.display = 'block';
                
                btnSubmit.disabled = false;
                btnSubmit.innerText = 'Entrar';
                btnSubmit.style.opacity = '1';
            }
        });
    </script>
</body>
</html>
