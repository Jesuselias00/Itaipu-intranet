<?php
// test_db_connection.php
// Script simples para testar a conexão com o banco de dados

require_once __DIR__ . '/app/core/Database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    echo "<h1>Teste de Conexão com o Banco de Dados</h1>";
    
    if ($conn instanceof PDO) {
        echo "<p style='color:green;'>✓ Conexão estabelecida com sucesso!</p>";
        
        // Verificar tabelas
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p>Tabelas encontradas no banco de dados:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
            
            // Verificar se existem departamentos
            $deptCount = $conn->query("SELECT COUNT(*) FROM departamentos")->fetchColumn();
            echo "<p>Total de departamentos: $deptCount</p>";
            
            // Verificar se existem usuários
            $userCount = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
            echo "<p>Total de usuários: $userCount</p>";
            
            // Verificar se existem funcionários
            $empCount = $conn->query("SELECT COUNT(*) FROM funcionarios")->fetchColumn();
            echo "<p>Total de funcionários: $empCount</p>";
            
        } else {
            echo "<p style='color:orange;'>⚠️ Banco de dados vazio. Execute os scripts SQL para criar as tabelas.</p>";
        }
    }
} catch (PDOException $e) {
    echo "<h1 style='color:red;'>Erro de Conexão</h1>";
    echo "<p>Não foi possível conectar ao banco de dados:</p>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    
    echo "<h2>Verifique:</h2>";
    echo "<ol>";
    echo "<li>O serviço MySQL/MariaDB está rodando?</li>";
    echo "<li>O banco de dados 'intranet_itaipu' existe?</li>";
    echo "<li>As credenciais em app/config/database.php estão corretas?</li>";
    echo "</ol>";
}
?>
