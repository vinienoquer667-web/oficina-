<?php
// =====================================================================
// api/cadastro_usuario.php - Endpoint de Cadastro de Usuários
// =====================================================================

require_once '../config.php';

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos obrigatórios
    $campos_obrigatorios = ['cpf', 'nome', 'email', 'senha', 'perfil'];
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($input[$campo]) || empty($input[$campo])) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => "Campo obrigatório não fornecido: $campo"
            ]);
            exit;
        }
    }
    
    $cpf = preg_replace('/\D/', '', $input['cpf']);
    $nome = trim($input['nome']);
    $email = trim($input['email']);
    $senha = $input['senha'];
    $perfil = $input['perfil'];
    
    // Validar CPF
    if (strlen($cpf) !== 11) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'CPF inválido'
        ]);
        exit;
    }
    
    // Validar perfil
    $perfis_validos = ['estagiario', 'orientador', 'supervisor', 'admin'];
    if (!in_array($perfil, $perfis_validos)) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Perfil inválido'
        ]);
        exit;
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Email inválido'
        ]);
        exit;
    }
    
    // Validar senha (mínimo 6 caracteres)
    if (strlen($senha) < 6) {
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
        // Verificar se CPF já existe
        $cpf_existe = $db->fetchOne("SELECT id FROM usuarios WHERE cpf = ?", [$cpf]);
        if ($cpf_existe) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'CPF já cadastrado'
            ]);
            exit;
        }
        
        // Verificar se email já existe
        $email_existe = $db->fetchOne("SELECT id FROM usuarios WHERE email = ?", [$email]);
        if ($email_existe) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Email já cadastrado'
            ]);
            exit;
        }
        
        // Hash da senha
        $senha_hash = $auth->hashPassword($senha);
        
        // Inserir usuário
        $sql = "INSERT INTO usuarios (cpf, nome, email, senha, perfil, ativo) 
                VALUES (?, ?, ?, ?, ?, TRUE)";
        
        $db->query($sql, [$cpf, $nome, $email, $senha_hash, $perfil]);
        
        http_response_code(201);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Usuário cadastrado com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao cadastrar usuário: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>
