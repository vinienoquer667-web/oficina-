<?php
// =====================================================================
// excluir.php - Deletar Estágio
// =====================================================================

require_once '../config.php';

// Inicializar sessão e verificar autenticação
$session = Session::getInstance();
$session->requireAuth();

// Verificar permissão (apenas admin pode deletar)
$session->requireProfile(['admin']);

// Verificar método DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    // Tentar obter do GET
    $estagio_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$estagio_id) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'ID do estágio não fornecido'
        ]);
        exit;
    }
} else {
    $estagio_id = intval($input['id']);
}

$usuario_id = $session->getUserId();

// Inicializar Database
$db = Database::getInstance();

// Verificar se estágio existe
$sql_check = "SELECT status FROM estagios WHERE id = ?";
$estagio = $db->fetchOne($sql_check, [$estagio_id]);

if (!$estagio) {
    http_response_code(404);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Estágio não encontrado'
    ]);
    exit;
}

// Iniciar transação para deletar documentos também
$db->beginTransaction();

try {
    // Deletar documentos relacionados
    $sql_docs = "DELETE FROM documentos WHERE estagio_id = ?";
    $db->query($sql_docs, [$estagio_id]);
    
    // Deletar relatórios de horas
    $sql_relat = "DELETE FROM relatorio_horas WHERE estagio_id = ?";
    $db->query($sql_relat, [$estagio_id]);
    
    // Deletar atividades
    $sql_ativ = "DELETE FROM atividades WHERE estagio_id = ?";
    $db->query($sql_ativ, [$estagio_id]);
    
    // Deletar estágio
    $sql = "DELETE FROM estagios WHERE id = ?";
    $db->query($sql, [$estagio_id]);
    
    // Confirmar transação
    $db->commit();
    
    // Registrar log
    registrarLog(null, $usuario_id, 'DELETAR_ESTAGIO', 'estagios', $estagio_id, 'Estágio deletado');
    
    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Estágio deletado com sucesso'
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao deletar estágio: ' . $e->getMessage()
    ]);
}

?>
