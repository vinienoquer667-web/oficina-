<?php
// =====================================================================
// api/recuperar_senha.php - Endpoint de Recuperação de Senha
// =====================================================================

require_once '../config.php';

// Processar solicitação de recuperação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar email
    if (!isset($input['email']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Email não fornecido'
        ]);
        exit;
    }
    
    $email = trim($input['email']);
    
    // Validar formato do email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Email inválido'
        ]);
        exit;
    }
    
    $db = Database::getInstance();
    
    try {
        // Verificar se email existe no banco
        $usuario = $db->fetchOne("SELECT id, nome, cpf FROM usuarios WHERE email = ? AND ativo = TRUE", [$email]);
        
        if (!$usuario) {
            // Por segurança, não informamos se o email existe ou não
            http_response_code(200);
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Se o email estiver cadastrado, você receberá instruções para recuperar sua senha.'
            ]);
            exit;
        }
        
        // Gerar token de recuperação
        $token = bin2hex(random_bytes(32));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Simulação: Em um sistema real, aqui enviariamos um email com o link
        // mail($email, "Recuperação de Senha", "Seu token: $token");
        
        // Por enquanto, vamos apenas registrar no log
        error_log("Token de recuperação para usuário {$usuario['id']}: $token (expira em: $expiracao)");
        
        http_response_code(200);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Se o email estiver cadastrado, você receberá instruções para recuperar sua senha.',
            'debug_token' => $token // Apenas para demonstração - remover em produção
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao processar solicitação: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Processar redefinição de senha com token
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'reset')) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos
    if (!isset($input['token']) || !isset($input['nova_senha']) || !isset($input['confirmar_senha'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Campos obrigatórios não fornecidos'
        ]);
        exit;
    }
    
    $token = $input['token'];
    $novaSenha = $input['nova_senha'];
    $confirmarSenha = $input['confirmar_senha'];
    
    // Validar senhas
    if ($novaSenha !== $confirmarSenha) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'As senhas não coincidem'
        ]);
        exit;
    }
    
    if (strlen($novaSenha) < 6) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'A senha deve ter no mínimo 6 caracteres'
        ]);
        exit;
    }
    
    $db = Database::getInstance();
    $auth = new Auth();
    
    try {
        // Em produção, verificaríamos o token na tabela de recuperação
        // Por enquanto, vamos apenas simular a redefinição
        
        // Hash da nova senha
        $senha_hash = $auth->hashPassword($novaSenha);
        
        // Aqui você atualizaria a senha do usuário associado ao token
        // UPDATE usuarios SET senha = ? WHERE id = (SELECT usuario_id FROM recuperacao_senha WHERE token = ? AND expiracao > NOW() AND usado = FALSE)
        
        http_response_code(200);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Senha redefinida com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao redefinir senha: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>
