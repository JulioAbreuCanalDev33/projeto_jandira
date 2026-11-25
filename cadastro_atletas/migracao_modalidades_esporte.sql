-- Migração idempotente: padroniza "modalidades de esporte" no banco existente.
-- Renomeia a estrutura antiga (tipos_esporte / tipo_esporte_id) preservando os
-- dados e pode ser executada mais de uma vez sem duplicar registros.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- 1) Remove a chave estrangeira antiga, se ainda existir.
SET @fk_antiga = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cadastro_atletas'
    AND CONSTRAINT_NAME = 'fk_atletas_tipo_esporte'
);
SET @sql = IF(@fk_antiga > 0,
  'ALTER TABLE `cadastro_atletas` DROP FOREIGN KEY `fk_atletas_tipo_esporte`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Renomeia a tabela antiga, se ela existir e a nova ainda não existir.
SET @tabela_antiga = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tipos_esporte'
);
SET @tabela_nova = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'modalidades_esporte'
);
SET @sql = IF(@tabela_antiga > 0 AND @tabela_nova = 0,
  'RENAME TABLE `tipos_esporte` TO `modalidades_esporte`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Garante a tabela nova e sua chave primária.
CREATE TABLE IF NOT EXISTS `modalidades_esporte` (
  `id` int(11) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='modalidades de esporte disponíveis para os atletas';

SET @pk_existe = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'modalidades_esporte'
    AND CONSTRAINT_TYPE = 'PRIMARY KEY'
);
SET @sql = IF(@pk_existe = 0,
  'ALTER TABLE `modalidades_esporte` ADD PRIMARY KEY (`id`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE `modalidades_esporte` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- 4) Renomeia (ou cria) o índice único do nome.
SET @uq_antiga = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'modalidades_esporte'
    AND INDEX_NAME = 'uq_tipos_esporte_nome'
);
SET @uq_nova = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'modalidades_esporte'
    AND INDEX_NAME = 'uq_modalidades_esporte_nome'
);
SET @sql = IF(@uq_antiga > 0 AND @uq_nova = 0,
  'ALTER TABLE `modalidades_esporte` RENAME INDEX `uq_tipos_esporte_nome` TO `uq_modalidades_esporte_nome`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @uq_nova = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'modalidades_esporte'
    AND INDEX_NAME = 'uq_modalidades_esporte_nome'
);
SET @sql = IF(@uq_nova = 0,
  'ALTER TABLE `modalidades_esporte` ADD UNIQUE KEY `uq_modalidades_esporte_nome` (`nome`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5) Dados iniciais (apenas se ainda não cadastrados).
INSERT INTO `modalidades_esporte` (`nome`, `descricao`)
SELECT * FROM (
  SELECT 'Futebol' AS nome, 'Futebol de campo e futsal.' AS descricao
  UNION ALL SELECT 'Judô', 'Artes marciais e combate.'
  UNION ALL SELECT 'Vôlei', 'Vôlei de quadra e de praia.'
  UNION ALL SELECT 'Atletismo', 'Corridas, saltos e arremessos.'
  UNION ALL SELECT 'Basquete', 'Basquete de quadra.'
) AS seeds
WHERE NOT EXISTS (
  SELECT 1 FROM `modalidades_esporte` WHERE `modalidades_esporte`.`nome` = seeds.nome
);

-- 6) Renomeia (ou cria) a coluna de modalidade nos atletas.
SET @col_antiga = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cadastro_atletas'
    AND COLUMN_NAME = 'tipo_esporte_id'
);
SET @col_nova = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cadastro_atletas'
    AND COLUMN_NAME = 'modalidade_esporte_id'
);
SET @sql = IF(@col_antiga > 0 AND @col_nova = 0,
  'ALTER TABLE `cadastro_atletas` CHANGE COLUMN `tipo_esporte_id` `modalidade_esporte_id` int(11) DEFAULT NULL AFTER `telefone`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_nova = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cadastro_atletas'
    AND COLUMN_NAME = 'modalidade_esporte_id'
);
SET @sql = IF(@col_nova = 0,
  'ALTER TABLE `cadastro_atletas` ADD COLUMN `modalidade_esporte_id` int(11) DEFAULT NULL AFTER `telefone`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `cadastro_atletas` a
  JOIN `modalidades_esporte` m ON m.nome = 'Futebol'
SET a.modalidade_esporte_id = m.id
WHERE a.modalidade_esporte_id IS NULL;

-- 7) Remove o índice antigo e cria a chave estrangeira com o nome novo.
SET @idx_antigo = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cadastro_atletas'
    AND INDEX_NAME = 'fk_atletas_tipo_esporte'
);
SET @sql = IF(@idx_antigo > 0,
  'ALTER TABLE `cadastro_atletas` DROP INDEX `fk_atletas_tipo_esporte`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_nova = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cadastro_atletas'
    AND INDEX_NAME = 'fk_atletas_modalidade_esporte'
);
SET @sql = IF(@idx_nova = 0,
  'ALTER TABLE `cadastro_atletas` ADD KEY `fk_atletas_modalidade_esporte` (`modalidade_esporte_id`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_nova = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cadastro_atletas'
    AND CONSTRAINT_NAME = 'fk_atletas_modalidade_esporte'
);
SET @sql = IF(@fk_nova = 0,
  'ALTER TABLE `cadastro_atletas` ADD CONSTRAINT `fk_atletas_modalidade_esporte` FOREIGN KEY (`modalidade_esporte_id`) REFERENCES `modalidades_esporte` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;
