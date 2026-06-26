<?php
// =====================================================================
// index.php - Listar Estágios (GET) e Filtrar
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

$usuario_id = $_SESSION['usuario_id'];
$usuario_perfil = $_SESSION['usuario_perfil'];

// Construir query base conforme perfil
if ($usuario_perfil === 'admin') {
    // Admin vê todos
    $sql = "SELECT e.id, e.usuario_id, u.nome as aluno_nome, u.cpf, e.curso_id, c.nome as curso_nome, 
                   e.empresa_id, em.nome as empresa_nome, e.orientador_id, o.nome as orientador_nome,
                   e.tipo, e.status, e.data_inicio, e.data_fim, e.carga_horaria_total, 
                   e.carga_horaria_cumprida, e.data_criacao
            FROM estagios e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN cursos c ON e.curso_id = c.id
            LEFT JOIN empresas em ON e.empresa_id = em.id
            LEFT JOIN usuarios o ON e.orientador_id = o.id
            WHERE e.status != 'cancelado'
            ORDER BY e.data_criacao DESC";
} elseif ($usuario_perfil === 'estagiario') {
    // Estagiário vê só seus estágios
    $sql = "SELECT e.id, e.usuario_id, u.nome as aluno_nome, u.cpf, e.curso_id, c.nome as curso_nome, 
                   e.empresa_id, em.nome as empresa_nome, e.orientador_id, o.nome as orientador_nome,
                   e.tipo, e.status, e.data_inicio, e.data_fim, e.carga_horaria_total, 
                   e.carga_horaria_cumprida, e.data_criacao
            FROM estagios e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN cursos c ON e.curso_id = c.id
            LEFT JOIN empresas em ON e.empresa_id = em.id
            LEFT JOIN usuarios o ON e.orientador_id = o.id
            WHERE e.usuario_id = ? AND e.status != 'cancelado'
            ORDER BY e.data_criacao DESC";
} else {
    // Orientador/Supervisor vê estágios onde são responsáveis
    $sql = "SELECT e.id, e.usuario_id, u.nome as aluno_nome, u.cpf, e.curso_id, c.nome as curso_nome, 
                   e.empresa_id, em.nome as empresa_nome, e.orientador_id, o.nome as orientador_nome,
                   e.tipo, e.status, e.data_inicio, e.data_fim, e.carga_horaria_total, 
                   e.carga_horaria_cumprida, e.data_criacao
            FROM estagios e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN cursos c ON e.curso_id = c.id
            LEFT JOIN empresas em ON e.empresa_id = em.id
            LEFT JOIN usuarios o ON e.orientador_id = o.id
            WHERE (e.orientador_id = ? OR e.supervisor_id = ?) AND e.status != 'cancelado'
            ORDER BY e.data_criacao DESC";
}

// Aplicar filtros
if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    if ($usuario_perfil === 'admin') {
        $sql .= " AND e.status = '$status'";
    } else {
        $sql = str_replace("ORDER BY", "AND e.status = '$status' ORDER BY", $sql);
    }
}

if (!empty($_GET['busca'])) {
    $busca = '%' . $_GET['busca'] . '%';
    if ($usuario_perfil === 'admin') {
        $sql .= " AND (u.nome LIKE '$busca' OR u.cpf LIKE '$busca' OR em.nome LIKE '$busca')";
    } else {
        $sql = str_replace("ORDER BY", "AND (u.nome LIKE '$busca' OR u.cpf LIKE '$busca' OR em.nome LIKE '$busca') ORDER BY", $sql);
    }
}

// Executar query
if ($usuario_perfil === 'estagiario') {
    $resultado = executarQuery($conn, $sql, "i", [$usuario_id]);
} elseif ($usuario_perfil !== 'admin') {
    $resultado = executarQuery($conn, $sql, "ii", [$usuario_id, $usuario_id]);
} else {
    $resultado = executarQuery($conn, $sql);
}

if (!$resultado['sucesso']) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao buscar estágios'
    ]);
    exit;
}

$stmt = $resultado['stmt'];
$result = $stmt->get_result();
$estagios = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Retornar dados
http_response_code(200);
echo json_encode([
    'sucesso' => true,
    'total' => count($estagios),
    'dados' => $estagios
]);

?>
