-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 10/01/2025 às 19:08
-- Versão do servidor: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- Versão do PHP: 8.1.2-1ubuntu2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projeto_jandira`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cadastro_atletas`
--

CREATE TABLE `cadastro_atletas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `rg` varchar(15) NOT NULL,
  `cpf` varchar(15) NOT NULL,
  `endereco` varchar(100) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `municipio` varchar(40) NOT NULL,
  `cidade` varchar(40) NOT NULL,
  `data_nascimento` date NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='registro de todos atletas de jandira';

--
-- Despejando dados para a tabela `cadastro_atletas`
--

INSERT INTO `cadastro_atletas` (`id`, `nome`, `rg`, `cpf`, `endereco`, `numero`, `cep`, `municipio`, `cidade`, `data_nascimento`, `email`, `senha`, `telefone`, `data_cadastro`) VALUES
(1, 'julio cesar de abreu', '35.234.790-9', '31183817878', 'av antonio bardella', '17', '06618000', 'jandira', 'sao paulo', '1983-02-03', 'juliocasp38@gmail.com', '', '(11) 94647-6117', '2024-12-23 14:48:07'),
(9, 'Antonio da Silva', '234569879', '31188817878', 'av antonio bardella', '2000', '06619000', 'jandira', 'sao paulo', '2000-10-12', 'antoniodasilva@gmail.com', '', '(11) 94646-6101', '2024-12-27 22:32:36'),
(10, 'samuel abreu', '123456789', '98765432101', 'av brasil', '1200', '07718000', 'sorocaba', 'são paulo', '1986-04-21', 'samuel@gmail.com', '', '(11) 94613-7985', '2024-12-29 18:50:52'),
(11, 'josefina da Silva', '35236456', '31165498721', '', '', '', '', '', '2000-10-12', 'josefina@gmail.com', '', '(11) 94646-7979', '2025-01-01 04:38:58'),
(12, 'joana fagundes', '98987654', '32265478989', 'av nazare', '4500', '06618000', 'itapevi', 'sao paulo', '1990-02-10', 'juliocasp38@gmail.com', '$2y$10$0vFoV1N5BB15FBtvWSmsYu2pHKISI0Edo8g6jHw2Y/fSblLarwDX2', '11946456118', '2025-01-05 01:52:42');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cadastro_atletas`
--
ALTER TABLE `cadastro_atletas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cadastro_atletas`
--
ALTER TABLE `cadastro_atletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
