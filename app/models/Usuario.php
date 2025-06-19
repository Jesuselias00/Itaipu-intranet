<?php
require_once __DIR__ . '/../core/Database.php';

class Usuario {
    private $db;
    private $table_name = "usuarios";

    // Propiedades que corresponden a las columnas de la tabla
    public $id_usuario;
    public $email;
    public $password_hash;
    public $nombre_completo;
    public $id_funcionario;
    public $rol;
    public $activo;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Buscar usuário por email
    public function findByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Crear un nuevo usuario
    public function create() {
        // Verificar si el email ya existe
        $check = $this->findByEmail($this->email);
        if ($check) {
            return false; // El email ya está en uso
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                 (email, password_hash, nombre_completo, id_funcionario, rol, activo) 
                 VALUES 
                 (:email, :password_hash, :nombre_completo, :id_funcionario, :rol, :activo)";
        
        $stmt = $this->db->prepare($query);
        
        // Limpiar y sanitizar datos
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->nombre_completo = htmlspecialchars(strip_tags($this->nombre_completo));
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        
        // Asignar valores a los parámetros
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $this->password_hash);
        $stmt->bindParam(':nombre_completo', $this->nombre_completo);
        $stmt->bindParam(':id_funcionario', $this->id_funcionario);
        $stmt->bindParam(':rol', $this->rol);
        $stmt->bindParam(':activo', $this->activo);
        
        // Ejecutar la query
        if ($stmt->execute()) {
            $this->id_usuario = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Obtener todos los usuarios
    public function getAll() {
        $query = "SELECT 
                    u.id_usuario, 
                    u.email, 
                    u.nombre_completo, 
                    u.rol, 
                    u.activo, 
                    u.data_criacao, 
                    u.data_atualizacao,
                    f.nome AS nombre_funcionario,
                    f.sobrenome AS apellido_funcionario
                  FROM " . $this->table_name . " u
                  LEFT JOIN funcionarios f ON u.id_funcionario = f.id_funcionario
                  ORDER BY u.nombre_completo ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
