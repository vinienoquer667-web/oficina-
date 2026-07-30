<?php
// =====================================================================
// cadastro_usuario.php - Tela de Cadastro de Usuários (Segura)
// =====================================================================

require_once '../config.php';

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $perfil = $_POST['perfil'] ?? '';
    
    // Limpar CPF (remover caracteres não numéricos)
    $cpf_limpo = preg_replace('/\D/', '', $cpf);
    
    // Validações
    $erros = [];
    
    if (strlen($cpf_limpo) !== 11) {
        $erros[] = 'CPF inválido. Deve conter 11 dígitos.';
    }
    
    if (empty($nome)) {
        $erros[] = 'Nome é obrigatório.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido.';
    }
    
    if (strlen($senha) < 6) {
        $erros[] = 'A senha deve ter no mínimo 6 caracteres.';
    }
    
    if ($senha !== $confirmar_senha) {
        $erros[] = 'As senhas não coincidem.';
    }
    
    $perfis_validos = ['estagiario', 'orientador', 'supervisor', 'admin'];
    if (!in_array($perfil, $perfis_validos)) {
        $erros[] = 'Perfil inválido.';
    }
    
    // Se houver erros, exibir
    if (!empty($erros)) {
        $erro_msg = implode('<br>', $erros);
    } else {
        // Processar cadastro no banco
        $db = Database::getInstance();
        $auth = new Auth();
        
        try {
            // Verificar se CPF já existe
            $cpf_existe = $db->fetchOne("SELECT id FROM usuarios WHERE cpf = ?", [$cpf_limpo]);
            if ($cpf_existe) {
                $erro_msg = 'CPF já cadastrado no sistema.';
            } else {
                // Verificar se email já existe
                $email_existe = $db->fetchOne("SELECT id FROM usuarios WHERE email = ?", [$email]);
                if ($email_existe) {
                    $erro_msg = 'Email já cadastrado no sistema.';
                } else {
                    // Hash da senha
                    $senha_hash = $auth->hashPassword($senha);
                    
                    // Inserir usuário
                    $sql = "INSERT INTO usuarios (cpf, nome, email, senha, perfil, ativo) 
                            VALUES (?, ?, ?, ?, ?, TRUE)";
                    
                    $db->query($sql, [$cpf_limpo, $nome, $email, $senha_hash, $perfil]);
                    
                    // Cadastro realizado com sucesso
                    $sucesso_msg = 'Usuário cadastrado com sucesso! Redirecionando para login...';
                    
                    // Redirecionar após 2 segundos
                    header("refresh:2;url=login.php");
                }
            }
        } catch (Exception $e) {
            $erro_msg = 'Erro ao cadastrar usuário: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFSertãoPE - SGE | Cadastro de Usuário</title>
    <link rel="stylesheet" href="../login.css">
    <style>
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success-message {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="brand-section">
            <img src="../img/if.png" alt="Logo IFSertãoPE">
            <h1>SGE Estágios</h1>
            <p>Cadastro de Usuário Seguro</p>
        </div>

        <?php if (isset($erro_msg)): ?>
            <div class="error-message">
                <strong>Erro:</strong><br>
                <?php echo $erro_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($sucesso_msg)): ?>
            <div class="success-message">
                <strong>Sucesso:</strong><br>
                <?php echo $sucesso_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite o seu CPF" required maxlength="14" 
                       value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o seu nome completo" required
                       value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Digite o seu email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite a sua senha (mínimo 6 caracteres)" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a sua senha" required minlength="6">
            </div>

            <div class="form-group">
                <label for="perfil">Perfil</label>
                <select id="perfil" name="perfil" required>
                    <option value="">Selecione o perfil</option>
                    <option value="estagiario" <?php echo (isset($_POST['perfil']) && $_POST['perfil'] === 'estagiario') ? 'selected' : ''; ?>>Estagiário</option>
                    <option value="orientador" <?php echo (isset($_POST['perfil']) && $_POST['perfil'] === 'orientador') ? 'selected' : ''; ?>>Orientador</option>
                    <option value="supervisor" <?php echo (isset($_POST['perfil']) && $_POST['perfil'] === 'supervisor') ? 'selected' : ''; ?>>Supervisor</option>
                </select>
            </div>

            <button type="submit" class="btn-login">Cadastrar</button>
        </form>

        <div class="footer-links">
            <p>Já tem conta? <a href="login.php">Faça login</a></p>
            <p><a href="recuperar_senha.php">Esqueceu a senha?</a></p>
        </div>
    </div>

    <script>
        // Máscara CPF
        const cpfInput = document.getElementById('cpf');

        cpfInput.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '');

            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

            this.value = valor;
        });

        // Validação no submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmar = document.getElementById('confirmar_senha').value;
            
            if (senha !== confirmar) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return false;
            }
            
            if (senha.length < 6) {
                e.preventDefault();
                alert('A senha deve ter no mínimo 6 caracteres!');
                return false;
            }
        });
    </script>
</body>
</html>
