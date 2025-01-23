-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 23/01/2025 às 12:47
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
  `foto` varchar(100) NOT NULL,
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

INSERT INTO `cadastro_atletas` (`id`, `foto`, `nome`, `rg`, `cpf`, `endereco`, `numero`, `cep`, `municipio`, `cidade`, `data_nascimento`, `email`, `senha`, `telefone`, `data_cadastro`) VALUES
(18, 'arquivos/67922595c2c6f.jpeg', 'antonio velasque', '123564454', '32265478989', 'rua brasil', '2100', '06618000', 'itapevi', 'sao paulo', '1990-02-10', 'canaldev33@gmail.com', '$2y$10$nZaKJLeA9pcfSourxdspR.aNLlMLicfUTOJAD7OpJlhMVpjfFQYpC', '11946476117', '2025-01-23 08:18:45'),
(19, 'arquivos/679225dd453a1.png', 'marcos antonio velasques', '33234790', '32265498712', 'av nazare', '4500', '06618000', 'Jandira', 'Sao paulo', '1990-02-10', 'canaldev33@gmail.com', '$2y$10$alaUbc32WrPhfRtMVmsgneHxGMVQ5R1FC5y6nQ5qLrGGQ20036dg.', '11946476117', '2025-01-23 08:19:57');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
