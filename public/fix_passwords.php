<?php
// Permitir a execução longa do script
set_time_limit(300);

// Incluir os arquivos necessários
require_once __DIR__ . '/../app/core/Database.php';

// Função para verificar se uma hash é válida
function isValidBcryptHash($hash) {
    // Verifica se começa com $2y$10$ (formato bcrypt)
    return strpos($hash, '$2y$10$') === 0 && strlen($hash) >= 60;
}

// Criar uma instância do banco de dados
$database = new Database();
$db = $database->connect();

// Criar arquivo de log
$log_file = __DIR__ . '/password_fix.log';
file_put_contents($log_file, date('c') . " | Iniciando correção de senhas\n", FILE_APPEND);

// Listar todos os usuários
$stmt = $db->query("SELECT id_usuario, email, password_hash FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$corrected = 0;
$already_valid = 0;
$errors = 0;

// Default passwords for admin accounts
$default_admin_passwords = [
    'admin@itaipu.com' => 'admin123',
    'admin2@itaipu.com' => 'admin456',
    'admin123@itaipu.com' => 'admin123',
    'admin.new@itaipu.com' => 'admin789',
];

// Other default password for non-admin accounts
$default_password = '123456';

echo "<h2>Corrigindo senhas de usuários</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Email</th><th>Hash Atual</th><th>Válido?</th><th>Ação</th></tr>";

foreach ($usuarios as $usuario) {
    $is_valid = isValidBcryptHash($usuario['password_hash']);
    $action = "";
    
    echo "<tr>";
    echo "<td>{$usuario['id_usuario']}</td>";
    echo "<td>{$usuario['email']}</td>";
    echo "<td>" . substr($usuario['password_hash'], 0, 20) . "...</td>";
    echo "<td>" . ($is_valid ? "Sim" : "Não") . "</td>";
    
    if (!$is_valid) {
        try {
            // Determinar a senha a usar
            $password = isset($default_admin_passwords[$usuario['email']]) 
                ? $default_admin_passwords[$usuario['email']] 
                : $default_password;
                
            // Gerar novo hash
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Atualizar no banco de dados
            $update_stmt = $db->prepare("UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?");
            $result = $update_stmt->execute([$new_hash, $usuario['id_usuario']]);
            
            if ($result) {
                $action = "Senha corrigida para: " . $password;
                $corrected++;
                file_put_contents($log_file, date('c') . " | CORRIGIDO | Usuário {$usuario['email']} (ID: {$usuario['id_usuario']}) - Senha: $password\n", FILE_APPEND);
            } else {
                $action = "Erro ao atualizar";
                $errors++;
                file_put_contents($log_file, date('c') . " | ERRO | Usuário {$usuario['email']} (ID: {$usuario['id_usuario']}) - Falha ao atualizar\n", FILE_APPEND);
            }
        } catch (Exception $e) {
            $action = "Erro: " . $e->getMessage();
            $errors++;
            file_put_contents($log_file, date('c') . " | EXCEPTION | Usuário {$usuario['email']} - " . $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        $action = "Hash já válido";
        $already_valid++;
        file_put_contents($log_file, date('c') . " | VÁLIDO | Usuário {$usuario['email']} - Hash já está no formato correto\n", FILE_APPEND);
    }
    
    echo "<td>$action</td>";
    echo "</tr>";
}

echo "</table>";
echo "<h3>Resumo:</h3>";
echo "<p>Usuários corrigidos: $corrected</p>";
echo "<p>Usuários já válidos: $already_valid</p>";
echo "<p>Erros: $errors</p>";
echo "<p>Total: " . count($usuarios) . "</p>";
echo "<p>Log detalhado salvo em: $log_file</p>";

echo "<p><a href='login.html'>Voltar para página de login</a></p>";

file_put_contents($log_file, date('c') . " | FIM | Correção de senhas finalizada. Corrigidos: $corrected, Já válidos: $already_valid, Erros: $errors\n", FILE_APPEND);
