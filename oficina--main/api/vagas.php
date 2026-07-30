<?php
// =====================================================================
// vagas.php - API para gerenciar vagas de estágio
// =====================================================================

require_once '../config.php';

// Verificar autenticação
$session = Session::getInstance();
$session->requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

// GET - Listar vagas
if ($method === 'GET') {
    $area = $_GET['area'] ?? null;
    $status = $_GET['status'] ?? 'aberta';
    
    $sql = "SELECT v.*, e.nome as empresa_nome, u.nome as criado_por_nome 
            FROM vagas_estagio v 
            LEFT JOIN empresas e ON v.empresa_id = e.id 
            LEFT JOIN usuarios u ON v.criado_por_id = u.id 
            WHERE v.status = ?";
    
    $params = [$status];
    
    if ($area && in_array($area, ['informatica', 'agro'])) {
        $sql .= " AND v.area = ?";
        $params[] = $area;
    }
    
    $sql .= " ORDER BY v.data_publicacao DESC";
    
    $vagas = $db->fetchAll($sql, $params);
    
    echo json_encode([
        'sucesso' => true,
        'vagas' => $vagas
    ]);
    exit;
}

// POST - Criar nova vaga (apenas admin)
if ($method === 'POST') {
    $session->requireProfile(['admin']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos obrigatórios
    $campos_obrigatorios = ['titulo', 'descricao', 'area', 'empresa_id'];
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
    
    // Validar área
    if (!in_array($input['area'], ['informatica', 'agro'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Área inválida. Deve ser informatica ou agro'
        ]);
        exit;
    }
    
    // Validar empresa
    $empresa = $db->fetchOne("SELECT id FROM empresas WHERE id = ?", [$input['empresa_id']]);
    if (!$empresa) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Empresa não encontrada'
        ]);
        exit;
    }
    
    try {
        $sql = "INSERT INTO vagas_estagio (titulo, descricao, area, empresa_id, carga_horaria, remuneracao, requisitos, beneficios, vagas_disponiveis, data_encerramento, criado_por_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $input['titulo'],
            $input['descricao'],
            $input['area'],
            $input['empresa_id'],
            $input['carga_horaria'] ?? 400,
            $input['remuneracao'] ?? null,
            $input['requisitos'] ?? null,
            $input['beneficios'] ?? null,
            $input['vagas_disponiveis'] ?? 1,
            $input['data_encerramento'] ?? null,
            $session->getUserId()
        ];
        
        $db->query($sql, $params);
        
        http_response_code(201);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Vaga criada com sucesso',
            'vaga_id' => $db->lastInsertId()
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao criar vaga: ' . $e->getMessage()
        ]);
    }
    exit;
}

// PUT - Atualizar vaga (apenas admin)
if ($method === 'PUT') {
    $session->requireProfile(['admin']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || empty($input['id'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID da vaga não fornecido'
        ]);
        exit;
    }
    
    // Verificar se vaga existe
    $vaga = $db->fetchOne("SELECT id FROM vagas_estagio WHERE id = ?", [$input['id']]);
    if (!$vaga) {
        http_response_code(404);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Vaga não encontrada'
        ]);
        exit;
    }
    
    try {
        $set = [];
        $params = [];
        
        $campos_permitidos = ['titulo', 'descricao', 'area', 'empresa_id', 'carga_horaria', 'remuneracao', 'requisitos', 'beneficios', 'vagas_disponiveis', 'data_encerramento', 'status'];
        
        foreach ($campos_permitidos as $campo) {
            if (isset($input[$campo])) {
                $set[] = "$campo = ?";
                $params[] = $input[$campo];
            }
        }
        
        if (empty($set)) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Nenhum campo para atualizar'
            ]);
            exit;
        }
        
        $params[] = $input['id'];
        
        $sql = "UPDATE vagas_estagio SET " . implode(', ', $set) . " WHERE id = ?";
        $db->query($sql, $params);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Vaga atualizada com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao atualizar vaga: ' . $e->getMessage()
        ]);
    }
    exit;
}

// DELETE - Deletar vaga (apenas admin)
if ($method === 'DELETE') {
    $session->requireProfile(['admin']);
    
    $vaga_id = $_GET['id'] ?? null;
    
    if (!$vaga_id) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID da vaga não fornecido'
        ]);
        exit;
    }
    
    try {
        $db->query("DELETE FROM vagas_estagio WHERE id = ?", [$vaga_id]);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Vaga deletada com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao deletar vaga: ' . $e->getMessage()
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
