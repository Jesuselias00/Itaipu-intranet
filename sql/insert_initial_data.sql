-- Garante que estamos usando o banco de dados correto.
USE intranet_itaipu;

-- Criação das tabelas na ordem de dependência correta.

-- 1. Tabelas sem dependências externas
CREATE TABLE IF NOT EXISTS departamentos (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    nome_departamento VARCHAR(100) NOT NULL UNIQUE,
    sigla_departamento VARCHAR(10) UNIQUE
);

CREATE TABLE IF NOT EXISTS cargos (
    id_cargo INT AUTO_INCREMENT PRIMARY KEY,
    nome_cargo VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS motivos_cracha (
    id_motivo INT AUTO_INCREMENT PRIMARY KEY,
    descricao_motivo VARCHAR(255) NOT NULL
);

-- 2. Tabela 'funcionarios', que depende de 'departamentos' e 'cargos'
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_empresa VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    id_cargo INT,
    documento VARCHAR(50) UNIQUE NOT NULL,
    telefone VARCHAR(20) NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    data_nascimento DATE NULL,
    foto_path VARCHAR(255) NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_departamento INT,
    id_chefe_direto INT NULL,
    FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento) ON DELETE SET NULL,
    FOREIGN KEY (id_cargo) REFERENCES cargos(id_cargo) ON DELETE SET NULL,
    FOREIGN KEY (id_chefe_direto) REFERENCES funcionarios(id) ON DELETE SET NULL
);

-- 3. Tabela 'usuarios', que depende de 'funcionarios'
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(150) NOT NULL,
    id_funcionario INT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'consulta',
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id) ON DELETE SET NULL
);

-- 4. Tabela 'solicitacoes_cracha', que depende de 'funcionarios' e 'motivos_cracha'
CREATE TABLE IF NOT EXISTS solicitacoes_cracha (
    id_solicitacao INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario_solicitante INT NOT NULL,
    id_motivo INT NOT NULL,
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_solicitacao VARCHAR(50) DEFAULT 'Pendente',
    observacoes TEXT,
    FOREIGN KEY (id_funcionario_solicitante) REFERENCES funcionarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_motivo) REFERENCES motivos_cracha(id_motivo)
);

