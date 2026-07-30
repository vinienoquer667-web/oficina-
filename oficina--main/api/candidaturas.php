<?php
// =====================================================================
// candidaturas.php - API para gerenciar candidaturas a vagas
// =====================================================================

require_once '../config.php';

// Verificar autenticação
$session = Session::getInstance();
$session->requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();
$user_id = $session->getUserId();
$user_perfil = $session->getUserProfile();

// GET - Listar candidaturas
if ($method === 'GET') {
    // Aluno vê suas candidaturas
    if ($user_perfil === 'estagiario') {
        $sql = "SELECT c.*, v.titulo as vaga_titulo, v.area as vaga_area, e.nome as empresa_nome,
                o.nome as orientador_nome, s.nome as supervisor_nome
                FROM candidaturas_vagas c
                LEFT JOIN vagas_estagio v ON c.vaga_id = v.id
                LEFT JOIN empresas e ON v.empresa_id = e.id
                LEFT JOIN usuarios o ON c.orientador_id = o.id
                LEFT JOIN usuarios s ON c.supervisor_id = s.id
                WHERE c.usuario_id = ?
                ORDER BY c.data_candidatura DESC";
        
        $candidaturas = $db->fetchAll($sql, [$user_id]);
        
        echo json_encode([
            'sucesso' => true,
            'candidaturas' => $candidaturas
        ]);
        exit;
    }
    
    // Admin e professores veem todas as candidaturas pendentes
    if (in_array($user_perfil, ['admin', 'orientador', 'supervisor'])) {
        $status = $_GET['status'] ?? 'pendente';
        
        $sql = "SELECT c.*, v.titulo as vaga_titulo, v.area as vaga_area, e.nome as empresa_nome,
                u.nome as usuario_nome, u.email as usuario_email,
                o.nome as orientador_nome, s.nome as supervisor_nome
                FROM candidaturas_vagas c
                LEFT JOIN vagas_estagio v ON c.vaga_id = v.id
                LEFT JOIN empresas e ON v.empresa_id = e.id
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN usuarios o ON c.orientador_id = o.id
                LEFT JOIN usuarios s ON c.supervisor_id = s.id
                WHERE c.status = ?
                ORDER BY c.data_candidatura DESC";
        
        $candidaturas = $db->fetchAll($sql, [$status]);
        
        echo json_encode([
            'sucesso' => true,
            'candidaturas' => $candidaturas
        ]);
        exit;
    }
}

// POST - Criar nova candidatura (apenas aluno)
if ($method === 'POST') {
    $session->requireProfile(['estagiario']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['vaga_id']) || empty($input['vaga_id'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID da vaga não fornecido'
        ]);
        exit;
    }
    
    // Verificar se vaga existe e está aberta
    $vaga = $db->fetchOne("SELECT id, status, vagas_disponiveis FROM vagas_estagio WHERE id = ?", [$input['vaga_id']]);
    if (!$vaga) {
        http_response_code(404);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Vaga não encontrada'
        ]);
        exit;
    }
    
    if ($vaga['status'] !== 'aberta') {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Esta vaga não está mais aberta'
        ]);
        exit;
    }
    
    if ($vaga['vagas_disponiveis'] <= 0) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Não há vagas disponíveis'
        ]);
        exit;
    }
    
    // Verificar se já se candidatou
    $candidatura_existente = $db->fetchOne(
        "SELECT id FROM candidaturas_vagas WHERE vaga_id = ? AND usuario_id = ?",
        [$input['vaga_id'], $user_id]
    );
    
    if ($candidatura_existente) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Você já se candidatou a esta vaga'
        ]);
        exit;
    }
    
    try {
        $sql = "INSERT INTO candidaturas_vagas (vaga_id, usuario_id, status) VALUES (?, ?, 'pendente')";
        $db->query($sql, [$input['vaga_id'], $user_id]);
        
        http_response_code(201);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Candidatura realizada com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao realizar candidatura: ' . $e->getMessage()
        ]);
    }
    exit;
}

// PUT - Aprovar/Rejeitar candidatura (apenas admin)
if ($method === 'PUT') {
    $session->requireProfile(['admin']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || empty($input['id'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID da candidatura não fornecido'
        ]);
        exit;
    }
    
    if (!isset($input['status']) || !in_array($input['status'], ['aprovada', 'rejeitada', 'cancelada'])) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Status inválido'
        ]);
        exit;
    }
    
    // Se aprovada, deve ter orientador e supervisor
    if ($input['status'] === 'aprovada') {
        if (!isset($input['orientador_id']) || !isset($input['supervisor_id'])) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Para aprovar, deve selecionar orientador e supervisor'
            ]);
            exit;
        }
        
        // Verificar se orientador existe e tem perfil correto
        $orientador = $db->fetchOne("SELECT id, perfil FROM usuarios WHERE id = ?", [$input['orientador_id']]);
        if (!$orientador || !in_array($orientador['perfil'], ['orientador', 'admin'])) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Orientador inválido'
            ]);
            exit;
        }
        
        // Verificar se supervisor existe e tem perfil correto
        $supervisor = $db->fetchOne("SELECT id, perfil FROM usuarios WHERE id = ?", [$input['supervisor_id']]);
        if (!$supervisor || !in_array($supervisor['perfil'], ['supervisor', 'admin'])) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Supervisor inválido'
            ]);
            exit;
        }
    }
    
    try {
        $candidatura = $db->fetchOne("SELECT * FROM candidaturas_vagas WHERE id = ?", [$input['id']]);
        if (!$candidatura) {
            http_response_code(404);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Candidatura não encontrada'
            ]);
            exit;
        }
        
        if ($input['status'] === 'aprovada') {
            $sql = "UPDATE candidaturas_vagas SET status = ?, orientador_id = ?, supervisor_id = ?, data_aprovacao = NOW(), observacoes = ? WHERE id = ?";
            $params = [$input['status'], $input['orientador_id'], $input['supervisor_id'], $input['observacoes'] ?? null, $input['id']];
            
            // Criar estágio automaticamente
            $vaga = $db->fetchOne("SELECT * FROM vagas_estagio WHERE id = ?", [$candidatura['vaga_id']]);
            
            $sql_estagio = "INSERT INTO estagios (usuario_id, curso_id, empresa_id, orientador_id, supervisor_id, tipo, status, carga_horaria_total, descricao) 
                           VALUES (?, 1, ?, ?, ?, 'obrigatorio', 'abertura', ?, ?)";
            $db->query($sql_estagio, [
                $candidatura['usuario_id'],
                $vaga['empresa_id'],
                $input['orientador_id'],
                $input['supervisor_id'],
                $vaga['carga_horaria'],
                $vaga['descricao']
            ]);
            
            // Atualizar vagas disponíveis
            $db->query("UPDATE vagas_estagio SET vagas_disponiveis = vagas_disponiveis - 1 WHERE id = ?", [$candidatura['vaga_id']]);
            
        } else {
            $sql = "UPDATE candidaturas_vagas SET status = ?, observacoes = ? WHERE id = ?";
            $params = [$input['status'], $input['observacoes'] ?? null, $input['id']];
        }
        
        $db->query($sql, $params);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Candidatura atualizada com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao atualizar candidatura: ' . $e->getMessage()
        ]);
    }
    exit;
}

// DELETE - Cancelar candidatura (aluno pode cancelar a própria)
if ($method === 'DELETE') {
    $candidatura_id = $_GET['id'] ?? null;
    
    if (!$candidatura_id) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID da candidatura não fornecido'
        ]);
        exit;
    }
    
    // Verificar se é o dono da candidatura ou admin
    if ($user_perfil !== 'admin') {
        $candidatura = $db->fetchOne("SELECT usuario_id FROM candidaturas_vagas WHERE id = ?", [$candidatura_id]);
        if (!$candidatura || $candidatura['usuario_id'] != $user_id) {
            http_response_code(403);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para cancelar esta candidatura'
            ]);
            exit;
        }
    }
    
    try {
        $db->query("DELETE FROM candidaturas_vagas WHERE id = ?", [$candidatura_id]);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Candidatura cancelada com sucesso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao cancelar candidatura: ' . $e->getMessage()
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
