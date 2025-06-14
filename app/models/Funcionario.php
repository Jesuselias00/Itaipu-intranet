<?php
// app/models/Funcionario.php

class Funcionario {
    private $conn;
    private $table_name = "funcionarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT
                    f.id_funcionario,
                    f.nome,
                    f.sobrenome,
                    f.numero_documento,
                    f.email,
                    f.cargo,
                    f.data_contratacao,
                    f.codigo_sistema_interno,
                    d.nome_departamento,
                    chefe.nome AS nome_chefe,
                    chefe.sobrenome AS sobrenome_chefe
                FROM
                    " . $this->table_name . " f
                JOIN
                    departamentos d ON f.id_departamento = d.id_departamento
                LEFT JOIN
                    " . $this->table_name . " chefe ON f.id_chefe_direto = chefe.id_funcionario
                ORDER BY
                    f.nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Outros métodos como create(), findById(), update(), delete() viriam aqui no futuro
}
