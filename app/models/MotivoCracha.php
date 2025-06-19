<?php
// app/models/MotivoCracha.php

class MotivoCracha {
    private $conn;
    private $table_name = "motivos_cracha";
    
    public $id_motivo;
    public $descricao_motivo;

    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obter todos os motivos
    public function getAll() {
        $query = "SELECT id_motivo, descricao_motivo FROM " . $this->table_name . " ORDER BY descricao_motivo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obter um motivo específico por ID
    public function getById() {
        $query = "SELECT id_motivo, descricao_motivo FROM " . $this->table_name . " WHERE id_motivo = :id_motivo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_motivo', $this->id_motivo);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->descricao_motivo = $row['descricao_motivo'];
            return true;
        }
        return false;
    }
    
    // Criar um novo motivo
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (descricao_motivo) VALUES (:descricao_motivo)";
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->descricao_motivo = htmlspecialchars(strip_tags($this->descricao_motivo));
        
        // Bind dos parâmetros
        $stmt->bindParam(':descricao_motivo', $this->descricao_motivo);
        
        // Executar query
        if ($stmt->execute()) {
            $this->id_motivo = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Atualizar um motivo existente
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET descricao_motivo = :descricao_motivo WHERE id_motivo = :id_motivo";
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->descricao_motivo = htmlspecialchars(strip_tags($this->descricao_motivo));
        $this->id_motivo = htmlspecialchars(strip_tags($this->id_motivo));
        
        // Bind dos parâmetros
        $stmt->bindParam(':descricao_motivo', $this->descricao_motivo);
        $stmt->bindParam(':id_motivo', $this->id_motivo);
        
        // Executar query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Excluir um motivo
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_motivo = :id_motivo";
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->id_motivo = htmlspecialchars(strip_tags($this->id_motivo));
        
        // Bind dos parâmetros
        $stmt->bindParam(':id_motivo', $this->id_motivo);
        
        // Executar query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
