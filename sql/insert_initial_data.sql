-- Script para inserir dados iniciais

-- Inserir departamentos
INSERT INTO departamentos (nome_departamento, descricao) VALUES
('TI', 'Departamento de Tecnologia da Informação'),
('Engenharia', 'Departamento de Engenharia'),
('Finanças', 'Departamento de Finanças e Contabilidade'),
('Recursos Humanos', 'Departamento de Recursos Humanos'),
('Manutenção', 'Departamento de Manutenção e Operações');

-- Inserir usuário administrador
-- Senha: admin123 (hash bcrypt)
INSERT INTO usuarios (email, password_hash, nombre_completo, rol, activo) VALUES 
('admin@itaipu.com', '$2y$10$9CUd1DyLfVAPB6RJs49Y2.XxVvBw8/kwIZ69D0zv2p9h0YHm00X12', 'Administrador do Sistema', 'admin', 1);

-- Inserir motivos para solicitação de crachás
INSERT INTO motivos_cracha (descricao_motivo) VALUES
('Novo Funcionário'),
('Perda'),
('Roubo'),
('Dano'),
('Promoção'),
('Transferência'),
('Atualização de Foto'),
('Expiração');

-- Inserir funcionário teste (sem foto)
INSERT INTO funcionarios (nome, sobrenome, numero_documento, email, cargo, data_contratacao, data_nascimento, codigo_sistema_interno, id_departamento) VALUES
('Ana', 'Silva', '12345678', 'ana.silva@itaipu.com', 'Gerente TI', '2020-01-01', '1985-05-15', 'EMP001', 1);

-- Inserir segundo funcionário para usar como chefe
INSERT INTO funcionarios (nome, sobrenome, numero_documento, email, cargo, data_contratacao, data_nascimento, codigo_sistema_interno, id_departamento) VALUES
('Carlos', 'Mendes', '87654321', 'carlos.mendes@itaipu.com', 'Diretor Engenharia', '2018-03-15', '1980-08-22', 'EMP002', 2);

-- Atualizar o primeiro funcionário para ter o segundo como chefe
UPDATE funcionarios SET id_chefe_direto = 2 WHERE id_funcionario = 1;
