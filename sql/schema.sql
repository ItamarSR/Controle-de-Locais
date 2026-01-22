-- sql/schema.sql
-- Esquema mínimo para Estoque System
-- Import: mysql -u root -p < sql/schema.sql

-- usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `nivel_acesso` ENUM('admin','editor','editorpro') NOT NULL DEFAULT 'editor',
  `primeiro_acesso` TINYINT(1) NOT NULL DEFAULT 1,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  FOREIGN KEY (`mp_id`) REFERENCES `materias_primas`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- logs (opcional)
CREATE TABLE IF NOT EXISTS `logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NULL,
  `acao` VARCHAR(255) NULL,
  `detalhes` TEXT NULL,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
