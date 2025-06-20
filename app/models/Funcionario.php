<?php

require_once __DIR__ . '/../core/Database.php';

class Funcionario {
    private $conexao;
    private $tabela = "funcionarios";

    public function __construct() {
        $this->conexao = Database::getConnection();
    }

    public function findAll() {
        $query = "SELECT id, codigo_empresa, nome, sobrenome, cargo, email, ativo FROM " . $this->tabela . " ORDER BY nome ASC";
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($dados) {
        $query = "INSERT INTO " . $this->tabela . " (codigo_empresa, nome, sobrenome, cargo, documento, telefone, email, data_nascimento, foto_path) VALUES (:codigo_empresa, :nome, :sobrenome, :cargo, :documento, :telefone, :email, :data_nascimento, :foto_path)";
        $stmt = $this->conexao->prepare($query);

        $stmt->bindParam(':codigo_empresa', $dados['codigo_empresa']);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':sobrenome', $dados['sobrenome']);
        $stmt->bindParam(':cargo', $dados['cargo']);
        $stmt->bindParam(':documento', $dados['documento']);
        $stmt->bindParam(':telefone', $dados['telefone']);
        $stmt->bindParam(':email', $dados['email']);
        $stmt->bindParam(':data_nascimento', $dados['data_nascimento']);
        $stmt->bindParam(':foto_path', $dados['foto_path']);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao criar funcionário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id_funcionario, $dados) {
        $query = "UPDATE " . $this->tabela . " SET codigo_empresa = :codigo_empresa, nome = :nome, sobrenome = :sobrenome, cargo = :cargo, documento = :documento, telefone = :telefone, email = :email, data_nascimento = :data_nascimento, foto_path = :foto_path WHERE id = :id";
        $stmt = $this->conexao->prepare($query);

        $stmt->bindParam(':codigo_empresa', $dados['codigo_empresa']);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':sobrenome', $dados['sobrenome']);
        $stmt->bindParam(':cargo', $dados['cargo']);
        $stmt->bindParam(':documento', $dados['documento']);
        $stmt->bindParam(':telefone', $dados['telefone']);
        $stmt->bindParam(':email', $dados['email']);
        $stmt->bindParam(':data_nascimento', $dados['data_nascimento']);
        $stmt->bindParam(':foto_path', $dados['foto_path']);
        $stmt->bindParam(':id', $id_funcionario);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao atualizar funcionário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id_funcionario) {
        $query = "DELETE FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(':id', $id_funcionario);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro ao excluir funcionário: ' . $e->getMessage());
            throw $e;
        }
    }
}