-- ============================================
-- Banco de Dados MySQL - Igreja Gileade Tabapuã
-- Convertido de SQLite para MySQL
-- ============================================

CREATE DATABASE IF NOT EXISTS igrejagileade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE igrejagileade;

CREATE TABLE IF NOT EXISTS membros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  telefone VARCHAR(20),
  endereco TEXT,
  data_cadastro DATE,
  status VARCHAR(50),
  observacao TEXT,
  data_nascimento DATE,
  sexo VARCHAR(20),
  naturalidade VARCHAR(100),
  estado_civil VARCHAR(50),
  nome_conjuge VARCHAR(255),
  telefone_conjuge VARCHAR(20),
  batismo_aguas DATE,
  ministrio VARCHAR(100),
  funcao VARCHAR(100),
  data_filiacao DATE,
  situacao VARCHAR(50),
  data_saida DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contatos_emergencia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  membro_id INT NOT NULL,
  nome VARCHAR(255) NOT NULL,
  telefone VARCHAR(20),
  parentesco VARCHAR(50),
  FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS familias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome_familia VARCHAR(255) NOT NULL,
  responsavel_id INT,
  FOREIGN KEY (responsavel_id) REFERENCES membros(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS membros_familia (
  membro_id INT NOT NULL,
  familia_id INT NOT NULL,
  parentesco VARCHAR(50),
  PRIMARY KEY (membro_id, familia_id),
  FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE CASCADE,
  FOREIGN KEY (familia_id) REFERENCES familias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ministerios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  descricao TEXT,
  ativo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO ministerios (id, nome, descricao, ativo) VALUES
  (1, 'Louvor e Adoração', 'Ministério de música e louvor', 1),
  (2, 'Mídia e Comunicação', 'Produção de conteúdo digital', 1),
  (3, 'Infantil', 'Atendimento para crianças', 1),
  (4, 'Juvenil', 'Atendimento para jovens', 1),
  (5, 'Evangelismo', 'Ministério de evangelização', 1),
  (6, 'Células', 'Liderança de células', 1),
  (7, 'Finanças', 'Administração financeira', 1),
  (8, 'Obras e Manutenção', 'Manutenção do templo', 1),
  (9, 'Hospitalidade', 'Recepção e acolhida', 1),
  (10, 'Intercessão', 'Oração e intercessão', 1);
