-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 10/01/2025 às 19:07
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
-- Estrutura para tabela `arquivos`
--

CREATE TABLE `arquivos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `data_upload` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `arquivos`
--

INSERT INTO `arquivos` (`id`, `nome`, `path`, `data_upload`) VALUES
(2, 'cadastrar_atleta.png', 'arquivos/6779b9a048e04.png', '2025-01-04 19:43:44'),
(6, 'listas_de_atletas.png', 'arquivos/6779bd64f098c.png', '2025-01-04 19:59:48'),
(10, 'php_logo.jpeg', 'arquivos/677c861d81c40.jpeg', '2025-01-06 22:40:45');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `arquivos`
--
ALTER TABLE `arquivos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `arquivos`
--
ALTER TABLE `arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
