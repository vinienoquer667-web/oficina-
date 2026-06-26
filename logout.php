<?php
// =====================================================================
// logout.php - Fazer Logout
// =====================================================================

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
session_start();

// Registrar logout
if (isset($_SESSION['usuario_id'])) {
    registrarLog($conn, $_SESSION['usuario_id'], 'LOGOUT', 'usuarios', $_SESSION['usuario_id'], '');
}

// Destruir sessão
session_destroy();

http_response_code(200);
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Logout realizado com sucesso'
]);

?>
