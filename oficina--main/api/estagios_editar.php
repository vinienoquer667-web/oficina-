<?php
// =====================================================================
// editar.php - Atualizar Estágio
// =====================================================================

require_once '../config.php';

// Inicializar sessão e verificar autenticação
$session = Session::getInstance();
$session->requireAuth();

// Verificar método PUT
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido'
    ]);
    exit;
}

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

// Validar ID do estágio
if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do estágio não fornecido'
    ]);
    exit;
}

$estagio_id = intval($input['id']);
$usuario_id = $session->getUserId();
$usuario_perfil = $session->getUserProfile();

// Inicializar Database
$db = Database::getInstance();

// Verificar se estágio existe e se tem permissão
$sql_check = "SELECT usuario_id, status FROM estagios WHERE id = ?";
$estagio = $db->fetchOne($sql_check, [$estagio_id]);

if (!$estagio) {
    http_response_code(404);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Estágio não encontrado'
    ]);
    exit;
}

// Verificar permissões
if ($usuario_perfil === 'estagiario' && $estagio['usuario_id'] !== $usuario_id) {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Permissão negada'
    ]);
    exit;
}

// Validar se pode editar conforme status
if ($estagio['status'] !== 'abertura' && !in_array($usuario_perfil, ['admin', 'orientador', 'supervisor'])) {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Estágio não pode ser editado neste status'
    ]);
    exit;
}

// Preparar dados para atualização
$updates = [];
$valores = [];

if (isset($input['status'])) {
    $updates[] = "status = ?";
    $valores[] = $input['status'];
}

if (isset($input['data_inicio'])) {
    $updates[] = "data_inicio = ?";
    $valores[] = $input['data_inicio'];
}

if (isset($input['data_fim'])) {
    $updates[] = "data_fim = ?";
    $valores[] = $input['data_fim'];
}

if (isset($input['carga_horaria_cumprida'])) {
    $updates[] = "carga_horaria_cumprida = ?";
    $valores[] = intval($input['carga_horaria_cumprida']);
}

if (isset($input['orientador_id'])) {
    $updates[] = "orientador_id = ?";
    $valores[] = $input['orientador_id'];
}

if (isset($input['descricao'])) {
    $updates[] = "descricao = ?";
    $valores[] = $input['descricao'];
}

if (isset($input['observacoes'])) {
    $updates[] = "observacoes = ?";
    $valores[] = $input['observacoes'];
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhum campo para atualizar'
    ]);
    exit;
}

// Adicionar ID e timestamp
$updates[] = "data_atualizacao = CURRENT_TIMESTAMP";
$valores[] = $estagio_id;

// Executar atualização
$sql = "UPDATE estagios SET " . implode(", ", $updates) . " WHERE id = ?";

try {
    $db->query($sql, $valores);

    // Registrar log
    registrarLog(null, $usuario_id, 'EDITAR_ESTAGIO', 'estagios', $estagio_id, 'Estágio atualizado');

    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Estágio atualizado com sucesso'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao atualizar estágio: ' . $e->getMessage()
    ]);
}

?>
