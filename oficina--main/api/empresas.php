<?php
// =====================================================================
// empresas.php - API para listar empresas
// =====================================================================

require_once '../config.php';

// Verificar autenticação
$session = Session::getInstance();
$session->requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

// GET - Listar empresas
if ($method === 'GET') {
    $sql = "SELECT id, nome, cnpj, email, telefone, cidade, estado FROM empresas WHERE ativo = TRUE ORDER BY nome";
    $empresas = $db->fetchAll($sql);
    
    echo json_encode([
        'sucesso' => true,
        'empresas' => $empresas
    ]);
    exit;
}

// Método não permitido
http_response_code(405);
echo json_encode([
    'sucesso' => false,
    'mensagem' => 'Método não permitido'
]);
