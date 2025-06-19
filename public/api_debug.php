<?php
// api_debug.php
// Este script mostra informações detalhadas sobre as requisições API e roteamento

// Habilitar relatório de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mostrar cabeçalho
echo "<!DOCTYPE html>
<html>
<head>
    <title>API Debug Tool</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            margin: 20px;
        }
        h1 { color: #2c3e50; }
        h2 { 
            color: #3498db; 
            margin-top: 30px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        pre { 
            background: #f8f9fa; 
            padding: 10px; 
            border-radius: 4px; 
            overflow-x: auto;
        }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        table { 
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td { 
            text-align: left; 
            padding: 8px; 
            border-bottom: 1px solid #ddd;
        }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <h1>API Debug Tool</h1>";

// 1. Informação do Servidor
echo "<h2>1. Informações do Servidor</h2>";
echo "<table>";
echo "<tr><th>Variável</th><th>Valor</th></tr>";
$serverInfo = [
    'PHP Version' => phpversion(),
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'N/A',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A'
];

foreach ($serverInfo as $key => $value) {
    echo "<tr><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
}
echo "</table>";

// 2. Teste de Conexão do Banco de Dados
echo "<h2>2. Teste de Conexão do Banco de Dados</h2>";
try {
    require_once __DIR__ . '/../app/core/Database.php';
    $database = new Database();
    $db = $database->connect();
    echo "<p class='success'>✓ Conexão com o banco de dados estabelecida com sucesso.</p>";
    
    echo "<h3>Tabelas no Banco de Dados:</h3>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<table>";
    echo "<tr><th>#</th><th>Nome da Tabela</th></tr>";
    foreach ($tables as $index => $table) {
        echo "<tr><td>".($index+1)."</td><td>{$table}</td></tr>";
    }
    echo "</table>";
    
    // Verificar tabela funcionarios
    if (in_array('funcionarios', $tables)) {
        echo "<h3>Estrutura da tabela 'funcionarios':</h3>";
        $stmt = $db->query("DESCRIBE funcionarios");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . ($value ?? "NULL") . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar e mostrar alguns funcionários
        $stmt = $db->query("SELECT COUNT(*) as total FROM funcionarios");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p>Total de funcionários: {$count}</p>";
        
        if ($count > 0) {
            $stmt = $db->query("SELECT * FROM funcionarios LIMIT 5");
            $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Amostra de funcionários (primeiros 5):</h3>";
            echo "<table>";
            echo "<tr>";
            foreach (array_keys($funcionarios[0]) as $header) {
                echo "<th>{$header}</th>";
            }
            echo "</tr>";
            
            foreach ($funcionarios as $func) {
                echo "<tr>";
                foreach ($func as $value) {
                    if (is_resource($value) || (is_string($value) && strlen($value) > 100)) {
                        echo "<td>[DADOS BINÁRIOS]</td>";
                    } else {
                        echo "<td>" . (is_null($value) ? "NULL" : htmlspecialchars($value)) . "</td>";
                    }
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
}

// 3. Teste de Router e Funcionamento da API
echo "<h2>3. Inspeção do Router</h2>";
try {
    require_once __DIR__ . '/../app/core/Router.php';
    echo "<p class='success'>✓ Classe Router carregada com sucesso.</p>";
    
    // Verificar arquivo de log do Router
    $logFile = __DIR__ . '/../app/core/router_debug.log';
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $logSize = filesize($logFile);
        
        echo "<p>Arquivo de log do Router: {$logFile}</p>";
        echo "<p>Tamanho do log: " . round($logSize / 1024, 2) . " KB</p>";
        
        if ($logSize > 0) {
            $logLines = array_filter(explode("\n", $logContent));
            $lastLines = array_slice($logLines, -20); // Últimas 20 linhas
            
            echo "<h3>Últimas 20 entradas do log do Router:</h3>";
            echo "<pre>";
            foreach ($lastLines as $line) {
                echo htmlspecialchars($line) . "\n";
            }
            echo "</pre>";
        } else {
            echo "<p class='warning'>⚠ O arquivo de log do Router está vazio.</p>";
        }
    } else {
        echo "<p class='error'>✗ Arquivo de log do Router não encontrado: {$logFile}</p>";
    }
    
    // Criar um novo arquivo de log para uso desta página
    $debugLogFile = __DIR__ . '/../app/core/api_debug_test.log';
    file_put_contents($debugLogFile, date('c') . " | API Debug Test iniciado\n");
    
    // Criar uma instância do Router para teste
    $router = new Router();
    
    // Registro manual de algumas rotas para teste
    $router->get('test', function() { echo "Test route"; });
    $router->get('funcionarios', 'FuncionarioController@index');
    
    // Mostrar rotas registradas
    $registeredRoutes = $router->getRegisteredRoutes();
    
    echo "<h3>Rotas registradas nesta instância de teste:</h3>";
    echo "<table>";
    echo "<tr><th>Método</th><th>URI</th></tr>";
    foreach ($registeredRoutes as $route) {
        echo "<tr><td>GET</td><td>{$route}</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Erro ao inspecionar o Router: " . $e->getMessage() . "</p>";
}

// 4. Análise das Chamadas API
echo "<h2>4. Testes de Chamadas API</h2>";

// Definir endpoints para teste
$endpoints = [
    '/api/funcionarios',
    '/Itaipu-intranet/api/funcionarios',
    '/funcionarios'
];

echo "<div id='api-test-results'>";
echo "<p>Clique no botão abaixo para testar os endpoints API:</p>";
echo "</div>";

echo "<button onclick='testEndpoints()'>Testar Endpoints API</button>";

// JavaScript para fazer chamadas AJAX
echo "<script>
async function testEndpoint(url) {
    const resultDiv = document.getElementById('api-test-results');
    resultDiv.innerHTML += `<p>Testando: <strong>${url}</strong>...</p>`;
    
    try {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const contentType = response.headers.get('content-type');
        let resultHtml = '';
        
        if (response.ok) {
            resultHtml += `<p class='success'>✓ Status: ${response.status} ${response.statusText}</p>`;
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                resultHtml += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
            } else {
                const text = await response.text();
                resultHtml += `<p class='warning'>⚠ Resposta não é JSON. Content-Type: ${contentType || 'não especificado'}</p>`;
                resultHtml += `<pre>${text.substring(0, 500)}${text.length > 500 ? '...' : ''}</pre>`;
            }
        } else {
            resultHtml += `<p class='error'>✗ Erro HTTP: ${response.status} ${response.statusText}</p>`;
            
            try {
                const text = await response.text();
                resultHtml += `<pre>${text.substring(0, 500)}${text.length > 500 ? '...' : ''}</pre>`;
            } catch(e) {
                resultHtml += `<p>Não foi possível ler o corpo da resposta: ${e.message}</p>`;
            }
        }
        
        resultDiv.innerHTML += resultHtml;
    } catch (error) {
        resultDiv.innerHTML += `<p class='error'>✗ Erro ao fazer requisição: ${error.message}</p>`;
    }
}

async function testEndpoints() {
    const endpoints = [
        '/api/funcionarios',
        '/Itaipu-intranet/api/funcionarios',
        '/funcionarios'
    ];
    
    document.getElementById('api-test-results').innerHTML = '<h3>Resultados dos testes:</h3>';
    
    for (const endpoint of endpoints) {
        await testEndpoint(endpoint);
    }
}
</script>";

echo "</body></html>";
