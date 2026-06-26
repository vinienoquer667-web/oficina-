<?php
// =====================================================================
// config.php - Configuração de Conexão com Banco de Dados
// =====================================================================

// Variáveis de conexão
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'sge_db';
$db_port = 3306;

// Criar conexão
$conn = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);

// Verificar conexão
if ($conn->connect_error) {
    die(json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro de conexão com o banco de dados: ' . $conn->connect_error
    ]));
}

// Configurar charset para UTF-8
$conn->set_charset("utf8mb4");

// Definir fuso horário
date_default_timezone_set('America/Recife');

// Função para preparar e executar queries
function executarQuery($conn, $sql, $tipos = "", $parametros = array()) {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'sucesso' => false,
            'erro' => $conn->error
        ];
    }
    
    if (!empty($parametros) && !empty($tipos)) {
        $stmt->bind_param($tipos, ...$parametros);
    }
    
    if ($stmt->execute()) {
        return [
            'sucesso' => true,
            'stmt' => $stmt
        ];
    } else {
        return [
            'sucesso' => false,
            'erro' => $stmt->error
        ];
    }
}

// Função para registrar logs
function registrarLog($conn, $usuario_id, $acao, $tabela_afetada, $registro_id, $descricao = "") {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $sql = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, descricao, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $resultado = executarQuery($conn, $sql, "isisisss", [
        $usuario_id,
        $acao,
        $tabela_afetada,
        $registro_id,
        $descricao,
        $ip_address,
        $user_agent
    ]);
    
    return $resultado['sucesso'];
}

?>
