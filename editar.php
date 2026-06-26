<?php
// =====================================================================
// editar.php - Atualizar Estágio
// =====================================================================

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
session_start();

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não autenticado'
    ]);
    exit;
}

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
$usuario_id = $_SESSION['usuario_id'];
$usuario_perfil = $_SESSION['usuario_perfil'];

// Verificar se estágio existe e se tem permissão
$sql_check = "SELECT usuario_id, status FROM estagios WHERE id = ?";
$resultado = executarQuery($conn, $sql_check, "i", [$estagio_id]);
if (!$resultado['sucesso']) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao verificar estágio'
    ]);
    exit;
}

$stmt = $resultado['stmt'];
$stmt->bind_result($estag_usuario_id, $status);
$stmt->fetch();
$stmt->close();

// Verificar permissões
if ($usuario_perfil === 'estagiario' && $estag_usuario_id !== $usuario_id) {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Permissão negada'
    ]);
    exit;
}

// Validar se pode editar conforme status
if ($status !== 'abertura' && !in_array($usuario_perfil, ['admin', 'orientador', 'supervisor'])) {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Estágio não pode ser editado neste status'
    ]);
    exit;
}

// Preparar dados para atualização
$updates = [];
$tipos = "";
$valores = [];

if (isset($input['status'])) {
    $updates[] = "status = ?";
    $tipos .= "s";
    $valores[] = $input['status'];
}

if (isset($input['data_inicio'])) {
    $updates[] = "data_inicio = ?";
    $tipos .= "s";
    $valores[] = $input['data_inicio'];
}

if (isset($input['data_fim'])) {
    $updates[] = "data_fim = ?";
    $tipos .= "s";
    $valores[] = $input['data_fim'];
}

if (isset($input['carga_horaria_cumprida'])) {
    $updates[] = "carga_horaria_cumprida = ?";
    $tipos .= "i";
    $valores[] = intval($input['carga_horaria_cumprida']);
}

if (isset($input['orientador_id'])) {
    $updates[] = "orientador_id = ?";
    $tipos .= "i";
    $valores[] = $input['orientador_id'];
}

if (isset($input['descricao'])) {
    $updates[] = "descricao = ?";
    $tipos .= "s";
    $valores[] = $input['descricao'];
}

if (isset($input['observacoes'])) {
    $updates[] = "observacoes = ?";
    $tipos .= "s";
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
$tipos .= "i";
$valores[] = $estagio_id;

// Executar atualização
$sql = "UPDATE estagios SET " . implode(", ", $updates) . " WHERE id = ?";
$resultado = executarQuery($conn, $sql, $tipos, $valores);

if (!$resultado['sucesso']) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao atualizar estágio: ' . $resultado['erro']
    ]);
    exit;
}

// Registrar log
registrarLog($conn, $usuario_id, 'EDITAR_ESTAGIO', 'estagios', $estagio_id, 'Estágio atualizado');

http_response_code(200);
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Estágio atualizado com sucesso'
]);

?>
