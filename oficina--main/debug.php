<?php
// =====================================================================
// debug.php - Script de diagnóstico do sistema
// =====================================================================

echo "<h1>🔍 Diagnóstico do Sistema SGE</h1>";
echo "<hr>";

// 1. Verificar arquivos essenciais
echo "<h2>1. Verificando arquivos essenciais</h2>";
$arquivos = [
    'config.php' => 'Configuração principal',
    'includes/autoload.php' => 'Autoloader de classes',
    'config/database.php' => 'Configuração do banco',
    'classes/Database.php' => 'Classe Database',
    'classes/Auth.php' => 'Classe Auth',
    'classes/Session.php' => 'Classe Session'
];

foreach ($arquivos as $arquivo => $descricao) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        echo "<p>✅ $descricao: <strong>$arquivo</strong> existe</p>";
    } else {
        echo "<p>❌ $descricao: <strong>$arquivo</strong> NÃO existe</p>";
    }
}

// 2. Tentar carregar config
echo "<hr><h2>2. Carregando configuração</h2>";
try {
    require_once __DIR__ . '/config.php';
    echo "<p>✅ config.php carregado com sucesso</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar config.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 3. Verificar classes
echo "<hr><h2>3. Verificando classes</h2>";
$classes = ['Database', 'Auth', 'Session'];
foreach ($classes as $classe) {
    if (class_exists($classe)) {
        echo "<p>✅ Classe <strong>$classe</strong> existe</p>";
    } else {
        echo "<p>❌ Classe <strong>$classe</strong> NÃO existe</p>";
    }
}

// 4. Testar conexão com banco
echo "<hr><h2>4. Testando conexão com banco de dados</h2>";
try {
    $db = Database::getInstance();
    echo "<p>✅ Instância Database criada</p>";
    
    // Testar query simples
    $result = $db->fetchOne("SELECT 1 as teste");
    if ($result && $result['teste'] == 1) {
        echo "<p>✅ Query de teste executada com sucesso</p>";
    } else {
        echo "<p>⚠️ Query de teste retornou resultado inesperado</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao conectar com banco: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Solução:</strong> Execute <a href='instalar_banco.php'>instalar_banco.php</a> para criar o banco de dados</p>";
}

// 5. Verificar tabela usuarios
echo "<hr><h2>5. Verificando tabela usuarios</h2>";
try {
    $db = Database::getInstance();
    $result = $db->fetchOne("SHOW TABLES LIKE 'usuarios'");
    if ($result) {
        echo "<p>✅ Tabela 'usuarios' existe</p>";
        
        // Contar usuários
        $count = $db->fetchOne("SELECT COUNT(*) as total FROM usuarios");
        echo "<p>📊 Total de usuários: " . $count['total'] . "</p>";
    } else {
        echo "<p>❌ Tabela 'usuarios' NÃO existe</p>";
        echo "<p><strong>Solução:</strong> Execute <a href='instalar_banco.php'>instalar_banco.php</a> para criar as tabelas</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao verificar tabela: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 6. Verificar estrutura de diretórios
echo "<hr><h2>6. Verificando estrutura de diretórios</h2>";
$diretorios = [
    'views' => 'Páginas do sistema',
    'api' => 'Endpoints API',
    'classes' => 'Classes do sistema',
    'config' => 'Configurações',
    'includes' => 'Arquivos incluídos'
];

foreach ($diretorios as $dir => $descricao) {
    $caminho = __DIR__ . '/' . $dir;
    if (is_dir($caminho)) {
        echo "<p>✅ $descricao: <strong>$dir/</strong> existe</p>";
    } else {
        echo "<p>❌ $descricao: <strong>$dir/</strong> NÃO existe</p>";
    }
}

echo "<hr>";
echo "<h2>📋 Ações recomendadas</h2>";
echo "<ul>";
echo "<li>Se o banco não existir: <a href='instalar_banco.php'>Executar instalar_banco.php</a></li>";
echo "<li>Se houver erro de conexão: Verifique se o MySQL está rodando no XAMPP</li>";
echo "<li>Se houver erro de classes: Verifique se o autoloader está funcionando</li>";
echo "<li><a href='index.php'>Voltar para o sistema</a></li>";
echo "</ul>";
?>
