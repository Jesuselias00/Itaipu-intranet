<?php
// app/core/Database.php

class Database {
    private $host;
    private $dbname;
    private $user;
    private $pass;
    private $charset;
    private $pdo; // Objeto PDO de conexão

    public function __construct() {
        // Cargar las configuraciones de la base de datos
        // Usa __DIR__ para garantir que o caminho seja sempre correto, independente de onde o script for executado.
        $config = require __DIR__ . '/../config/database.php';

        $this->host = $config['DB_HOST'];
        $this->dbname = $config['DB_NAME'];
        $this->user = $config['DB_USER'];
        $this->pass = $config['DB_PASS'];
        $this->charset = $config['DB_CHARSET'];
    }

    // Método para obter a conexão PDO
    public function connect() {
        // Se já houver uma conexão ativa, a retornamos (reuso da conexão)
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        // Construção da DSN (Data Source Name)
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lançar exceções em caso de erros (facilita o debug)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retornar resultados como arrays associativos (melhor para trabalhar)
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Desativar emulação de preparações para maior segurança e desempenho
        ];

        try {
            // Tenta criar a nova conexão PDO
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            return $this->pdo;
        } catch (PDOException $e) {
            // Em caso de erro na conexão, mata o script e mostra a mensagem de erro.
            // Em um ambiente de produção, é melhor registrar o erro em um log
            // e mostrar uma mensagem genérica de erro ao usuário por segurança.
            die("Erro de conexão a base de dados: " . $e->getMessage());
        }
    }
}
