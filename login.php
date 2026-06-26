<?php
// =====================================================================
// login.php - Autenticação de Usuários
// =====================================================================

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

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

$cpf = preg_replace('/\D/', '', $input['username']); // Remove formatação
$senha = $input['password'];

// Validar CPF
if (strlen($cpf) !== 11) {
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'CPF inválido'
    ]);
    exit;
}

// Buscar usuário no banco
$sql = "SELECT id, cpf, nome, email, perfil, ativo FROM usuarios WHERE cpf = ? AND senha = MD5(?)";
$resultado = executarQuery($conn, $sql, "ss", [$cpf, $senha]);

if (!$resultado['sucesso']) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao consultar banco de dados'
    ]);
    exit;
}

$stmt = $resultado['stmt'];
$stmt->bind_result($id, $cpf_db, $nome, $email, $perfil, $ativo);
$stmt->fetch();
$stmt->close();

// Verificar credenciais
if (!$id) {
    registrarLog($conn, null, 'LOGIN_FALHA', 'usuarios', null, 'CPF: ' . $cpf);
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário ou senha incorretos'
    ]);
    exit;
}

// Verificar se usuário está ativo
if (!$ativo) {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário desativado. Entre em contato com o administrador'
    ]);
    exit;
}

// Login bem-sucedido
session_start();
$_SESSION['usuario_id'] = $id;
$_SESSION['usuario_nome'] = $nome;
$_SESSION['usuario_email'] = $email;
$_SESSION['usuario_perfil'] = $perfil;

registrarLog($conn, $id, 'LOGIN_SUCESSO', 'usuarios', $id, '');

http_response_code(200);
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Login realizado com sucesso',
    'usuario' => [
        'id' => $id,
        'nome' => $nome,
        'email' => $email,
        'perfil' => $perfil
    ]
]);

?>
