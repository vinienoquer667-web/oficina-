<?php
// =====================================================================
// auth_login.php - Endpoint de Autenticação (API)
// =====================================================================

require_once '../config.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido'
    ]);
    exit;
}

// Receber dados do formulário
$input = json_decode(file_get_contents('php://input'), true);

// Validar entrada
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário ou senha não fornecidos'
    ]);
    exit;
}

$email = $input['username'];
$senha = $input['password'];

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Email inválido'
    ]);
    exit;
}

// Usar classe Auth para login (busca apenas por email)
$auth = new Auth();
$resultado = $auth->login('email', $email, $senha);

if ($resultado['sucesso']) {
    http_response_code(200);
} else {
    http_response_code(401);
}

echo json_encode($resultado);
