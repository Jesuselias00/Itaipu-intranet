<?php
// app/models/Cracha.php

class Cracha {
    private $conn;
    private $table_name = "crachas";
    
    // Propriedades correspondentes às colunas da tabela
    public $id_cracha;
    public $id_funcionario;
    public $id_motivo;
    public $data_solicitacao;
    public $data_emissao;
    public $data_validade;
    public $status_cracha;
    public $observacoes;
    
    // Construtor com a conexão ao banco de dados
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obter todos os crachás com informações detalhadas
    public function getAll() {
        $query = "SELECT 
                    c.id_cracha, 
                    c.id_funcionario, 
                    f.nome, 
                    f.sobrenome,
                    f.foto_path,
                    c.id_motivo, 
                    m.descricao_motivo,
                    c.data_solicitacao, 
                    c.data_emissao, 
                    c.data_validade, 
                    c.status_cracha, 
                    c.observacoes,
                    d.nome_departamento,
                    f.cargo
                FROM 
                    crachas c
                LEFT JOIN funcionarios f ON c.id_funcionario = f.id
                LEFT JOIN motivos m ON c.id_motivo = m.id_motivo
                LEFT JOIN departamentos d ON f.id_departamento = d.id_departamento
                ORDER BY c.data_solicitacao DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obter crachás pendentes
    public function getPendentes() {
        $query = "SELECT 
                    c.id_cracha, 
                    c.id_funcionario, 
                    f.nome, 
                    f.sobrenome,
                    f.foto,
                    c.id_motivo, 
                    m.descricao_motivo,
                    c.data_solicitacao, 
                    c.data_emissao, 
                    c.data_validade, 
                    c.status_cracha, 
                    c.observacoes,
                    d.nome_departamento,
                    f.cargo
                FROM 
                    " . $this->table_name . " c
                JOIN 
                    funcionarios f ON c.id_funcionario = f.id_funcionario
                JOIN 
                    motivos_cracha m ON c.id_motivo = m.id_motivo
                JOIN 
                    departamentos d ON f.id_departamento = d.id_departamento
                WHERE
                    c.status_cracha = 'Solicitado'
                ORDER BY 
                    c.data_solicitacao ASC";
                    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertendo a foto binária para base64
        foreach ($result as &$row) {
            if (!empty($row['foto'])) {
                $row['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
                unset($row['foto']);
            }
        }
        
        return $result;
    }
    
    // Obter crachás por status
    public function getByStatus($status) {
        $query = "SELECT 
                    c.id_cracha, 
                    c.id_funcionario, 
                    f.nome, 
                    f.sobrenome,
                    f.foto,
                    c.id_motivo, 
                    m.descricao_motivo,
                    c.data_solicitacao, 
                    c.data_emissao, 
                    c.data_validade, 
                    c.status_cracha, 
                    c.observacoes,
                    d.nome_departamento,
                    f.cargo
                FROM 
                    " . $this->table_name . " c
                JOIN 
                    funcionarios f ON c.id_funcionario = f.id_funcionario
                JOIN 
                    motivos_cracha m ON c.id_motivo = m.id_motivo
                JOIN 
                    departamentos d ON f.id_departamento = d.id_departamento
                WHERE
                    c.status_cracha = :status
                ORDER BY 
                    c.data_solicitacao DESC";
                    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertendo a foto binária para base64
        foreach ($result as &$row) {
            if (!empty($row['foto'])) {
                $row['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
                unset($row['foto']);
            }
        }
        
        return $result;
    }
    
    // Obter um único crachá pelo ID
    public function getById() {
        $query = "SELECT 
                    c.id_cracha, 
                    c.id_funcionario, 
                    f.nome, 
                    f.sobrenome,
                    f.foto,
                    c.id_motivo, 
                    m.descricao_motivo,
                    c.data_solicitacao, 
                    c.data_emissao, 
                    c.data_validade, 
                    c.status_cracha, 
                    c.observacoes,
                    d.nome_departamento,
                    f.cargo
                FROM 
                    " . $this->table_name . " c
                JOIN 
                    funcionarios f ON c.id_funcionario = f.id_funcionario
                JOIN 
                    motivos_cracha m ON c.id_motivo = m.id_motivo
                JOIN 
                    departamentos d ON f.id_departamento = d.id_departamento
                WHERE 
                    c.id_cracha = :id_cracha";
                    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cracha', $this->id_cracha);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Converter foto para base64 se existir
            if (!empty($row['foto'])) {
                $row['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
                unset($row['foto']);
            }
            
            return $row;
        }
        
        return false;
    }
    
    // Obter crachás por funcionário
    public function getByFuncionario() {
        $query = "SELECT 
                    c.id_cracha, 
                    c.id_funcionario, 
                    f.nome, 
                    f.sobrenome,
                    c.id_motivo, 
                    m.descricao_motivo,
                    c.data_solicitacao, 
                    c.data_emissao, 
                    c.data_validade, 
                    c.status_cracha, 
                    c.observacoes
                FROM 
                    " . $this->table_name . " c
                JOIN 
                    funcionarios f ON c.id_funcionario = f.id_funcionario
                JOIN 
                    motivos_cracha m ON c.id_motivo = m.id_motivo
                WHERE 
                    c.id_funcionario = :id_funcionario
                ORDER BY 
                    c.data_solicitacao DESC";
                    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_funcionario', $this->id_funcionario);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Criar um novo crachá
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                (id_funcionario, id_motivo, data_solicitacao, data_emissao, data_validade, status_cracha, observacoes) 
                VALUES 
                (:id_funcionario, :id_motivo, :data_solicitacao, :data_emissao, :data_validade, :status_cracha, :observacoes)";
                
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->id_funcionario = htmlspecialchars(strip_tags($this->id_funcionario));
        $this->id_motivo = htmlspecialchars(strip_tags($this->id_motivo));
        $this->data_solicitacao = htmlspecialchars(strip_tags($this->data_solicitacao));
        $this->status_cracha = htmlspecialchars(strip_tags($this->status_cracha));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        
        // Data de emissão e validade podem ser nulos no momento da solicitação
        if (empty($this->data_emissao)) {
            $this->data_emissao = null;
        } else {
            $this->data_emissao = htmlspecialchars(strip_tags($this->data_emissao));
        }
        
        if (empty($this->data_validade)) {
            $this->data_validade = null;
        } else {
            $this->data_validade = htmlspecialchars(strip_tags($this->data_validade));
        }
        
        // Bind dos parâmetros
        $stmt->bindParam(':id_funcionario', $this->id_funcionario);
        $stmt->bindParam(':id_motivo', $this->id_motivo);
        $stmt->bindParam(':data_solicitacao', $this->data_solicitacao);
        $stmt->bindParam(':data_emissao', $this->data_emissao);
        $stmt->bindParam(':data_validade', $this->data_validade);
        $stmt->bindParam(':status_cracha', $this->status_cracha);
        $stmt->bindParam(':observacoes', $this->observacoes);
        
        // Executar query
        if ($stmt->execute()) {
            $this->id_cracha = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Atualizar um crachá existente
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    id_funcionario = :id_funcionario, 
                    id_motivo = :id_motivo, 
                    data_solicitacao = :data_solicitacao, 
                    data_emissao = :data_emissao, 
                    data_validade = :data_validade, 
                    status_cracha = :status_cracha, 
                    observacoes = :observacoes 
                WHERE 
                    id_cracha = :id_cracha";
                    
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->id_cracha = htmlspecialchars(strip_tags($this->id_cracha));
        $this->id_funcionario = htmlspecialchars(strip_tags($this->id_funcionario));
        $this->id_motivo = htmlspecialchars(strip_tags($this->id_motivo));
        $this->data_solicitacao = htmlspecialchars(strip_tags($this->data_solicitacao));
        $this->status_cracha = htmlspecialchars(strip_tags($this->status_cracha));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        
        // Data de emissão e validade podem ser nulos
        if (empty($this->data_emissao)) {
            $this->data_emissao = null;
        } else {
            $this->data_emissao = htmlspecialchars(strip_tags($this->data_emissao));
        }
        
        if (empty($this->data_validade)) {
            $this->data_validade = null;
        } else {
            $this->data_validade = htmlspecialchars(strip_tags($this->data_validade));
        }
        
        // Bind dos parâmetros
        $stmt->bindParam(':id_cracha', $this->id_cracha);
        $stmt->bindParam(':id_funcionario', $this->id_funcionario);
        $stmt->bindParam(':id_motivo', $this->id_motivo);
        $stmt->bindParam(':data_solicitacao', $this->data_solicitacao);
        $stmt->bindParam(':data_emissao', $this->data_emissao);
        $stmt->bindParam(':data_validade', $this->data_validade);
        $stmt->bindParam(':status_cracha', $this->status_cracha);
        $stmt->bindParam(':observacoes', $this->observacoes);
        
        // Executar query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Atualizar apenas o status do crachá
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    status_cracha = :status_cracha";
                    
        // Adicionar campos condicionais à query
        if ($this->status_cracha === 'Emitido') {
            $query .= ", data_emissao = :data_emissao, data_validade = :data_validade";
        }
        
        $query .= " WHERE id_cracha = :id_cracha";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->id_cracha = htmlspecialchars(strip_tags($this->id_cracha));
        $this->status_cracha = htmlspecialchars(strip_tags($this->status_cracha));
        
        // Bind dos parâmetros básicos
        $stmt->bindParam(':id_cracha', $this->id_cracha);
        $stmt->bindParam(':status_cracha', $this->status_cracha);
        
        // Bind condicional de parâmetros adicionais
        if ($this->status_cracha === 'Emitido') {
            // Se for emissão, definir data atual e validade para 1 ano à frente
            $today = date('Y-m-d');
            $expiry = date('Y-m-d', strtotime('+1 year'));
            
            $stmt->bindParam(':data_emissao', $today);
            $stmt->bindParam(':data_validade', $expiry);
        }
        
        // Executar query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Excluir um crachá
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cracha = :id_cracha";
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar id
        $this->id_cracha = htmlspecialchars(strip_tags($this->id_cracha));
        
        // Bind do parâmetro
        $stmt->bindParam(':id_cracha', $this->id_cracha);
        
        // Executar query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
