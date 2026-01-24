-- sql/schema.sql
-- Esquema mínimo para Estoque System
-- Import: mysql -u root -p < sql/schema.sql

-- usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `nivel_acesso` ENUM('admin','editor','editorpro','conferencia') NOT NULL DEFAULT 'editor',
  `primeiro_acesso` TINYINT(1) NOT NULL DEFAULT 1,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migração: adiciona o nível "conferencia" no enum (para bases já existentes)
ALTER TABLE `usuarios`
  MODIFY `nivel_acesso` ENUM('admin','editor','editorpro','conferencia') NOT NULL DEFAULT 'editor';

-- matérias-primas
CREATE TABLE IF NOT EXISTS `materias_primas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo_mp` VARCHAR(50) NOT NULL,
  `nome_mp` VARCHAR(255) NOT NULL,
  UNIQUE KEY `ux_mp_codigo` (`codigo_mp`),
  KEY `idx_nome_mp` (`nome_mp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- locais
CREATE TABLE IF NOT EXISTS `locais` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome_local` VARCHAR(255) NOT NULL,
  `mp_id` INT NOT NULL,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responsavel_usuario_id` INT NULL,
  FOREIGN KEY (`mp_id`) REFERENCES `materias_primas`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migração para bases já existentes (MySQL 5.7+): cria coluna/índice somente se não existirem
SET @__db_name := DATABASE();

SET @__add_resp_col := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = @__db_name AND TABLE_NAME = 'locais' AND COLUMN_NAME = 'responsavel_usuario_id') = 0,
    'ALTER TABLE `locais` ADD COLUMN `responsavel_usuario_id` INT NULL',
    'SELECT 1'
  )
);
PREPARE stmt FROM @__add_resp_col;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @__add_resp_idx := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
      WHERE TABLE_SCHEMA = @__db_name AND TABLE_NAME = 'locais' AND INDEX_NAME = 'idx_locais_responsavel') = 0,
    'CREATE INDEX `idx_locais_responsavel` ON `locais` (`responsavel_usuario_id`)',
    'SELECT 1'
  )
);
PREPARE stmt2 FROM @__add_resp_idx;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- logs (opcional)
CREATE TABLE IF NOT EXISTS `logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NULL,
  `acao` VARCHAR(255) NULL,
  `detalhes` TEXT NULL,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- configurações (tema e impressão)
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `chave` VARCHAR(80) NOT NULL UNIQUE,
  `valor` VARCHAR(255) NOT NULL,
  `atualizado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracoes` (`chave`, `valor`) VALUES
  ('print_text_pt', '22'),
  ('print_local_pt', '0'),
  ('printer_profile', 'bematech'),
  ('print_offset_x_mm', '0'),
  ('print_offset_y_mm', '0'),
  ('print_scale', '1'),
  ('dash_meta_op_step', '4'),
  ('dash_meta_kg_step', '500'),
  ('theme_page', '#f6f7fb'),
  ('theme_header', '#ffffff'),
  ('theme_footer', '#ffffff'),
  ('theme_form', '#ffffff'),
  ('theme_text', '#0f172a'),
  ('logo_path', '')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- ordens de produção (OP)
CREATE TABLE IF NOT EXISTS `ops` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `op` VARCHAR(50) NOT NULL,
  `entrada` VARCHAR(50) NULL,
  `oleo` VARCHAR(50) NULL,
  `saida` VARCHAR(50) NULL,
  `qtde_emb` INT NULL,
  `total_emb_kg` VARCHAR(50) NULL,
  `desperdicio` VARCHAR(50) NULL,
  `cor` VARCHAR(50) NULL,
  `reacerto` INT NOT NULL DEFAULT 0,
  `obs` TEXT NULL,
  `retem` TINYINT(1) NOT NULL DEFAULT 0,
  `responsavel_usuario_id` INT NULL,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_ops_op` (`op`),
  KEY `idx_ops_criado_em` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migração: adiciona coluna desperdicio em bases já existentes
SET @__add_ops_desp := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ops' AND COLUMN_NAME = 'desperdicio') = 0,
    'ALTER TABLE `ops` ADD COLUMN `desperdicio` VARCHAR(50) NULL AFTER `saida`',
    'SELECT 1'
  )
);
PREPARE stmt3 FROM @__add_ops_desp;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Migração: adiciona qtde_emb e total_emb_kg em bases já existentes
SET @__add_ops_qtde := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ops' AND COLUMN_NAME = 'qtde_emb') = 0,
    'ALTER TABLE `ops` ADD COLUMN `qtde_emb` INT NULL AFTER `saida`',
    'SELECT 1'
  )
);
PREPARE stmt4 FROM @__add_ops_qtde;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

SET @__add_ops_total := (
  SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ops' AND COLUMN_NAME = 'total_emb_kg') = 0,
    'ALTER TABLE `ops` ADD COLUMN `total_emb_kg` VARCHAR(50) NULL AFTER `qtde_emb`',
    'SELECT 1'
  )
);
PREPARE stmt5 FROM @__add_ops_total;
EXECUTE stmt5;
DEALLOCATE PREPARE stmt5;

/*
  Seed — ADMIN INICIAL (RECOMENDADO: usar script)

  Para criar o Admin inicial de forma segura e reprodutível execute o script CLI abaixo:
    php sql/seed_admin.php --email=admin@example.com --password=trocar123 --name="Administrador Inicial"

  O script gera o hash com password_hash() e insere o usuário apenas se NÃO houver um Admin existente.
  Após o primeiro login o Admin deve trocar a senha (campo `primeiro_acesso = 1`).

  Se preferir criar manualmente, gere o hash em PHP e insira no SQL (exemplo comentado abaixo):
    php -r "echo password_hash('trocar123', PASSWORD_DEFAULT);"

  Exemplo (manual — substitua <HASH_AQUI>):
  -- INSERT INTO usuarios (nome, email, senha, nivel_acesso, primeiro_acesso, status) 
  -- VALUES ('Administrador Inicial','admin@example.com','<HASH_AQUI>','admin',1,1);
*/
