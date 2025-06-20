<?php

require_once __DIR__ . '/../models/Funcionario.php';
require_once __DIR__ . '/../auth/verificarToken.php';

class FuncionarioController {
    public function handleRequest($method, $id) {
        switch ($method) {
            case 'GET':
                $this->getFuncionarios($id);
                break;
            case 'POST':
                $this->createFuncionario();
                break;
            case 'PUT':
                $this->updateFuncionario($id);
                break;
            case 'DELETE':
                $this->deleteFuncionario($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
                break;
        }
    }

    private function getFuncionarios($id) {
        $funcionarioModel = new Funcionario();
        if ($id) {
            $funcionario = $funcionarioModel->findById($id);
            echo json_encode($funcionario);
        } else {
            $funcionarios = $funcionarioModel->findAll();
            echo json_encode($funcionarios);
        }
    }

    private function createFuncionario() {
        verificarToken(); // Verifica o token JWT

        $dados = json_decode(file_get_contents('php://input'), true);
        $erros = [];

        // Validação de campos obrigatórios e de formato
        if (empty($dados['codigo_empresa'])) $erros[] = "O campo 'codigo_empresa' é obrigatório.";
        if (empty($dados['nome'])) $erros[] = "O campo 'nome' é obrigatório.";
        if (empty($dados['sobrenome'])) $erros[] = "O campo 'sobrenome' é obrigatório.";
        if (empty($dados['cargo'])) $erros[] = "O campo 'cargo' é obrigatório.";
        if (empty($dados['documento'])) $erros[] = "O campo 'documento' é obrigatório.";
        if (empty($dados['email'])) {
            $erros[] = "O campo 'email' é obrigatório.";
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = "O formato do campo 'email' é inválido.";
        }

        if (!empty($erros)) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'erro_validacao', 'mensagens' => $erros]);
            return;
        }

        $funcionarioModelo = new Funcionario();
        try {
            if ($funcionarioModelo->create($dados)) {
                http_response_code(201); // 201 Created
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Funcionário criado com sucesso.']);
            }
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            error_log('Erro ao criar funcionário: ' . $e->getMessage());

            if ($e->getCode() == '23000') {
                http_response_code(409); // Conflict
                echo json_encode(['status' => 'erro_conflito', 'mensagem' => 'Erro: Código da empresa, documento ou email já cadastrado.']);
            } else {
                echo json_encode(['status' => 'erro_servidor', 'mensagem' => 'Ocorreu um erro interno ao tentar criar o funcionário.']);
            }
        }
    }

    private function updateFuncionario($id_funcionario) {
        verificarToken(); // Verifica o token JWT

        $dados = json_decode(file_get_contents('php://input'), true);
        $erros = [];

        // Validação de campos obrigatórios e de formato
        if (empty($dados['codigo_empresa'])) $erros[] = "O campo 'codigo_empresa' é obrigatório.";
        if (empty($dados['nome'])) $erros[] = "O campo 'nome' é obrigatório.";
        if (empty($dados['sobrenome'])) $erros[] = "O campo 'sobrenome' é obrigatório.";
        if (empty($dados['cargo'])) $erros[] = "O campo 'cargo' é obrigatório.";
        if (empty($dados['documento'])) $erros[] = "O campo 'documento' é obrigatório.";
        if (empty($dados['email'])) {
            $erros[] = "O campo 'email' é obrigatório.";
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = "O formato do campo 'email' é inválido.";
        }

        if (!empty($erros)) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'erro_validacao', 'mensagens' => $erros]);
            return;
        }

        $funcionarioModelo = new Funcionario();
        try {
            if ($funcionarioModelo->update($id_funcionario, $dados)) {
                http_response_code(200); // OK
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Funcionário atualizado com sucesso.']);
            }
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            error_log('Erro ao atualizar funcionário: ' . $e->getMessage());

            if ($e->getCode() == '23000') {
                http_response_code(409); // Conflict
                echo json_encode(['status' => 'erro_conflito', 'mensagem' => 'Erro: Código da empresa, documento ou email já cadastrado.']);
            } else {
                echo json_encode(['status' => 'erro_servidor', 'mensagem' => 'Ocorreu um erro interno ao tentar atualizar o funcionário.']);
            }
        }
    }

    private function deleteFuncionario($id_funcionario) {
        verificarToken(); // Verifica o token JWT

        $funcionarioModelo = new Funcionario();
        try {
            if ($funcionarioModelo->delete($id_funcionario)) {
                http_response_code(200); // OK
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Funcionário excluído com sucesso.']);
            }
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            error_log('Erro ao excluir funcionário: ' . $e->getMessage());
            echo json_encode(['status' => 'erro_servidor', 'mensagem' => 'Ocorreu um erro interno ao tentar excluir o funcionário.']);
        }
    }
}