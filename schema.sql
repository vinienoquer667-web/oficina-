-- =====================================================================
-- SGE (Sistema de Gestão de Estágios) - Banco de Dados
-- =====================================================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS sge_db;
USE sge_db;

-- =====================================================================
-- 1. TABELA DE USUÁRIOS
-- =====================================================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('admin', 'orientador', 'supervisor', 'estagiario') NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cpf (cpf),
    INDEX idx_email (email),
    INDEX idx_perfil (perfil)
);

-- =====================================================================
-- 2. TABELA DE CURSOS
-- =====================================================================
CREATE TABLE cursos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    carga_horaria_minima INT NOT NULL DEFAULT 400,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome)
);

-- =====================================================================
-- 3. TABELA DE EMPRESAS
-- =====================================================================
CREATE TABLE empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    email VARCHAR(255),
    telefone VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    contato_responsavel VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cnpj (cnpj),
    INDEX idx_nome (nome)
);

-- =====================================================================
-- 4. TABELA DE ESTÁGIOS
-- =====================================================================
CREATE TABLE estagios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    curso_id INT NOT NULL,
    empresa_id INT NOT NULL,
    orientador_id INT,
    supervisor_id INT,
    tipo ENUM('obrigatorio', 'opcional') NOT NULL DEFAULT 'obrigatorio',
    status ENUM('abertura', 'em_andamento', 'concluido', 'cancelado') NOT NULL DEFAULT 'abertura',
    data_inicio DATE,
    data_fim DATE,
    carga_horaria_total INT NOT NULL DEFAULT 400,
    carga_horaria_cumprida INT DEFAULT 0,
    descricao TEXT,
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE RESTRICT,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
    FOREIGN KEY (orientador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (supervisor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_data_inicio (data_inicio)
);

-- =====================================================================
-- 5. TABELA DE DOCUMENTOS
-- =====================================================================
CREATE TABLE documentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    estagio_id INT NOT NULL,
    tipo ENUM('plano_trabalho', 'relatorio', 'avaliacao', 'outro') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    arquivo_nome VARCHAR(255),
    arquivo_caminho VARCHAR(500),
    status ENUM('pendente', 'aprovado', 'rejeitado', 'revisao') NOT NULL DEFAULT 'pendente',
    responsavel_id INT,
    observacoes TEXT,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao DATETIME,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estagio_id) REFERENCES estagios(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_estagio (estagio_id),
    INDEX idx_status (status)
);

-- =====================================================================
-- 6. TABELA DE ATIVIDADES DO PROFESSOR
-- =====================================================================
CREATE TABLE atividades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    estagio_id INT NOT NULL,
    professor_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    data_limite DATE,
    status ENUM('pendente', 'respondida', 'vencida') NOT NULL DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_resposta DATETIME,
    resposta_texto TEXT,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estagio_id) REFERENCES estagios(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_estagio (estagio_id),
    INDEX idx_professor (professor_id),
    INDEX idx_status (status)
);

-- =====================================================================
-- 7. TABELA DE RELATÓRIOS DE HORAS
-- =====================================================================
CREATE TABLE relatorio_horas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    estagio_id INT NOT NULL,
    data_relatorio DATE NOT NULL,
    horas_trabalhadas INT NOT NULL,
    descricao_atividades TEXT,
    confirmado_orientador BOOLEAN DEFAULT FALSE,
    confirmado_supervisor BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_confirmacao_orientador DATETIME,
    data_confirmacao_supervisor DATETIME,
    FOREIGN KEY (estagio_id) REFERENCES estagios(id) ON DELETE CASCADE,
    INDEX idx_estagio (estagio_id),
    INDEX idx_data (data_relatorio),
    UNIQUE KEY unique_relatorio (estagio_id, data_relatorio)
);

-- =====================================================================
-- 8. TABELA DE LOGS DO SISTEMA
-- =====================================================================
CREATE TABLE logs_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    acao VARCHAR(255) NOT NULL,
    tabela_afetada VARCHAR(100),
    registro_id INT,
    descricao TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (data_acao),
    INDEX idx_acao (acao)
);

-- =====================================================================
-- INSERIR USUÁRIOS DE TESTE
-- =====================================================================
INSERT INTO usuarios (cpf, nome, email, senha, perfil) VALUES
('12345678901', 'Admin Sistema', 'admin@ifsertaope.edu.br', MD5('senha123'), 'admin'),
('98765432101', 'Prof. Orientador João', 'joao.orientador@ifsertaope.edu.br', MD5('senha123'), 'orientador'),
('11122233344', 'Prof. Supervisor Maria', 'maria.supervisor@ifsertaope.edu.br', MD5('senha123'), 'supervisor'),
('55566677788', 'Aluno Pedro Silva', 'pedro.silva@student.ifsertaope.edu.br', MD5('senha123'), 'estagiario');

-- =====================================================================
-- INSERIR CURSOS DE TESTE
-- =====================================================================
INSERT INTO cursos (nome, descricao, carga_horaria_minima) VALUES
('Análise e Desenvolvimento de Sistemas', 'Curso tecnólogo em ADS', 400),
('Informática', 'Curso técnico em Informática', 400),
('Administração', 'Curso técnico em Administração', 400);

-- =====================================================================
-- INSERIR EMPRESAS DE TESTE
-- =====================================================================
INSERT INTO empresas (nome, cnpj, email, telefone, endereco, cidade, estado, contato_responsavel) VALUES
('Tech Solutions Brasil', '12.345.678/0001-99', 'contato@techsolutions.com.br', '(87) 3333-1111', 'Rua A, 123', 'Salgueiro', 'PE', 'João da Silva'),
('Digital Inovação', '98.765.432/0001-88', 'rh@digitalinovacao.com.br', '(87) 3333-2222', 'Avenida B, 456', 'Salgueiro', 'PE', 'Maria Santos');
