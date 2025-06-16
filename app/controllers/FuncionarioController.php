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
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | FuncionarioController::index | Método chamado\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Obter funcionários
            $funcionarios = $this->funcionarioModel->getAll();
            
            // Registrar sucesso
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | FuncionarioController::index | Sucesso: " . count($funcionarios) . " funcionários encontrados\n", FILE_APPEND);
            
            echo json_encode(['status' => 'success', 'data' => $funcionarios]);
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | FuncionarioController::index | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
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
        // Si la petición es multipart/form-data, usar $_POST y $_FILES
        if (isset($_FILES['foto'])) {
            $data = (object)$_POST;
        } else {
            $data = json_decode(file_get_contents("php://input"));
        }

        // Validar se os dados necessários estão presentes
        if (
            !isset($data->nome) ||
            !isset($data->sobrenome) ||
            !isset($data->numero_documento) ||
            !isset($data->email) ||
            !isset($data->cargo) ||
            !isset($data->data_contratacao) ||
            !isset($data->codigo_sistema_interno) ||
            !isset($data->id_departamento)
        ) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Dados incompletos para criar funcionário. Todos os campos obrigatórios devem ser fornecidos.']);
            return;
        }

        // Validar formato do email
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de email inválido']);
            return;
        }

        // Validar comprimento dos campos
        if (strlen($data->nome) > 50 || strlen($data->sobrenome) > 50) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Nome ou sobrenome muito longos (máximo 50 caracteres)']);
            return;
        }
        if (strlen($data->numero_documento) > 20) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Número de documento muito longo (máximo 20 caracteres)']);
            return;
        }

        // Verificar se já existe um funcionário com esse número de documento
        if ($this->funcionarioModel->usuarioExistente($data->numero_documento)) {
            http_response_code(409); // Código 409 Conflict
            echo json_encode(['status' => 'error', 'message' => 'Já existe um funcionário com esse número de documento.']);
            return;
        }

        // Procesar la foto si existe
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/img/funcionarios/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('funcionario_') . '.' . $ext;
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destPath)) {
                $fotoPath = 'assets/img/funcionarios/' . $fileName;
            }
        }

        // Atribuir os dados recebidos às propriedades do modelo antes de criar
        $this->funcionarioModel->nome = mb_strtoupper($data->nome, 'UTF-8');
        $this->funcionarioModel->sobrenome = mb_strtoupper($data->sobrenome, 'UTF-8');
        $this->funcionarioModel->numero_documento = $data->numero_documento;
        $this->funcionarioModel->email = $data->email;
        $this->funcionarioModel->cargo = $data->cargo;
        $this->funcionarioModel->data_contratacao = $data->data_contratacao;
        $this->funcionarioModel->codigo_sistema_interno = $data->codigo_sistema_interno;
        $this->funcionarioModel->id_departamento = $data->id_departamento;
        $this->funcionarioModel->id_chefe_direto = isset($data->id_chefe_direto) ? $data->id_chefe_direto : null; // Pode ser nulo
        $this->funcionarioModel->foto = $fotoPath;

        try {
            if ($this->funcionarioModel->create()) {
                http_response_code(201); // Created
                echo json_encode(['status' => 'success', 'message' => 'Funcionário criado com sucesso.']);
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(['status' => 'error', 'message' => 'Não foi possível criar funcionário.']);
            }
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
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
        if ($id === null && isset($_GET['id'])) {
            $id = $_GET['id'];
        }
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID do funcionário não fornecido']);
            return;
        }
        $funcionario = $this->funcionarioModel->findById($id);
        if (!$funcionario) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Funcionário não encontrado']);
            return;
        }

        // Suporte a JSON e multipart/form-data
        $isMultipart = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;
        if ($isMultipart) {
            $data = $_POST;
        } else {
            $data = json_decode(file_get_contents("php://input"), true);
        }
        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Nenhum dado fornecido para atualização']);
            return;
        }
        // Validações (email, comprimento, documento único)
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de email inválido']);
            return;
        }
        if ((isset($data['nome']) && strlen($data['nome']) > 50) || (isset($data['sobrenome']) && strlen($data['sobrenome']) > 50)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Nome ou sobrenome muito longos (máximo 50 caracteres)']);
            return;
        }
        if (isset($data['numero_documento']) && strlen($data['numero_documento']) > 20) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Número de documento muito longo (máximo 20 caracteres)']);
            return;
        }
        if (isset($data['numero_documento']) && $data['numero_documento'] !== $funcionario['numero_documento'] && $this->funcionarioModel->usuarioExistente($data['numero_documento'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Já existe um funcionário com esse número de documento']);
            return;
        }
        $this->funcionarioModel->id_funcionario = $id;
        $this->funcionarioModel->nome = isset($data['nome']) ? mb_strtoupper($data['nome'], 'UTF-8') : $funcionario['nome'];
        $this->funcionarioModel->sobrenome = isset($data['sobrenome']) ? mb_strtoupper($data['sobrenome'], 'UTF-8') : $funcionario['sobrenome'];
        $this->funcionarioModel->numero_documento = isset($data['numero_documento']) ? $data['numero_documento'] : $funcionario['numero_documento'];
        $this->funcionarioModel->email = isset($data['email']) ? $data['email'] : $funcionario['email'];
        $this->funcionarioModel->cargo = isset($data['cargo']) ? $data['cargo'] : $funcionario['cargo'];
        $this->funcionarioModel->data_contratacao = isset($data['data_contratacao']) ? $data['data_contratacao'] : $funcionario['data_contratacao'];
        $this->funcionarioModel->codigo_sistema_interno = isset($data['codigo_sistema_interno']) ? $data['codigo_sistema_interno'] : $funcionario['codigo_sistema_interno'];
        $this->funcionarioModel->id_departamento = isset($data['id_departamento']) ? $data['id_departamento'] : $funcionario['id_departamento'];
        $this->funcionarioModel->id_chefe_direto = (isset($data['id_chefe_direto']) && $data['id_chefe_direto'] !== '') ? $data['id_chefe_direto'] : null;

        // Lógica para atualizar foto
        $fotoPath = $funcionario['foto'] ?? null;
        if ($isMultipart && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoTmp = $_FILES['foto']['tmp_name'];
            $fotoName = uniqid('funcionario_') . '_' . basename($_FILES['foto']['name']);
            $destDir = __DIR__ . '/../../public/assets/img/funcionarios/';
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            $destPath = $destDir . $fotoName;
            if (move_uploaded_file($fotoTmp, $destPath)) {
                // Eliminar foto anterior si existe y es diferente
                if ($fotoPath && file_exists(__DIR__ . '/../../public/' . $fotoPath)) {
                    @unlink(__DIR__ . '/../../public/' . $fotoPath);
                }
                $fotoPath = 'assets/img/funcionarios/' . $fotoName;
            }
        }
        $this->funcionarioModel->foto = $fotoPath;
        try {
            if ($this->funcionarioModel->update()) {
                echo json_encode(['status' => 'success', 'message' => 'Funcionário atualizado com sucesso']);
            } else {
                http_response_code(503);
                echo json_encode(['status' => 'error', 'message' => 'Não foi possível atualizar funcionário']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar funcionário: ' . $e->getMessage()]);
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
