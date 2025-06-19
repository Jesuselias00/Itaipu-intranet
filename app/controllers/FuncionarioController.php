<?php
// app/controllers/FuncionarioController.php

require_once __DIR__ . '/../models/Funcionario.php';
require_once __DIR__ . '/../core/Database.php';

class FuncionarioController {
    private $funcionarioModel;

    public function __construct() {
        try {
            $database = new Database();
            $db = $database->connect();
            $this->funcionarioModel = new Funcionario($db);
            
            // Registrar inicialização exitosa
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | FuncionarioController::__construct | Inicializado com sucesso\n", FILE_APPEND);
        } catch (Exception $e) {
            // Registrar erro de inicialização
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | FuncionarioController::__construct | ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
            
            // Garantir que a resposta seja JSON
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro ao inicializar o controlador: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function index() {
        try {
            file_put_contents(__DIR__ . '/../core/router_debug.log',
                date('c') . " | FuncionarioController::index | INICIO\n", FILE_APPEND);
            header('Content-Type: application/json');

            file_put_contents(__DIR__ . '/../core/router_debug.log',
                date('c') . " | FuncionarioController::index | Antes de getAll\n", FILE_APPEND);
            $funcionarios = $this->funcionarioModel->getAll();
            file_put_contents(__DIR__ . '/../core/router_debug.log',
                date('c') . " | FuncionarioController::index | Después de getAll\n", FILE_APPEND);

            echo json_encode(['status' => 'success', 'data' => $funcionarios]);
            file_put_contents(__DIR__ . '/../core/router_debug.log',
                date('c') . " | FuncionarioController::index | FIN OK\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../core/router_debug.log',
                date('c') . " | FuncionarioController::index | ERROR: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Não foi possível listar funcionários: ' . $e->getMessage()
            ]);
        }
    }
    
    // Nuevo método para criar um funcionário
    public function store() {
        // Detectar si es multipart/form-data o JSON
        $isMultipart = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false);
        if ($isMultipart) {
            $data = $_POST;
        } else {
            $data = json_decode(file_get_contents("php://input"), true);
        }
        // Validar dados requeridos
        $required = ['nome','sobrenome','numero_documento','email','cargo','data_contratacao','codigo_sistema_interno','id_departamento'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "Campo obrigatório ausente: $field"]);
                return;
            }
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de email inválido']);
            return;
        }
        if (strlen($data['nome']) > 50 || strlen($data['sobrenome']) > 50) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Nome ou sobrenome muito longos (máximo 50 caracteres)']);
            return;
        }
        if (strlen($data['numero_documento']) > 20) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Número de documento muito longo (máximo 20 caracteres)']);
            return;
        }
        if ($this->funcionarioModel->usuarioExistente($data['numero_documento'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Já existe um funcionário com esse número de documento.']);
            return;
        }
        // Procesar la foto si existe
        $fotoBin = null;
        if ($isMultipart && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoBin = file_get_contents($_FILES['foto']['tmp_name']);
        }
        // Asignar valores al modelo
        $this->funcionarioModel->nome = mb_strtoupper($data['nome'], 'UTF-8');
        $this->funcionarioModel->sobrenome = mb_strtoupper($data['sobrenome'], 'UTF-8');
        $this->funcionarioModel->numero_documento = $data['numero_documento'];
        $this->funcionarioModel->email = $data['email'];
        $this->funcionarioModel->cargo = $data['cargo'];
        $this->funcionarioModel->data_contratacao = $data['data_contratacao'];
        $this->funcionarioModel->data_nascimento = isset($data['data_nascimento']) ? $data['data_nascimento'] : null;
        $this->funcionarioModel->codigo_sistema_interno = $data['codigo_sistema_interno'];
        $this->funcionarioModel->id_departamento = $data['id_departamento'];
        $this->funcionarioModel->id_chefe_direto = isset($data['id_chefe_direto']) && $data['id_chefe_direto'] !== '' ? $data['id_chefe_direto'] : null;
        $this->funcionarioModel->foto = $fotoBin;
        try {
            if ($this->funcionarioModel->create()) {
                echo json_encode(['status' => 'success', 'message' => 'Funcionário criado com sucesso']);
            } else {
                http_response_code(503);
                echo json_encode(['status' => 'error', 'message' => 'Não foi possível criar funcionário']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro ao criar funcionário: ' . $e->getMessage()]);
        }
    }
    
    // Método para obter um funcionário específico pelo ID
    public function show($id = null) {
        // Se não houver ID na URI, tentamos pegar da query string
        if ($id === null && isset($_GET['id'])) {
            $id = $_GET['id'];
        }
        
        // Validar se o ID foi fornecido
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID do funcionário não fornecido']);
            return;
        }
        
        try {
            // Obter o funcionário pelo ID
            $funcionario = $this->funcionarioModel->findById($id);
            
            if ($funcionario) {
                echo json_encode(['status' => 'success', 'data' => $funcionario]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Funcionário não encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar funcionário: ' . $e->getMessage()]);
        }
    }
    
    // Método para atualizar um funcionário existente
    public function update($id = null) {
        // Obter ID da URL
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID do funcionário não fornecido.']);
            return;
        }

        // Encontrar registro existente
        $funcionario = $this->funcionarioModel->findById($id);
        if (!$funcionario) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Funcionário não encontrado.']);
            return;
        }

        // Obter dados da requisição de forma robusta
        $isMultipart = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;
        $data = [];
        if ($isMultipart) {
            $data = $_POST;
        } else {
            $input = file_get_contents("php://input");
            if ($input) {
                $data = json_decode($input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                     http_response_code(400);
                     echo json_encode(['status' => 'error', 'message' => 'Corpo da requisição inválido. Esperado JSON ou multipart/form-data.']);
                     return;
                }
            }
        }

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Nenhum dado fornecido para atualização. Verifique o formulário.']);
            return;
        }

        // Validações específicas
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de email inválido.']);
            return;
        }
        if (isset($data['numero_documento']) && $data['numero_documento'] !== $funcionario['numero_documento'] && $this->funcionarioModel->usuarioExistente($data['numero_documento'])) {
            http_response_code(409); // Conflito
            echo json_encode(['status' => 'error', 'message' => 'Já existe um funcionário com esse número de documento.']);
            return;
        }

        // Atribuir ao modelo, mantendo valores antigos se os novos não vierem
        $this->funcionarioModel->id_funcionario = $id;
        $this->funcionarioModel->nome = isset($data['nome']) ? mb_strtoupper($data['nome'], 'UTF-8') : $funcionario['nome'];
        $this->funcionarioModel->sobrenome = isset($data['sobrenome']) ? mb_strtoupper($data['sobrenome'], 'UTF-8') : $funcionario['sobrenome'];
        $this->funcionarioModel->numero_documento = $data['numero_documento'] ?? $funcionario['numero_documento'];
        $this->funcionarioModel->email = $data['email'] ?? $funcionario['email'];
        $this->funcionarioModel->cargo = $data['cargo'] ?? $funcionario['cargo'];
        $this->funcionarioModel->data_contratacao = $data['data_contratacao'] ?? $funcionario['data_contratacao'];
        $this->funcionarioModel->data_nascimento = $data['data_nascimento'] ?? $funcionario['data_nascimento'];
        $this->funcionarioModel->codigo_sistema_interno = $data['codigo_sistema_interno'] ?? $funcionario['codigo_sistema_interno'];
        $this->funcionarioModel->id_departamento = $data['id_departamento'] ?? $funcionario['id_departamento'];
        $this->funcionarioModel->id_chefe_direto = (isset($data['id_chefe_direto']) && $data['id_chefe_direto'] !== '') ? $data['id_chefe_direto'] : $funcionario['id_chefe_direto'];

        // Manter foto antiga por padrão, e atualizar se uma nova for enviada
        $fotoBin = $funcionario['foto'];

        // Se uma nova foto for enviada, ela tem prioridade
        if ($isMultipart && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoBin = file_get_contents($_FILES['foto']['tmp_name']);
        } 
        // Senão, verificar se a foto deve ser removida (o campo remove_foto foi enviado como '1')
        else if ($isMultipart && isset($data['remove_foto']) && $data['remove_foto'] == '1') {
            $fotoBin = null;
        }
        
        $this->funcionarioModel->foto = $fotoBin;

        // Executar a atualização
        try {
            if ($this->funcionarioModel->update()) {
                echo json_encode(['status' => 'success', 'message' => 'Funcionário atualizado com sucesso']);
            } else {
                http_response_code(503);
                echo json_encode(['status' => 'error', 'message' => 'Não foi possível atualizar o funcionário no banco de dados.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro no servidor ao atualizar funcionário: ' . $e->getMessage()]);
        }
    }
    
    // Método para excluir um funcionário
    public function destroy($id = null) {
        // Se não houver ID na URI, tentamos pegar da query string
        if ($id === null && isset($_GET['id'])) {
            $id = $_GET['id'];
        }
        
        // Validar se o ID foi fornecido
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID do funcionário não fornecido']);
            return;
        }
        
        // Verificar se o funcionário existe antes de excluir
        $funcionario = $this->funcionarioModel->findById($id);
        if (!$funcionario) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Funcionário não encontrado']);
            return;
        }
        
        // Definir o ID do funcionário a ser excluído
        $this->funcionarioModel->id_funcionario = $id;
        
        try {
            if ($this->funcionarioModel->delete()) {
                echo json_encode(['status' => 'success', 'message' => 'Funcionário excluído com sucesso']);
            } else {
                http_response_code(503);
                echo json_encode(['status' => 'error', 'message' => 'Não foi possível excluir funcionário']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir funcionário: ' . $e->getMessage()]);
        }
    }
}
