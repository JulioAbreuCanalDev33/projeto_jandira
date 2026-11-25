-- phpMyAdmin SQL Dump
-- Estrutura e dados iniciais da tabela `modalidades_esporte`
-- Projeto Jandira — gestão esportiva municipal

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE IF NOT EXISTS `modalidades_esporte` (
  `id` int(11) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='modalidades esportivas disponíveis para os atletas';

INSERT INTO `modalidades_esporte` (`id`, `nome`, `descricao`) VALUES
(1, 'Futebol', 'Futebol de campo e futsal.'),
(2, 'Judô', 'Artes marciais e combate.'),
(3, 'Vôlei', 'Vôlei de quadra e de praia.'),
(4, 'Atletismo', 'Corridas, saltos e arremessos.'),
(5, 'Basquete', 'Basquete de quadra.');

ALTER TABLE `modalidades_esporte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_modalidades_esporte_nome` (`nome`);

ALTER TABLE `modalidades_esporte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
