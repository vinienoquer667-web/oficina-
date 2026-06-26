<?php
// =====================================================================
// excluir.php - Deletar Estágio
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

// Verificar permissão (apenas admin pode deletar)
$usuario_perfil = $_SESSION['usuario_perfil'];
if ($usuario_perfil !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Permissão negada. Apenas administrador pode deletar.'
    ]);
    exit;
}

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

$usuario_id = $_SESSION['usuario_id'];

// Verificar se estágio existe
$sql_check = "SELECT status FROM estagios WHERE id = ?";
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
$stmt->bind_result($status);
$existe = $stmt->fetch();
$stmt->close();

if (!$existe) {
    http_response_code(404);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Estágio não encontrado'
    ]);
    exit;
}

// Iniciar transação para deletar documentos também
$conn->begin_transaction();

try {
    // Deletar documentos relacionados
    $sql_docs = "DELETE FROM documentos WHERE estagio_id = ?";
    $res_docs = executarQuery($conn, $sql_docs, "i", [$estagio_id]);
    
    if (!$res_docs['sucesso']) {
        throw new Exception('Erro ao deletar documentos');
    }
    
    // Deletar relatórios de horas
    $sql_relat = "DELETE FROM relatorio_horas WHERE estagio_id = ?";
    $res_relat = executarQuery($conn, $sql_relat, "i", [$estagio_id]);
    
    if (!$res_relat['sucesso']) {
        throw new Exception('Erro ao deletar relatórios');
    }
    
    // Deletar atividades
    $sql_ativ = "DELETE FROM atividades WHERE estagio_id = ?";
    $res_ativ = executarQuery($conn, $sql_ativ, "i", [$estagio_id]);
    
    if (!$res_ativ['sucesso']) {
        throw new Exception('Erro ao deletar atividades');
    }
    
    // Deletar estágio
    $sql = "DELETE FROM estagios WHERE id = ?";
    $resultado = executarQuery($conn, $sql, "i", [$estagio_id]);
    
    if (!$resultado['sucesso']) {
        throw new Exception('Erro ao deletar estágio');
    }
    
    // Confirmar transação
    $conn->commit();
    
    // Registrar log
    registrarLog($conn, $usuario_id, 'DELETAR_ESTAGIO', 'estagios', $estagio_id, 'Estágio deletado');
    
    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Estágio deletado com sucesso'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao deletar estágio: ' . $e->getMessage()
    ]);
}

?>
