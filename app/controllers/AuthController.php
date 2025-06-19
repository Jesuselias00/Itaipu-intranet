<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {    // Login de usuario
    public function login() {
        // Registrar inicio de la función login
        file_put_contents(__DIR__ . '/../core/router_debug.log', 
            date('c') . " | AuthController::login | Método iniciado\n", FILE_APPEND);
        
        $rawInput = file_get_contents('php://input');
        file_put_contents(__DIR__ . '/../core/router_debug.log', 
            date('c') . " | AuthController::login | Input recibido: " . $rawInput . "\n", FILE_APPEND);
        
        $data = json_decode($rawInput, true);
        
        file_put_contents(__DIR__ . '/../core/router_debug.log', 
            date('c') . " | AuthController::login | Datos decodificados: " . json_encode($data) . "\n", FILE_APPEND);
        
        if (!isset($data['email'], $data['senha'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email e senha são obrigatórios']);
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | AuthController::login | Error: Falta email o password\n", FILE_APPEND);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByEmail($data['email']);

        // Use 'password_hash' para corresponder ao esquema da tabela
        if (!$usuario || !password_verify($data['senha'], $usuario['password_hash'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Credenciais inválidas']);
            return;
        }

        // Verificar se o usuário está ativo
        if (!$usuario['activo']) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Conta de usuário desativada']);
            return;
        }

        // Iniciar sessão para gerenciamento de login (alternativa a JWT para simplicidade inicial)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_role'] = $usuario['rol'];

        echo json_encode([
            'status' => 'success', 
            'message' => 'Login bem-sucedido',
            'usuario' => [
                'id' => $usuario['id_usuario'], 
                'email' => $usuario['email'],
                'nome' => $usuario['nombre_completo'],
                'rol' => $usuario['rol']
            ]
        ]);
    }

    // Registro de nuevo usuario
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos necesarios
        if (!isset($data['email'], $data['senha'], $data['nombre_completo'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Dados incompletos para registro de usuário']);
            return;
        }
        
        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de email inválido']);
            return;
        }
        
        // Crear instancia del modelo Usuario
        $usuarioModel = new Usuario();
        
        // Verificar si el email ya existe
        if ($usuarioModel->findByEmail($data['email'])) {
            http_response_code(409); // Conflict
            echo json_encode(['status' => 'error', 'message' => 'Este email já está em uso']);
            return;
        }
        
        // Asignar valores
        $usuarioModel->email = $data['email'];
        $usuarioModel->password_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
        $usuarioModel->nombre_completo = $data['nombre_completo'];
        $usuarioModel->id_funcionario = $data['id_funcionario'] ?? null;
        $usuarioModel->rol = $data['rol'] ?? 'usuario';
        $usuarioModel->activo = 1;
        
        // Intentar crear el usuario
        if ($usuarioModel->create()) {
            http_response_code(201); // Created
            echo json_encode([
                'status' => 'success', 
                'message' => 'Usuário registrado com sucesso',
                'id_usuario' => $usuarioModel->id_usuario
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar usuário']);
        }
    }

    // Logout de usuário
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpar todas as variáveis de sessão
        $_SESSION = array();

        // Destruir a sessão.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        echo json_encode(['status' => 'success', 'message' => 'Logout bem-sucedido']);
    }
    
    // Obtener todos los usuarios (para administradores)
    public function getAllUsers() {
        // Verificar sessão e permissão (ideal implementar middleware)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Permissão negada']);
            return;
        }
        
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->getAll();
        
        echo json_encode([
            'status' => 'success',
            'data' => $usuarios
        ]);
    }
}
