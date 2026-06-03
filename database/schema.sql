-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 03-Jun-2026 às 12:29
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `coudelarialm`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `administradores`
--

INSERT INTO `administradores` (`id`, `username`, `password`) VALUES
(1, 'Direção', '$2y$10$poUIuvF41F/LIbZCdmg1n.lns9YNcQb/bYNrM004IBqbFDwTL80aS'),
(2, 'Gestor', '$2y$10$7ZjHyYzBkc7pKaMQEt92LOYcIn5TxYTARDAs0jTq0tYYkva540TjS');

-- --------------------------------------------------------

--
-- Estrutura da tabela `alugueres`
--

CREATE TABLE `alugueres` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cavalo_id` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `preco_diario` decimal(10,2) DEFAULT 0.00,
  `estado` enum('reservado','ativo','concluido','cancelado') NOT NULL DEFAULT 'reservado',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `aulas`
--

CREATE TABLE `aulas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `cavalo_id` int(11) DEFAULT NULL,
  `data_aula` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `tipo_aula` varchar(100) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estado` enum('marcada','realizada','cancelada') DEFAULT 'marcada',
  `observacoes` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cavalos`
--

CREATE TABLE `cavalos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `raca` varchar(100) NOT NULL,
  `sexo` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `altura` decimal(3,2) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `cavalos`
--

INSERT INTO `cavalos` (`id`, `nome`, `raca`, `sexo`, `data_nascimento`, `altura`, `cor`, `preco`, `estado`, `descricao`, `imagem`, `criado_em`) VALUES
(1, 'Névoa do Monte', 'Puro Sangue Lusitano', 'Fêmea', '2017-05-14', 1.62, 'Tordilha Rodada', 8500.00, 'Disponível', 'Égua elegante e expressiva, de pelagem tordilha rodada, com porte nobre e presença tranquila. Ideal para lazer, ensino ou trabalho clássico, destacando-se pela beleza e equilíbrio.', 'cavalo_6a1fe8e3418856.75343272.webp', '2026-06-03 08:42:12'),
(2, 'Rubi da Serra', 'Cruzado Português', 'Macho', '2016-03-22', 1.60, 'Baia', 6750.00, 'Disponível', 'Cavalo baio de presença forte e temperamento calmo, habituado ao campo e de fácil maneio. Uma excelente opção para lazer, passeios e trabalho ligeiro.', 'cavalo_6a1fe925cd42f1.93457711.webp', '2026-06-03 08:43:19'),
(3, 'Dourado do Vale', 'Cavalo do Sorraia', 'Garanhão', '2018-06-09', 1.55, 'Baia Amarilha', 7900.00, 'Disponível', 'Cavalo de porte atlético e expressão marcante, com pelagem baia amarilha e crinas escuras. Destaca-se pelo movimento solto e pela presença elegante, ideal para lazer ou trabalho em picadeiro.', 'cavalo_6a1fe9950c8620.91863991.webp', '2026-06-03 08:45:10'),
(4, 'Aurora do Prado', 'Cruzado Português', 'Fêmea', '2024-04-18', 1.32, 'Baia Amarilha', 3200.00, 'Reservado', 'Poldra jovem e encantadora, de expressão doce e pelagem clara com crinas escuras. Promete bom desenvolvimento e destaca-se pelo temperamento tranquilo e curioso.', 'cavalo_6a1fe9cd9e6d75.96811664.webp', '2026-06-03 08:46:07'),
(5, 'Castor do Bosque', 'Mangalarga Marchador', 'Macho', '2015-07-27', 1.54, 'Alazã Tostada', 5800.00, 'Indisponível', 'Cavalo robusto e sereno, de pelagem alazã tostada e expressão tranquila. Ideal para quem procura um animal de confiança, com presença forte e temperamento equilibrado.', 'cavalo_6a1fea302dd8b2.31692046.webp', '2026-06-03 08:47:46'),
(6, 'Fidalgo do Pinhal', 'Haflinger', 'Macho', '2023-02-11', 1.38, 'Alazã Amarilha', 2950.00, 'Em Tratamento', 'Jovem cavalo de expressão meiga e pelagem clara, com marca branca destacada na face. Encontra-se em tratamento, mantendo um temperamento dócil e curioso.', 'cavalo_6a1feb3896d239.50541509.webp', '2026-06-03 08:52:10'),
(7, 'Ventania da Herdade', 'Appaloosa', 'Fêmea', '2019-05-06', 1.57, 'Rosilha', 7250.00, 'Disponível', 'Égua enérgica e elegante, com pelagem rosilha marcada e movimento expressivo. Destaca-se pela presença no campo e pelo temperamento atento, ideal para lazer ou trabalho ligeiro.', 'cavalo_6a1fed0a3b7876.85238318.webp', '2026-06-03 08:59:55'),
(8, 'Melodia do Sol', 'Palomino', 'Fêmea', '2017-08-19', 1.52, 'Palomina', 6400.00, 'Disponível', 'Égua de pelagem dourada e crinas claras, com expressão doce e porte harmonioso. Destaca-se pela calma e beleza natural, ideal para lazer e passeios.', 'cavalo_6a1fed72a4cd49.41589020.webp', '2026-06-03 09:01:39'),
(9, 'Sultão da Vinha', 'Cruzado Português', 'Macho', '2011-09-03', 1.61, 'Rosilha', 2500.00, 'Reformado', 'Cavalo experiente e tranquilo, de pelagem rosilha e porte distinto. Ideal para companhia ou vida em campo, com presença serena e olhar dócil.', 'cavalo_6a1fedc38717d7.07688426.webp', '2026-06-03 09:03:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `nif` varchar(20) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `tipo_interesse` varchar(50) DEFAULT 'compra',
  `estado` varchar(50) DEFAULT 'potencial',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`, `nif`, `mensagem`, `tipo_interesse`, `estado`, `criado_em`) VALUES
(1, 'Mariana Silva', 'mariana.silva@email.com', '912345678', NULL, '', 'compra', 'potencial', '2026-06-03 09:06:02'),
(2, 'João Martins', 'joao.martins@email.com', '934567890', NULL, '', 'informacao', 'contactado', '2026-06-03 09:06:34'),
(3, 'Beatriz Costa', 'beatriz.costa@email.com', '919876543', NULL, '', 'visita', 'potencial', '2026-06-03 09:07:22'),
(4, 'Tiago Ferreira', 'tiago.ferreira@email.com', '936789012', '245789123', '', 'compra', 'cliente', '2026-06-03 09:07:57'),
(5, 'Inês Rodrigues', 'ines.rodrigues@email.com', '927654321', '289456732', '', 'compra', 'cliente', '2026-06-03 09:08:27'),
(6, 'Miguel Almeida', 'miguel.almeida@email.com', '914328765', '256987341', '', 'compra', 'cliente', '2026-06-03 09:08:50');

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes_cavalos`
--

CREATE TABLE `clientes_cavalos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cavalo_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `clientes_cavalos`
--

INSERT INTO `clientes_cavalos` (`id`, `cliente_id`, `cavalo_id`, `criado_em`) VALUES
(2, 1, 7, '2026-06-03 09:06:41'),
(3, 3, 3, '2026-06-03 09:07:22');

-- --------------------------------------------------------

--
-- Estrutura da tabela `consumos_cavalos`
--

CREATE TABLE `consumos_cavalos` (
  `id` int(11) NOT NULL,
  `cavalo_id` int(11) NOT NULL,
  `tipo_consumo` varchar(100) NOT NULL,
  `consumo_diario` decimal(10,2) NOT NULL,
  `unidade` varchar(20) DEFAULT 'kg',
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `dias` int(11) NOT NULL,
  `quantidade_total` decimal(10,2) NOT NULL,
  `quantidade_por_embalagem` decimal(10,2) DEFAULT NULL,
  `preco_embalagem` decimal(10,2) DEFAULT NULL,
  `embalagens_necessarias` int(11) DEFAULT NULL,
  `custo_total` decimal(10,2) DEFAULT NULL,
  `data_registo` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `despesas`
--

CREATE TABLE `despesas` (
  `id` int(11) NOT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `cavalo_id` int(11) DEFAULT NULL,
  `categoria` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_despesa` date NOT NULL,
  `metodo_pagamento` varchar(50) DEFAULT NULL,
  `estado_pagamento` enum('pago','pendente','cancelado') DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nif` varchar(20) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `morada` text DEFAULT NULL,
  `tipo_fornecedor` varchar(100) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id`, `nome`, `nif`, `telefone`, `email`, `morada`, `tipo_fornecedor`, `observacoes`, `data_criacao`) VALUES
(1, 'AgroRação Lusitana', '514782936', '912345678', 'geral@agroracaolusitana.pt', 'Rua das Quintas, nº 24, 2000-456 Santarém', 'Alimentação', NULL, '2026-06-03 09:28:41'),
(2, 'Palhas do Ribatejo', '508193742', '934876210', 'encomendas@palhasdoribatejo.pt', 'Estrada Nacional 118, km 42, 2130-120 Benavente', 'Palha', NULL, '2026-06-03 09:29:14'),
(3, 'Fenos & Campos', '516209873', '962114589', 'geral@fenosecampos.pt', 'Rua do Campo Verde, nº 7, 7350-203 Elvas', 'Feno', NULL, '2026-06-03 09:29:43'),
(4, 'Clínica Veterinária EquusCare', '507648219', '913908456', 'clinica@equuscare.pt', 'Avenida dos Animais, nº 15, 7005-321 Évora', 'Veterinário', NULL, '2026-06-03 09:30:52'),
(5, 'Ferrador João Martins', '221459876', '919 332 145', 'joao.martins.ferrador@gmail.com', 'Rua da Ferradura, nº 10, 2040-112 Rio Maior', 'Ferrador', NULL, '2026-06-03 09:31:16'),
(6, 'FarmaEquina Portugal', '515734982', '961778234', 'vendas@farmaequina.pt', 'Rua da Saúde Animal, nº 18, 2560-098 Torres Vedras', 'Medicamentos', NULL, '2026-06-03 09:31:42'),
(7, 'EquiStore Equipamentos', '517293640', '914555902', 'apoio@equistore.pt', 'Rua dos Arreios, nº 32, 4705-145 Braga', 'Equipamento', NULL, '2026-06-03 09:33:01');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas_cavalos`
--

CREATE TABLE `vendas_cavalos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cavalo_id` int(11) NOT NULL,
  `data_venda` date NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `metodo_pagamento` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Índices para tabela `alugueres`
--
ALTER TABLE `alugueres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `cavalo_id` (`cavalo_id`);

--
-- Índices para tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `cavalo_id` (`cavalo_id`);

--
-- Índices para tabela `cavalos`
--
ALTER TABLE `cavalos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `clientes_cavalos`
--
ALTER TABLE `clientes_cavalos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cliente_cavalo_unico` (`cliente_id`,`cavalo_id`),
  ADD KEY `cavalo_id` (`cavalo_id`);

--
-- Índices para tabela `consumos_cavalos`
--
ALTER TABLE `consumos_cavalos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cavalo_id` (`cavalo_id`);

--
-- Índices para tabela `despesas`
--
ALTER TABLE `despesas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fornecedor_id` (`fornecedor_id`),
  ADD KEY `fk_despesas_cavalo` (`cavalo_id`);

--
-- Índices para tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `vendas_cavalos`
--
ALTER TABLE `vendas_cavalos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cavalo_vendido` (`cavalo_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `alugueres`
--
ALTER TABLE `alugueres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cavalos`
--
ALTER TABLE `cavalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `clientes_cavalos`
--
ALTER TABLE `clientes_cavalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `consumos_cavalos`
--
ALTER TABLE `consumos_cavalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `vendas_cavalos`
--
ALTER TABLE `vendas_cavalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `alugueres`
--
ALTER TABLE `alugueres`
  ADD CONSTRAINT `alugueres_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alugueres_ibfk_2` FOREIGN KEY (`cavalo_id`) REFERENCES `cavalos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `aulas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `aulas_ibfk_2` FOREIGN KEY (`cavalo_id`) REFERENCES `cavalos` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `clientes_cavalos`
--
ALTER TABLE `clientes_cavalos`
  ADD CONSTRAINT `clientes_cavalos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clientes_cavalos_ibfk_2` FOREIGN KEY (`cavalo_id`) REFERENCES `cavalos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `consumos_cavalos`
--
ALTER TABLE `consumos_cavalos`
  ADD CONSTRAINT `consumos_cavalos_ibfk_1` FOREIGN KEY (`cavalo_id`) REFERENCES `cavalos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `despesas`
--
ALTER TABLE `despesas`
  ADD CONSTRAINT `despesas_ibfk_1` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_despesas_cavalo` FOREIGN KEY (`cavalo_id`) REFERENCES `cavalos` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `vendas_cavalos`
--
ALTER TABLE `vendas_cavalos`
  ADD CONSTRAINT `vendas_cavalos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `vendas_cavalos_ibfk_2` FOREIGN KEY (`cavalo_id`) REFERENCES `cavalos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
