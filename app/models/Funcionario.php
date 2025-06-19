<?php
// app/models/Funcionario.php

class Funcionario {
    private $conn;
    private $table_name = "funcionarios";
      // Propriedades que correspondem às colunas na tabela
    public $id_funcionario;
    public $nome;
    public $sobrenome;
    public $numero_documento;
    public $email;
    public $cargo;
    public $data_contratacao;
    public $data_nascimento; // Nova propriedade para a data de nascimento
    public $codigo_sistema_interno;
    public $id_departamento;
    public $id_chefe_direto;
    public $foto; // Nova propriedade para a foto

    public function __construct($db) {
        $this->conn = $db;
    }    public function getAll() {
        try {
            $query = "SELECT
                    f.id_funcionario,
                    f.nome,
                    f.sobrenome,
                    f.numero_documento,
                    f.email,
                    f.cargo,
                    f.data_contratacao,
                    f.data_nascimento,
                    f.codigo_sistema_interno,
                    f.id_departamento,
                    f.id_chefe_direto,
                    f.data_criacao_registro,
                    f.ultima_modificacao_registro,
                    d.nome_departamento,
                    chefe.nome AS nome_chefe,
                    chefe.sobrenome AS sobrenome_chefe,
                    f.foto
                FROM
                    " . $this->table_name . " f
                LEFT JOIN
                    departamentos d ON f.id_departamento = d.id_departamento
                LEFT JOIN
                    " . $this->table_name . " chefe ON f.id_chefe_direto = chefe.id_funcionario
                ORDER BY
                    f.nome ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Convertir binario a base64 con tipo correcto
            foreach ($result as &$row) {
                if (!empty($row['foto'])) {
                    $mime = null;
                    $bin = $row['foto'];
                    // Detectar tipo de imagen por cabecera binaria
                    if (substr($bin, 0, 2) === "\xFF\xD8") {
                        $mime = 'image/jpeg';
                    } elseif (substr($bin, 0, 8) === "\x89PNG\x0D\x0A\x1A\x0A") {
                        $mime = 'image/png';
                    }
                    if ($mime) {
                        $row['foto_base64'] = 'data:' . $mime . ';base64,' . base64_encode($bin);
                    } else {
                        $row['foto_base64'] = null; // Imagen no válida o tipo desconocido
                    }
                    unset($row['foto']);
                } else {
                    $row['foto_base64'] = null;
                    unset($row['foto']);
                }
            }
            return $result;
        } catch (Exception $e) {
            // Registrar el error en el log
            file_put_contents(__DIR__ . '/../core/router_debug.log',
                date('c') . " | Funcionario::getAll | ERROR: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            throw $e;
        }
    }
    
    // Método para criar um novo funcionário
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    nome=:nome, sobrenome=:sobrenome, numero_documento=:numero_documento, email=:email, cargo=:cargo, data_contratacao=:data_contratacao, data_nascimento=:data_nascimento, codigo_sistema_interno=:codigo_sistema_interno, id_departamento=:id_departamento, id_chefe_direto=:id_chefe_direto, foto=:foto";
        $stmt = $this->conn->prepare($query);
          // Limpar e sanitizar os dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->sobrenome = htmlspecialchars(strip_tags($this->sobrenome));
        $this->numero_documento = htmlspecialchars(strip_tags($this->numero_documento));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->cargo = htmlspecialchars(strip_tags($this->cargo));
        $this->data_contratacao = htmlspecialchars(strip_tags($this->data_contratacao));
        $this->data_nascimento = isset($this->data_nascimento) ? htmlspecialchars(strip_tags($this->data_nascimento)) : null;
        $this->codigo_sistema_interno = htmlspecialchars(strip_tags($this->codigo_sistema_interno));
          // Bind dos valores para os parâmetros da query
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':sobrenome', $this->sobrenome);
        $stmt->bindParam(':numero_documento', $this->numero_documento);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':cargo', $this->cargo);
        $stmt->bindParam(':data_contratacao', $this->data_contratacao);
        $stmt->bindParam(':data_nascimento', $this->data_nascimento);
        $stmt->bindParam(':codigo_sistema_interno', $this->codigo_sistema_interno);
        $stmt->bindParam(':id_departamento', $this->id_departamento);
        $stmt->bindParam(':id_chefe_direto', $this->id_chefe_direto);
        $stmt->bindParam(':foto', $this->foto, PDO::PARAM_LOB);
        
        // Executar a query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Método para verificar se já existe um funcionário com o mesmo número de documento
    public function usuarioExistente($numero_documento) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE numero_documento = :numero_documento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero_documento", $numero_documento);
        $stmt->execute();
        return ($stmt->fetchColumn() > 0);
    }
    
    // Método para obter um funcionário pelo ID
    public function findById($id) {
        $query = "SELECT
                    f.id_funcionario,
                    f.nome,
                    f.sobrenome,
                    f.numero_documento,
                    f.email,
                    f.cargo,
                    f.data_contratacao,
                    f.data_nascimento,
                    f.codigo_sistema_interno,
                    f.id_departamento,
                    f.id_chefe_direto,
                    f.foto, -- Adicionado para carregar a foto existente
                    d.nome_departamento,
                    chefe.nome AS nome_chefe,
                    chefe.sobrenome AS sobrenome_chefe
                FROM
                    " . $this->table_name . " f
                LEFT JOIN
                    departamentos d ON f.id_departamento = d.id_departamento
                LEFT JOIN
                    " . $this->table_name . " chefe ON f.id_chefe_direto = chefe.id_funcionario
                WHERE
                    f.id_funcionario = :id
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Se não encontrar, retorna null
        if (!$row) {
            return null;
        }
        
        return $row;
    }
    
    // Método para atualizar um funcionário
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    nome = :nome,
                    sobrenome = :sobrenome,
                    numero_documento = :numero_documento,
                    email = :email,
                    cargo = :cargo,
                    data_contratacao = :data_contratacao,
                    data_nascimento = :data_nascimento,
                    codigo_sistema_interno = :codigo_sistema_interno,
                    id_departamento = :id_departamento,
                    id_chefe_direto = :id_chefe_direto,
                    foto = :foto
                WHERE
                    id_funcionario = :id_funcionario";
        $stmt = $this->conn->prepare($query);        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->sobrenome = htmlspecialchars(strip_tags($this->sobrenome));
        $this->numero_documento = htmlspecialchars(strip_tags($this->numero_documento));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->cargo = htmlspecialchars(strip_tags($this->cargo));
        $this->data_contratacao = htmlspecialchars(strip_tags($this->data_contratacao));
        $this->data_nascimento = isset($this->data_nascimento) ? htmlspecialchars(strip_tags($this->data_nascimento)) : null;
        $this->codigo_sistema_interno = htmlspecialchars(strip_tags($this->codigo_sistema_interno));
        // Não sanitizar binário        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':sobrenome', $this->sobrenome);
        $stmt->bindParam(':numero_documento', $this->numero_documento);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':cargo', $this->cargo);
        $stmt->bindParam(':data_contratacao', $this->data_contratacao);
        $stmt->bindParam(':data_nascimento', $this->data_nascimento);
        $stmt->bindParam(':codigo_sistema_interno', $this->codigo_sistema_interno);
        $stmt->bindParam(':id_departamento', $this->id_departamento);
        $stmt->bindParam(':id_chefe_direto', $this->id_chefe_direto);
        $stmt->bindParam(':foto', $this->foto, PDO::PARAM_LOB);
        $stmt->bindParam(':id_funcionario', $this->id_funcionario);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Método para excluir um funcionário
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_funcionario = :id_funcionario";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar o ID
        $this->id_funcionario = htmlspecialchars(strip_tags($this->id_funcionario));
        
        // Bind do ID
        $stmt->bindParam(':id_funcionario', $this->id_funcionario);
        
        // Executar a query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Outros métodos como verificar duplicados, etc.
}
