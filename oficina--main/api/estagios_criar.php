<?php
// =====================================================================
// cadastro.php - Criar Novo Estágio
// =====================================================================

require_once '../config.php';

// Inicializar sessão e verificar autenticação
$session = Session::getInstance();
$session->requireAuth();

// Verificar permissão (apenas admin e orientador podem criar)
$session->requireProfile(['admin', 'orientador', 'supervisor']);

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

// Inicializar Database
$db = Database::getInstance();

// Validar se usuário é estagiário
$sql_check = "SELECT perfil FROM usuarios WHERE id = ?";
$user = $db->fetchOne($sql_check, [$usuario_id]);

if (!$user || $user['perfil'] !== 'estagiario') {
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

$curso_existe = $db->fetchOne($sql_curso, [$curso_id]);
$empresa_existe = $db->fetchOne($sql_empresa, [$empresa_id]);

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

try {
    $db->query($sql, [
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

    $estagio_id = $db->lastInsertId();

    // Registrar log
    registrarLog(null, $session->getUserId(), 'CRIAR_ESTAGIO', 'estagios', $estagio_id, 'Novo estágio criado');

    http_response_code(201);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Estágio criado com sucesso',
        'estagio_id' => $estagio_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao criar estágio: ' . $e->getMessage()
    ]);
}

?>
