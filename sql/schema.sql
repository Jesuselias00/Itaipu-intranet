-- SQL de creación de tablas

-- Tabla de Departamentos
CREATE TABLE IF NOT EXISTS departamentos (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    nome_departamento VARCHAR(100) NOT NULL,
    descricao TEXT
);

-- Tabla de Funcionarios
CREATE TABLE IF NOT EXISTS funcionarios (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    sobrenome VARCHAR(50) NOT NULL,
    numero_documento VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE,
    cargo VARCHAR(100),
    data_contratacao DATE,
    data_nascimento DATE, -- Fecha de nacimiento
    codigo_sistema_interno VARCHAR(50) UNIQUE,
    id_departamento INT,
    id_chefe_direto INT NULL, -- Pode ser nulo se não tiver chefe direto ou for o topo da hierarquia
    foto LONGBLOB, -- Para armazenar a imagem como binário
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento),
    FOREIGN KEY (id_chefe_direto) REFERENCES funcionarios(id_funcionario) -- Auto-referência para chefe
);

-- Tabla de Usuarios para acceso al sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    id_funcionario INT NULL, -- Clave foránea opcional para enlazar con un funcionario
    rol VARCHAR(50) NOT NULL DEFAULT 'consulta', -- Ej: 'admin', 'gerente', 'consulta'
    activo BOOLEAN NOT NULL DEFAULT TRUE, -- Para activar/desactivar la cuenta
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario) ON DELETE SET NULL -- Si se elimina el funcionario, el usuario no se elimina pero se desvincula
);

-- Tabla de Motivos para Crachás
CREATE TABLE IF NOT EXISTS motivos_cracha (
    id_motivo INT AUTO_INCREMENT PRIMARY KEY,
    descricao_motivo VARCHAR(255) NOT NULL
);

-- Tabla de Crachás
CREATE TABLE IF NOT EXISTS crachas (
    id_cracha INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,
    id_motivo INT NOT NULL,
    data_solicitacao DATE NOT NULL,
    data_emissao DATE,
    data_validade DATE,
    status_cracha VARCHAR(50), -- Ej: 'Solicitado', 'Emitido', 'Vencido', 'Cancelado'
    observacoes TEXT,
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario),
    FOREIGN KEY (id_motivo) REFERENCES motivos_cracha(id_motivo)
);
