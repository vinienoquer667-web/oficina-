<?php
// =====================================================================
// instalar_banco.php - Script para criar banco de dados e tabelas automaticamente
// =====================================================================

try {
    // Configurações do banco
    $host = 'localhost';
    $port = 3306;
    $database = 'sge_db';
    $username = 'root';
    $password = '';
    
    echo "<h1>🔧 Instalação do Banco de Dados SGE</h1>";
    echo "<hr>";
    
    // Conectar ao MySQL sem especificar banco
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ Conectado ao MySQL com sucesso!</p>";
    
    // Criar banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Banco de dados '$database' criado/verificado!</p>";
    
    // Conectar ao banco específico
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ Conectado ao banco '$database'!</p>";
    
    // Ler e executar schema.sql
    $schema_file = __DIR__ . '/schema.sql';
    
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        
        // Remover a parte de CREATE DATABASE e USE pois já estamos conectados
        $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s', '', $sql);
        $sql = preg_replace('/USE sge_db;/', '', $sql);
        
        // Executar cada comando separadamente
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Ignorar erros de tabela já existente
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "<p>⚠️ Aviso: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            }
        }
        
        echo "<p>✅ Tabelas criadas com sucesso!</p>";
    } else {
        echo "<p>⚠️ Arquivo schema.sql não encontrado!</p>";
    }
    
    // Verificar usuários criados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "<p>📊 Total de usuários: " . $result['total'] . "</p>";
    
    // Listar usuários de teste
    $stmt = $pdo->query("SELECT nome, email, perfil FROM usuarios");
    $usuarios = $stmt->fetchAll();
    
    if (!empty($usuarios)) {
        echo "<h3>Usuários de teste criados:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Senha</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($usuario['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['email']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['perfil']) . "</td>";
            echo "<td>senha123</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Instalação concluída com sucesso!</h2>";
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ul>";
    echo "<li><a href='index.php'>Acessar o sistema</a></li>";
    echo "<li><a href='testar_conexao.php'>Testar conexão novamente</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro durante a instalação</h2>";
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
