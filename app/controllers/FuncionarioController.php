<?php
// app/controllers/FuncionarioController.php

require_once __DIR__ . '/../models/Funcionario.php';
require_once __DIR__ . '/../core/Database.php';

class FuncionarioController {
    private $funcionarioModel;

    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        $this->funcionarioModel = new Funcionario($db);
    }

    public function index() {
        try {
            $funcionarios = $this->funcionarioModel->getAll();
            echo json_encode(['status' => 'success', 'data' => $funcionarios]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Não foi possível listar funcionários: ' . $e->getMessage()]);
        }
    }
    // Outros métodos como store(), show(), update(), destroy() viriam aqui no futuro
}
