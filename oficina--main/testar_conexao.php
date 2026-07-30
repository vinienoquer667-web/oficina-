<?php
// =====================================================================
// testar_conexao.php - Script para testar conexão com o banco de dados
// =====================================================================

try {
    // Configurações do banco
    $host = 'localhost';
    $port = 3306;
    $database = 'sge_db';
    $username = 'root';
    $password = '';
    
    // Testar conexão sem especificar o banco (para criar se não existir)
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h2>✅ Conexão com MySQL estabelecida com sucesso!</h2>";
    
    // Verificar se o banco existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $db_exists = $stmt->fetch();
    
    if ($db_exists) {
        echo "<p>✅ Banco de dados '$database' já existe.</p>";
        
        // Conectar ao banco específico
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Verificar se a tabela usuarios existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
        $table_exists = $stmt->fetch();
        
        if ($table_exists) {
            echo "<p>✅ Tabela 'usuarios' existe.</p>";
            
            // Contar usuários
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
            $result = $stmt->fetch();
            echo "<p>📊 Total de usuários cadastrados: " . $result['total'] . "</p>";
            
            // Listar usuários
            $stmt = $pdo->query("SELECT id, nome, email, perfil, ativo FROM usuarios LIMIT 10");
            $usuarios = $stmt->fetchAll();
            
            if (!empty($usuarios)) {
                echo "<h3>Usuários cadastrados:</h3>";
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Perfil</th><th>Ativo</th></tr>";
                foreach ($usuarios as $usuario) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($usuario['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['nome']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['perfil']) . "</td>";
                    echo "<td>" . ($usuario['ativo'] ? 'Sim' : 'Não') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p>⚠️ Tabela 'usuarios' NÃO existe. Execute o schema.sql para criar as tabelas.</p>";
        }
    } else {
        echo "<p>⚠️ Banco de dados '$database' NÃO existe.</p>";
        echo "<p>📝 Para criar o banco, execute o seguinte SQL:</p>";
        echo "<pre>CREATE DATABASE sge_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>";
    }
    
    echo "<hr>";
    echo "<p><a href='index.php'>Voltar para o sistema</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro na conexão com o banco de dados</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Código:</strong> " . htmlspecialchars($e->getCode()) . "</p>";
    
    echo "<hr>";
    echo "<h3>Soluções possíveis:</h3>";
    echo "<ul>";
    echo "<li>Verifique se o MySQL está rodando no XAMPP</li>";
    echo "<li>Verifique se as credenciais estão corretas (root sem senha)</li>";
    echo "<li>Verifique se a porta 3306 está disponível</li>";
    echo "</ul>";
}
?>
