-- Enterprise Ranking - schema completo (v1)

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NULL,
  nivel ENUM('super_admin','rh') NOT NULL DEFAULT 'rh',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  tipo ENUM('convite','recuperacao') NOT NULL,
  expira_em DATETIME NOT NULL,
  usado_em DATETIME NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE regionais (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL UNIQUE,
  diretor_colaborador_id INT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cidades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  uf CHAR(2) NOT NULL,
  regional_id INT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_cidade_uf (nome, uf),
  FOREIGN KEY (regional_id) REFERENCES regionais(id)
);

CREATE TABLE departamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE colaboradores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(180) NOT NULL,
  foto VARCHAR(255) NOT NULL,
  sexo ENUM('masculino','feminino') NOT NULL,
  cidade_id INT NOT NULL,
  departamento_id INT NOT NULL,
  cargo VARCHAR(150) NOT NULL,
  data_admissao DATE NOT NULL,
  data_nascimento DATE NOT NULL,
  status ENUM('ativo','desligado') NOT NULL DEFAULT 'ativo',
  data_desligamento DATE NULL,
  telefone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  instagram VARCHAR(100) NULL,
  facebook VARCHAR(100) NULL,
  observacoes TEXT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cidade_id) REFERENCES cidades(id),
  FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
);

ALTER TABLE regionais
  ADD FOREIGN KEY (diretor_colaborador_id) REFERENCES colaboradores(id);

CREATE TABLE colaboradores_historico (
  id INT AUTO_INCREMENT PRIMARY KEY,
  colaborador_id INT NOT NULL,
  tipo ENUM('admissao','promocao','mudanca_setor','mudanca_cidade','desligamento','readmissao') NOT NULL,
  data_evento DATE NOT NULL,
  cargo_anterior VARCHAR(150) NULL,
  cargo_novo VARCHAR(150) NULL,
  detalhes VARCHAR(255) NULL,
  criado_por INT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
  FOREIGN KEY (criado_por) REFERENCES usuarios(id)
);

CREATE TABLE tipos_ocorrencia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE ocorrencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  colaborador_id INT NOT NULL,
  tipo_ocorrencia_id INT NOT NULL,
  data_evento DATE NOT NULL,
  descricao TEXT NULL,
  arquivo VARCHAR(255) NULL,
  criado_por INT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
  FOREIGN KEY (tipo_ocorrencia_id) REFERENCES tipos_ocorrencia(id),
  FOREIGN KEY (criado_por) REFERENCES usuarios(id)
);

CREATE TABLE rankings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mes TINYINT NOT NULL,
  ano SMALLINT NOT NULL,
  setor VARCHAR(150) NOT NULL,
  colaborador_id INT NOT NULL,
  colocacao TINYINT NOT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
);

CREATE TABLE artes_geradas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('ranking','boas_vindas','promocao','aniversario_grupo','aniversario_individual') NOT NULL,
  ranking_id INT NULL,
  colaborador_id INT NULL,
  mes TINYINT NULL,
  ano SMALLINT NULL,
  caminho_imagem VARCHAR(255) NOT NULL,
  gerado_por INT NULL,
  gerado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ranking_id) REFERENCES rankings(id),
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
  FOREIGN KEY (gerado_por) REFERENCES usuarios(id)
);

CREATE TABLE configuracoes_visuais (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cor_primaria VARCHAR(7) NOT NULL DEFAULT '#004597',
  cor_secundaria VARCHAR(7) NOT NULL DEFAULT '#FF9308',
  logo VARCHAR(255) NULL,
  fonte VARCHAR(100) NOT NULL DEFAULT 'Poppins',
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  acao VARCHAR(150) NOT NULL,
  entidade VARCHAR(100) NULL,
  entidade_id INT NULL,
  detalhes TEXT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Configuração visual padrão (identidade Mottanet, ajustável depois pelo Super Admin)
INSERT INTO configuracoes_visuais (cor_primaria, cor_secundaria, fonte) VALUES ('#004597', '#FF9308', 'Poppins');