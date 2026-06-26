<?php
// =====================================================================
// cadastro.php - Criar Novo Estágio
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

// Verificar permissão (apenas admin e orientador podem criar)
$usuario_perfil = $_SESSION['usuario_perfil'];
if (!in_array($usuario_perfil, ['admin', 'orientador', 'supervisor'])) {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Permissão negada'
    ]);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido'
    ]);
    exit;
}

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

// Validar dados obrigatórios
$campos_obrigatorios = ['usuario_id', 'curso_id', 'empresa_id', 'tipo'];
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

// Preparar dados
$usuario_id = intval($input['usuario_id']);
$curso_id = intval($input['curso_id']);
$empresa_id = intval($input['empresa_id']);
$tipo = $input['tipo'] === 'opcional' ? 'opcional' : 'obrigatorio';
$data_inicio = isset($input['data_inicio']) ? $input['data_inicio'] : null;
$data_fim = isset($input['data_fim']) ? $input['data_fim'] : null;
$carga_horaria = intval($input['carga_horaria_total'] ?? 400);
$descricao = $input['descricao'] ?? '';
$orientador_id = isset($input['orientador_id']) ? intval($input['orientador_id']) : null;

// Validar se usuário é estagiário
$sql_check = "SELECT perfil FROM usuarios WHERE id = ?";
$resultado = executarQuery($conn, $sql_check, "i", [$usuario_id]);
if (!$resultado['sucesso']) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao validar usuário'
    ]);
    exit;
}

$stmt = $resultado['stmt'];
$stmt->bind_result($perfil);
$stmt->fetch();
$stmt->close();

if ($perfil !== 'estagiario') {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário deve ser um estagiário'
    ]);
    exit;
}

// Validar curso e empresa existem
$sql_curso = "SELECT id FROM cursos WHERE id = ? AND ativo = TRUE";
$sql_empresa = "SELECT id FROM empresas WHERE id = ? AND ativo = TRUE";

$res_curso = executarQuery($conn, $sql_curso, "i", [$curso_id]);
$stmt_curso = $res_curso['stmt'];
$stmt_curso->bind_result($id_curso);
$curso_existe = $stmt_curso->fetch();
$stmt_curso->close();

$res_empresa = executarQuery($conn, $sql_empresa, "i", [$empresa_id]);
$stmt_empresa = $res_empresa['stmt'];
$stmt_empresa->bind_result($id_empresa);
$empresa_existe = $stmt_empresa->fetch();
$stmt_empresa->close();

if (!$curso_existe || !$empresa_existe) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Curso ou empresa inválidos'
    ]);
    exit;
}

// Inserir estágio
$sql = "INSERT INTO estagios (usuario_id, curso_id, empresa_id, orientador_id, tipo, status, 
                              data_inicio, data_fim, carga_horaria_total, descricao) 
        VALUES (?, ?, ?, ?, ?, 'abertura', ?, ?, ?, ?)";

$resultado = executarQuery($conn, $sql, "iiiisssIs", [
    $usuario_id,
    $curso_id,
    $empresa_id,
    $orientador_id,
    $tipo,
    $data_inicio,
    $data_fim,
    $carga_horaria,
    $descricao
]);

if (!$resultado['sucesso']) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao criar estágio: ' . $resultado['erro']
    ]);
    exit;
}

$estagio_id = $conn->insert_id;

// Registrar log
registrarLog($conn, $_SESSION['usuario_id'], 'CRIAR_ESTAGIO', 'estagios', $estagio_id, 'Novo estágio criado');

http_response_code(201);
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Estágio criado com sucesso',
    'estagio_id' => $estagio_id
]);

?>
