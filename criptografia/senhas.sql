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
-- Estrutura para tabela `senhas`
--

CREATE TABLE `senhas` (
  `id` int(11) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `senhas`
--

INSERT INTO `senhas` (`id`, `senha`, `email`) VALUES
(1, '$2y$10$Zg9lSmjNzb6fsO4SFhwPn.cK4X.Xho8vSbSc0qaID0ycqqkTUPx3m', 'juliocasp38@gmail.com'),
(2, '$2y$10$ut6l9ni/NBM/bM1rtfNIQeEytLRgzz/I2rh3Ubf8MoMUUUgo5O8T6', 'juliocasp38@gmail.com'),
(3, '$2y$10$BwvNxAnph4tD0zPR67ei/.qBiOHSOmWqMeLp0QFAIiLP6D3RSNKxa', 'maria@gmail.com'),
(4, '$2y$10$mPCHA0PTa09leOElXzSKde./eW8BMxDAYy59OiWdh76q6isN51oNO', 'juliocasp38@gmail.com'),
(5, '$2y$10$3T3C91gxQR0FUuFYBSfJ6eWBm/w7TrUOpDj4qRebon4eThX7woAc2', 'samuel@gmail.com');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `senhas`
--
ALTER TABLE `senhas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `senhas`
--
ALTER TABLE `senhas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
