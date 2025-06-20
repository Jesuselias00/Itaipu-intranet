<?php

class Database {
    private static $connection;

    public static function getConnection() {
        if (!self::$connection) {
            try {
                $host = 'localhost';
                $dbname = 'intranet_itaipu';
                $username = 'root';
                $password = 'qH@v11@dKaKd4wIx';

                self::$connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erro na conexão com o banco de dados: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
