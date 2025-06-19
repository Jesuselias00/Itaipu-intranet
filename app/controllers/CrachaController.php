<?php
// app/controllers/CrachaController.php

require_once __DIR__ . '/../models/Cracha.php';
require_once __DIR__ . '/../models/MotivoCracha.php';
require_once __DIR__ . '/../core/Database.php';

class CrachaController {
    private $crachaModel;
    private $motivoModel;

    public function __construct() {
        try {
            $database = new Database();
            $db = $database->connect();
            $this->crachaModel = new Cracha($db);
            $this->motivoModel = new MotivoCracha($db);
            
            // Registrar inicialização exitosa
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::__construct | Inicializado com sucesso\n", FILE_APPEND);
        } catch (Exception $e) {
            // Registrar erro de inicialização
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::__construct | ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
            
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

    // Listar todos os crachás
    public function index() {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::index | Método chamado\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Obter crachás
            $crachas = $this->crachaModel->getAll();
            
            // Registrar sucesso
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::index | Sucesso: " . count($crachas) . " crachás encontrados\n", FILE_APPEND);
            
            echo json_encode(['status' => 'success', 'data' => $crachas]);
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::index | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Não foi possível listar crachás: ' . $e->getMessage()
            ]);
        }
    }
    
    // Listar crachás pendentes
    public function pendentes() {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::pendentes | Método chamado\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Obter crachás pendentes
            $crachas = $this->crachaModel->getPendentes();
            
            // Registrar sucesso
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::pendentes | Sucesso: " . count($crachas) . " crachás pendentes encontrados\n", FILE_APPEND);
            
            echo json_encode(['status' => 'success', 'data' => $crachas]);
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::pendentes | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Não foi possível listar crachás pendentes: ' . $e->getMessage()
            ]);
        }
    }
    
    // Obter crachás por status
    public function status($statusParam) {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::status | Método chamado com status: " . $statusParam . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Validar status
            $statusValidos = ['Solicitado', 'Emitido', 'Vencido', 'Cancelado'];
            if (!in_array($statusParam, $statusValidos)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Status inválido']);
                return;
            }
            
            // Obter crachás pelo status
            $crachas = $this->crachaModel->getByStatus($statusParam);
            
            // Registrar sucesso
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::status | Sucesso: " . count($crachas) . " crachás com status " . $statusParam . " encontrados\n", FILE_APPEND);
            
            echo json_encode(['status' => 'success', 'data' => $crachas]);
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::status | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Não foi possível listar crachás por status: ' . $e->getMessage()
            ]);
        }
    }

    // Obter um crachá específico
    public function show($id) {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::show | Método chamado com ID: " . $id . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Setar ID do cracha
            $this->crachaModel->id_cracha = $id;
            
            // Obter dados do crachá
            $cracha = $this->crachaModel->getById();
            
            if ($cracha) {
                // Registrar sucesso
                file_put_contents(__DIR__ . '/../core/router_debug.log', 
                    date('c') . " | CrachaController::show | Sucesso: Crachá ID " . $id . " encontrado\n", FILE_APPEND);
                    
                echo json_encode(['status' => 'success', 'data' => $cracha]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Crachá não encontrado']);
            }
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::show | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro ao buscar crachá: ' . $e->getMessage()
            ]);
        }
    }
    
    // Obter crachás de um funcionário
    public function porFuncionario($idFuncionario) {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::porFuncionario | Método chamado com ID Funcionário: " . $idFuncionario . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Setar ID do funcionário
            $this->crachaModel->id_funcionario = $idFuncionario;
            
            // Obter crachás do funcionário
            $crachas = $this->crachaModel->getByFuncionario();
            
            // Registrar sucesso
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::porFuncionario | Sucesso: " . count($crachas) . " crachás encontrados para o funcionário ID " . $idFuncionario . "\n", FILE_APPEND);
                
            echo json_encode(['status' => 'success', 'data' => $crachas]);
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::porFuncionario | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro ao buscar crachás do funcionário: ' . $e->getMessage()
            ]);
        }
    }
    
    // Criar um novo crachá
    public function store() {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::store | Método chamado\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Obter dados da requisição
            $data = json_decode(file_get_contents("php://input"));
            
            // Validar se os dados necessários estão presentes
            if (
                !isset($data->id_funcionario) ||
                !isset($data->id_motivo)
            ) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Dados incompletos para criar crachá. id_funcionario e id_motivo são obrigatórios.'
                ]);
                return;
            }
            
            // Definir valores para o novo crachá
            $this->crachaModel->id_funcionario = $data->id_funcionario;
            $this->crachaModel->id_motivo = $data->id_motivo;
            $this->crachaModel->data_solicitacao = date('Y-m-d'); // Data atual
            $this->crachaModel->status_cracha = 'Solicitado'; // Status inicial
            
            // Campos opcionais
            $this->crachaModel->observacoes = isset($data->observacoes) ? $data->observacoes : null;
            $this->crachaModel->data_emissao = null; // Será definido quando o crachá for emitido
            $this->crachaModel->data_validade = null; // Será definido quando o crachá for emitido
            
            // Criar o crachá
            if ($this->crachaModel->create()) {
                // Registrar sucesso
                file_put_contents(__DIR__ . '/../core/router_debug.log', 
                    date('c') . " | CrachaController::store | Sucesso: Crachá criado com ID " . $this->crachaModel->id_cracha . "\n", FILE_APPEND);
                
                http_response_code(201);
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Crachá criado com sucesso', 
                    'id_cracha' => $this->crachaModel->id_cracha
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Não foi possível criar o crachá'
                ]);
            }
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::store | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro ao criar crachá: ' . $e->getMessage()
            ]);
        }
    }
    
    // Atualizar um crachá existente
    public function update($id) {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::update | Método chamado com ID: " . $id . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Obter dados da requisição
            $data = json_decode(file_get_contents("php://input"));
            
            // Verificar se o crachá existe
            $this->crachaModel->id_cracha = $id;
            $existingCracha = $this->crachaModel->getById();
            
            if (!$existingCracha) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Crachá não encontrado'
                ]);
                return;
            }
            
            // Atualizar campos com base nos dados recebidos
            $this->crachaModel->id_funcionario = isset($data->id_funcionario) ? $data->id_funcionario : $existingCracha['id_funcionario'];
            $this->crachaModel->id_motivo = isset($data->id_motivo) ? $data->id_motivo : $existingCracha['id_motivo'];
            $this->crachaModel->data_solicitacao = isset($data->data_solicitacao) ? $data->data_solicitacao : $existingCracha['data_solicitacao'];
            $this->crachaModel->data_emissao = isset($data->data_emissao) ? $data->data_emissao : $existingCracha['data_emissao'];
            $this->crachaModel->data_validade = isset($data->data_validade) ? $data->data_validade : $existingCracha['data_validade'];
            $this->crachaModel->status_cracha = isset($data->status_cracha) ? $data->status_cracha : $existingCracha['status_cracha'];
            $this->crachaModel->observacoes = isset($data->observacoes) ? $data->observacoes : $existingCracha['observacoes'];
            
            // Atualizar o crachá
            if ($this->crachaModel->update()) {
                // Registrar sucesso
                file_put_contents(__DIR__ . '/../core/router_debug.log', 
                    date('c') . " | CrachaController::update | Sucesso: Crachá ID " . $id . " atualizado\n", FILE_APPEND);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Crachá atualizado com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Não foi possível atualizar o crachá'
                ]);
            }
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::update | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro ao atualizar crachá: ' . $e->getMessage()
            ]);
        }
    }
    
    // Atualizar apenas o status do crachá
    public function updateStatus($id) {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::updateStatus | Método chamado com ID: " . $id . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Obter dados da requisição
            $data = json_decode(file_get_contents("php://input"));
            
            // Verificar se o status foi fornecido
            if (!isset($data->status_cracha)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Status não fornecido'
                ]);
                return;
            }
            
            // Validar status
            $statusValidos = ['Solicitado', 'Emitido', 'Vencido', 'Cancelado'];
            if (!in_array($data->status_cracha, $statusValidos)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Status inválido'
                ]);
                return;
            }
            
            // Verificar se o crachá existe
            $this->crachaModel->id_cracha = $id;
            $existingCracha = $this->crachaModel->getById();
            
            if (!$existingCracha) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Crachá não encontrado'
                ]);
                return;
            }
            
            // Atualizar o status
            $this->crachaModel->status_cracha = $data->status_cracha;
            
            // Atualizar o status do crachá
            if ($this->crachaModel->updateStatus()) {
                // Registrar sucesso
                file_put_contents(__DIR__ . '/../core/router_debug.log', 
                    date('c') . " | CrachaController::updateStatus | Sucesso: Status do Crachá ID " . $id . " atualizado para " . $data->status_cracha . "\n", FILE_APPEND);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Status do crachá atualizado com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Não foi possível atualizar o status do crachá'
                ]);
            }
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::updateStatus | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro ao atualizar status do crachá: ' . $e->getMessage()
            ]);
        }
    }
    
    // Excluir um crachá
    public function destroy($id) {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::destroy | Método chamado com ID: " . $id . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Verificar se o crachá existe
            $this->crachaModel->id_cracha = $id;
            $existingCracha = $this->crachaModel->getById();
            
            if (!$existingCracha) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Crachá não encontrado'
                ]);
                return;
            }
            
            // Excluir o crachá
            if ($this->crachaModel->delete()) {
                // Registrar sucesso
                file_put_contents(__DIR__ . '/../core/router_debug.log', 
                    date('c') . " | CrachaController::destroy | Sucesso: Crachá ID " . $id . " excluído\n", FILE_APPEND);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Crachá excluído com sucesso'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Não foi possível excluir o crachá'
                ]);
            }
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::destroy | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro ao excluir crachá: ' . $e->getMessage()
            ]);
        }
    }
    
    // Listar todos os motivos de crachá
    public function motivos() {
        try {
            // Registrar chamada ao método
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::motivos | Método chamado\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON
            header('Content-Type: application/json');
            
            // Obter motivos
            $motivos = $this->motivoModel->getAll();
            
            // Registrar sucesso
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::motivos | Sucesso: " . count($motivos) . " motivos encontrados\n", FILE_APPEND);
            
            echo json_encode(['status' => 'success', 'data' => $motivos]);
            
        } catch (Exception $e) {
            // Registrar erro
            file_put_contents(__DIR__ . '/../core/router_debug.log', 
                date('c') . " | CrachaController::motivos | ERRO: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            
            // Garantir cabeçalho JSON e código de status
            header('Content-Type: application/json');
            http_response_code(500);
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Não foi possível listar motivos de crachá: ' . $e->getMessage()
            ]);
        }
    }
}
