<?php
// =====================================================================
// config.php - Configuração do Sistema
// =====================================================================

// Carregar autoloader
require_once __DIR__ . '/includes/autoload.php';

// Definir fuso horário
date_default_timezone_set('America/Recife');

// Configurar headers padrão para JSON (apenas para requisições API)
$uri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($uri, '/api/') !== false) {
    header('Content-Type: application/json; charset=utf-8');
}

// Função para registrar logs (compatibilidade com código antigo)
function registrarLog($conn, $usuario_id, $acao, $tabela_afetada, $registro_id, $descricao = "") {
    // Esta função é mantida para compatibilidade, mas o uso da classe Auth é recomendado
    $db = Database::getInstance();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $sql = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $db->query($sql, [
            $usuario_id,
            $acao,
            $tabela_afetada,
            $registro_id,
            $descricao,
            $ip_address,
            $user_agent
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
        return false;
    }
}
?>
