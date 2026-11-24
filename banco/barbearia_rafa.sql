-- Barbearia do Rafa - Trabalho Momento II
-- Banco compatível com MySQL/MariaDB (XAMPP)

CREATE DATABASE IF NOT EXISTS barbearia_rafa
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE barbearia_rafa;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pagamentos;
DROP TABLE IF EXISTS vendas;
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS servicos;
DROP TABLE IF EXISTS barbeiros;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('Administrador', 'Atendente') NOT NULL DEFAULT 'Atendente',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf CHAR(11) NOT NULL UNIQUE,
    nascimento DATE NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    observacoes VARCHAR(300) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE barbeiros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf CHAR(11) NOT NULL UNIQUE,
    telefone VARCHAR(11) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    especialidade VARCHAR(60) NOT NULL,
    comissao DECIMAL(5,2) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE servicos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(40) NOT NULL,
    duracao SMALLINT UNSIGNED NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    nivel VARCHAR(20) NOT NULL,
    descricao VARCHAR(300) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(40) NOT NULL,
    marca VARCHAR(80) NOT NULL,
    estoque INT UNSIGNED NOT NULL DEFAULT 0,
    preco DECIMAL(10,2) NOT NULL,
    fornecedor VARCHAR(120) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE agendamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    barbeiro_id INT UNSIGNED NOT NULL,
    servico_id INT UNSIGNED NOT NULL,
    data_agendamento DATETIME NOT NULL,
    confirmacao VARCHAR(20) NOT NULL,
    observacoes VARCHAR(300) NOT NULL,
    status ENUM('Agendado', 'Confirmado', 'Concluído', 'Cancelado') NOT NULL DEFAULT 'Agendado',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_agendamento_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamento_barbeiro FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamento_servico FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_agendamento_data (data_agendamento),
    INDEX idx_agendamento_status (status)
) ENGINE=InnoDB;

-- Tabelas previstas desde o Momento I e mantidas para evolução do sistema.
CREATE TABLE vendas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NULL,
    usuario_id INT UNSIGNED NOT NULL,
    data_venda DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2) NOT NULL,
    observacoes VARCHAR(300) NULL,
    CONSTRAINT fk_venda_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_venda_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE pagamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venda_id INT UNSIGNED NOT NULL,
    forma VARCHAR(30) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pago',
    data_pagamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagamento_venda FOREIGN KEY (venda_id) REFERENCES vendas(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Usuário administrador necessário para o primeiro acesso ao sistema.
-- Login: admin@barbearia.local
-- Senha: Admin@123
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES
('Administrador', 'admin@barbearia.local', '$2y$12$DoKjx/rToVRWjSl5WyyGTu7gfbg3qLKNUIOLA6syyveMvg8.D4PW6', 'Administrador', 1);

-- Dados mínimos de demonstração para permitir testar o sistema logo após a instalação.
INSERT INTO clientes (nome, cpf, nascimento, telefone, email, observacoes) VALUES
('Cliente Demonstração', '00011122233', '2000-01-15', '34999990001', 'cliente@demo.local', 'Registro inicial para demonstração.');

INSERT INTO barbeiros (nome, cpf, telefone, email, especialidade, comissao, ativo) VALUES
('Rafael', '11122233344', '34999990002', 'rafael@demo.local', 'Corte masculino', 40.00, 1),
('Lucas', '22233344455', '34999990003', 'lucas@demo.local', 'Barba', 35.00, 1);

INSERT INTO servicos (nome, categoria, duracao, preco, nivel, descricao, ativo) VALUES
('Corte tradicional', 'Cabelo', 45, 40.00, 'Alta', 'Corte masculino tradicional.', 1),
('Barba completa', 'Barba', 30, 35.00, 'Média', 'Modelagem e acabamento da barba.', 1);

INSERT INTO produtos (nome, categoria, marca, estoque, preco, fornecedor, ativo) VALUES
('Pomada Matte', 'Pomada', 'Rafa Barber', 12, 32.90, 'Fornecedor Demonstração', 1),
('Shampoo Masculino', 'Shampoo', 'Rafa Barber', 8, 29.90, 'Fornecedor Demonstração', 1);
