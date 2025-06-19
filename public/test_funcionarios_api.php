<?php
// test_funcionarios_api.php - Script para testar a conexão com o banco de dados e a API de funcionários

// Habilitar exibição de erros para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste da API de Funcionários</h1>";

// Testar conexão direta ao banco de dados
require_once __DIR__ . '/../app/core/Database.php';

echo "<h2>1. Testando conexão com o banco de dados:</h2>";
try {
    $database = new Database();
    $db = $database->connect();
    echo "<p style='color: green;'>✓ Conexão com o banco de dados estabelecida com sucesso.</p>";
    
    // Verificar se a tabela funcionarios existe
    $stmt = $db->query("SHOW TABLES LIKE 'funcionarios'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Tabela 'funcionarios' encontrada.</p>";
        
        // Contar registros na tabela
        $stmt = $db->query("SELECT COUNT(*) as total FROM funcionarios");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p>Total de funcionários no banco de dados: {$count}</p>";
        
        // Listar primeiros 5 funcionários
        $stmt = $db->query("SELECT id_funcionario, nome, sobrenome, email FROM funcionarios LIMIT 5");
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($funcionarios) > 0) {
            echo "<p>Primeiros 5 funcionários:</p>";
            echo "<ul>";
            foreach ($funcionarios as $func) {
                echo "<li>ID: {$func['id_funcionario']} - Nome: {$func['nome']} {$func['sobrenome']} - Email: {$func['email']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠ A tabela 'funcionarios' está vazia.</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Tabela 'funcionarios' não encontrada!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
}

// Testar chamada direta ao FuncionarioController
echo "<h2>2. Testando FuncionarioController diretamente:</h2>";
try {
    require_once __DIR__ . '/../app/controllers/FuncionarioController.php';
    
    echo "<p>Criando instância do FuncionarioController...</p>";
    $controller = new FuncionarioController();
    echo "<p style='color: green;'>✓ FuncionarioController instanciado com sucesso.</p>";
    
    echo "<p>Chamando método index() do FuncionarioController...</p>";
    // Capturar a saída do controlador
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    echo "<p>Resposta do controller:</p>";
    echo "<pre style='background-color: #f0f0f0; padding: 10px; overflow: auto; max-height: 300px;'>";
    // Tenta formatar o JSON para facilitar a leitura
    $jsonOutput = json_decode($output);
    if ($jsonOutput !== null) {
        echo json_encode($jsonOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo htmlspecialchars($output);
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro ao testar o FuncionarioController: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

// Testar chamada à API via curl
echo "<h2>3. Testando API endpoint via cURL:</h2>";
try {
    $api_url = 'http://localhost/api/funcionarios';
    if (strpos($_SERVER['REQUEST_URI'], 'Itaipu-intranet') !== false) {
        $api_url = 'http://localhost/Itaipu-intranet/api/funcionarios';
    }
    
    echo "<p>Chamando API URL: {$api_url}</p>";
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p>Código HTTP: {$httpCode}</p>";
    if ($httpCode == 200) {
        echo "<p style='color: green;'>✓ API respondeu com sucesso.</p>";
    } else {
        echo "<p style='color: red;'>✗ API retornou um código de erro: {$httpCode}</p>";
    }
    
    echo "<p>Resposta da API:</p>";
    echo "<pre style='background-color: #f0f0f0; padding: 10px; overflow: auto; max-height: 300px;'>";
    // Tenta formatar o JSON para facilitar a leitura
    $jsonResponse = json_decode($response);
    if ($jsonResponse !== null) {
        echo json_encode($jsonResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo htmlspecialchars($response);
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro ao testar a API via cURL: " . $e->getMessage() . "</p>";
}
