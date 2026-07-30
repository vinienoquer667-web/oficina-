<?php
// =====================================================================
// estagios.php - API para gerenciar estágios
// =====================================================================

require_once '../config.php';

// Inicializar sessão e verificar autenticação
$session = Session::getInstance();
$session->requireAuth();

$usuario_id = $session->getUserId();
$usuario_perfil = $session->getUserProfile();

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

// GET - Listar Estágios
if ($method === 'GET') {
    // Construir query base conforme perfil
    $params = [];
    $where_conditions = ["e.status != 'cancelado'"];

    if ($usuario_perfil === 'admin') {
        // Admin vê todos
        $where_conditions[] = "1=1";
    } elseif ($usuario_perfil === 'estagiario') {
        // Estagiário vê só seus estágios
        $where_conditions[] = "e.usuario_id = ?";
        $params[] = $usuario_id;
    } else {
        // Orientador/Supervisor vê estágios onde são responsáveis
        $where_conditions[] = "(e.orientador_id = ? OR e.supervisor_id = ?)";
        $params[] = $usuario_id;
        $params[] = $usuario_id;
    }

    // Aplicar filtros de forma segura (SQL injection protection)
    if (!empty($_GET['status'])) {
        $status = $_GET['status'];
        // Validar que o status é um valor válido
        $valid_statuses = ['abertura', 'em_andamento', 'concluido', 'cancelado'];
        if (in_array($status, $valid_statuses)) {
            $where_conditions[] = "e.status = ?";
            $params[] = $status;
        }
    }

    if (!empty($_GET['busca'])) {
        $busca = '%' . $_GET['busca'] . '%';
        $where_conditions[] = "(u.nome LIKE ? OR u.cpf LIKE ? OR em.nome LIKE ?)";
        $params[] = $busca;
        $params[] = $busca;
        $params[] = $busca;
    }

    // Construir SQL completo
    $where_clause = implode(' AND ', $where_conditions);

    $sql = "SELECT e.id, e.usuario_id, u.nome as aluno_nome, u.cpf, e.curso_id, c.nome as curso_nome, 
               e.empresa_id, em.nome as empresa_nome, e.orientador_id, o.nome as orientador_nome,
               e.supervisor_id, s.nome as supervisor_nome,
               e.tipo, e.status, e.data_inicio, e.data_fim, e.carga_horaria_total, 
               e.carga_horaria_cumprida, e.data_criacao
        FROM estagios e
        LEFT JOIN usuarios u ON e.usuario_id = u.id
        LEFT JOIN cursos c ON e.curso_id = c.id
        LEFT JOIN empresas em ON e.empresa_id = em.id
        LEFT JOIN usuarios o ON e.orientador_id = o.id
        LEFT JOIN usuarios s ON e.supervisor_id = s.id
        WHERE $where_clause
        ORDER BY e.data_criacao DESC";

    try {
        $estagios = $db->fetchAll($sql, $params);

        // Retornar dados
        http_response_code(200);
        echo json_encode([
            'sucesso' => true,
            'total' => count($estagios),
            'dados' => $estagios
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao buscar estágios: ' . $e->getMessage()
        ]);
    }
    exit;
}

// PUT - Atualizar estágio (apenas admin)
if ($method === 'PUT') {
    $session->requireProfile(['admin']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || empty($input['id'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID do estágio não fornecido'
        ]);
        exit;
    }
    
    if (!isset($input['status']) || !in_array($input['status'], ['em_andamento', 'cancelado'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Status inválido. Use em_andamento ou cancelado'
        ]);
        exit;
    }
    
    try {
        // Verificar se estágio existe e está em abertura
        $estagio = $db->fetchOne("SELECT * FROM estagios WHERE id = ?", [$input['id']]);
        if (!$estagio) {
            http_response_code(404);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Estágio não encontrado'
            ]);
            exit;
        }
        
        if ($estagio['status'] !== 'abertura') {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Este estágio já foi processado'
            ]);
            exit;
        }
        
        // Se aprovado, definir orientador e supervisor se fornecidos
        if ($input['status'] === 'em_andamento') {
            $sql = "UPDATE estagios SET status = ?, orientador_id = ?, supervisor_id = ?, data_inicio = CURDATE(), observacoes = ? WHERE id = ?";
            $params = [
                $input['status'],
                $input['orientador_id'] ?? null,
                $input['supervisor_id'] ?? null,
                $input['observacoes'] ?? null,
                $input['id']
            ];
        } else {
            $sql = "UPDATE estagios SET status = ?, observacoes = ? WHERE id = ?";
            $params = [
                $input['status'],
                $input['observacoes'] ?? null,
                $input['id']
            ];
        }
        
        $db->query($sql, $params);
        
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
    exit;
}

// Método não permitido
http_response_code(405);
echo json_encode([
    'sucesso' => false,
    'mensagem' => 'Método não permitido'
]);

