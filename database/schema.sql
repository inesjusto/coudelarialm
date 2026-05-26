-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 26-Maio-2026 às 15:48
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
(1, 'Direção', '$2y$10$alwMYEzh4hevjGj8fkctYelsRfBdjZU2QFtjQB.BYOziMKYT.TJ2m'),
(2, 'Gestor', '$2y$10$xWCL1AHQA7oAaLQurbNm4.9Nm5al6LEwhTGVMqNh4EtBpQ29sdqmu');

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
  `estado` enum('ativo','concluido','cancelado') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `alugueres`
--

INSERT INTO `alugueres` (`id`, `cliente_id`, `cavalo_id`, `data_inicio`, `data_fim`, `preco_diario`, `estado`, `criado_em`) VALUES
(1, 10, 10, '2026-05-26', '2026-06-26', 20.00, 'ativo', '2026-05-26 10:38:14'),
(2, 4, 17, '2026-05-01', '2026-07-01', 25.00, 'ativo', '2026-05-26 10:42:45');

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

--
-- Extraindo dados da tabela `aulas`
--

INSERT INTO `aulas` (`id`, `cliente_id`, `cavalo_id`, `data_aula`, `hora_inicio`, `hora_fim`, `tipo_aula`, `preco`, `estado`, `observacoes`, `data_criacao`) VALUES
(1, 12, 4, '2026-05-20', '17:00:00', '18:00:00', 'Avançada', 25.00, 'realizada', NULL, '2026-05-26 11:37:58');

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
(1, 'Storm Wind', 'Mustang', 'Macho', '2018-03-14', 1.58, 'Tordilha', 7500.00, 'Disponível', 'Cavalo resistente e muito adaptável, com excelente capacidade para trilhos e terrenos irregulares. Possui um temperamento equilibrado, sendo atento e fácil de conduzir. A pelagem tordilha acinzentada juntamente com a crina escura dá-lhe uma aparência marcante e elegante.', 'cavalo_6a0d7103d82f83.60418752.jpg', '2026-05-20 08:29:55'),
(2, 'Thunder Blaze', 'Quarto de Milha', 'Garanhão', '2016-05-22', 1.63, 'Castanha', 11900.00, 'Reservado', 'Cavalo forte e musculado, com excelente desempenho em trabalho de campo e provas de velocidade. Demonstra um comportamento obediente e seguro, sendo indicado tanto para cavaleiros experientes como intermédios. A sua pelagem castanha brilhante e a boa condição física destacam-no pela elegância e potência.', 'cavalo_6a0d71914d1089.52294933.jpg', '2026-05-20 08:32:17'),
(3, 'Golden Spirit', 'Puro Sangue Lusitano', 'Fêmea', '2024-04-03', 0.95, 'Baia', 4200.00, 'Em Tratamento', 'Potra jovem de raça Lusitana com excelente estrutura corporal e aparência elegante. Demonstra um comportamento dócil e curioso, habituando-se facilmente ao contacto humano. A pelagem baia clara combinada com a marca branca na face dá-lhe um aspeto muito distinto. Atualmente encontra-se em acompanhamento veterinário preventivo para garantir um crescimento saudável e equilibrado.', 'cavalo_6a0d720b6db424.39327934.jpg', '2026-05-20 08:34:19'),
(4, 'Imperial do Vale', 'Puro Sangue Lusitano', 'Garanhão', '2015-09-11', 1.66, 'Tordilha', 18500.00, 'Disponível', 'Garanhão Lusitano de excelente presença e movimentos harmoniosos, ideal para dressage e ensino clássico. Demonstra um temperamento calmo, inteligente e muito cooperativo no trabalho diário. A pelagem tordilha clara destaca a sua elegância natural e porte atlético, sendo um cavalo muito apreciado em apresentações equestres.', 'cavalo_6a0d72a0001bb9.68689977.jpg', '2026-05-20 08:36:48'),
(5, 'Bronze River', 'Puro Sangue Inglês', 'Macho', '2017-05-22', 1.64, 'Castanha', 9800.00, 'Indisponível', 'Cavalo atlético e elegante, com boa resistência física e movimentos leves. Demonstra um comportamento tranquilo no maneio diário, mantendo energia e rapidez durante o exercício. Ideal para passeios longos, treino de salto e trabalho desportivo ligeiro. A pelagem castanha uniforme e a pequena marca branca na testa conferem-lhe um aspeto refinado e harmonioso.', 'cavalo_6a0d73057e5c10.44889231.jpg', '2026-05-20 08:38:29'),
(6, 'Sierra Dust', 'Paint Horse', 'Fêmea', '2019-08-17', 1.52, 'Pampa', 8600.00, 'Vendido', 'Égua elegante e resistente, conhecida pela sua pelagem pampa castanha e branca bastante marcante. Possui um temperamento dócil e cooperativo, sendo excelente para passeios, lazer e trabalho ligeiro. Demonstra movimentos suaves e boa adaptação a ambientes abertos e trilhos naturais.', 'cavalo_6a0d737ea87288.15318294.jpg', '2026-05-20 08:40:30'),
(7, 'Shadow Comet', 'Andaluz', 'Garanhão', '2018-02-28', 1.65, 'Castanha Escura', 16700.00, 'Disponível', 'Garanhão Andaluz de grande elegância e presença forte, com movimentos enérgicos e excelente impulsão. Demonstra um temperamento vivo, mas muito inteligente e obediente durante o treino. Ideal para dressage, apresentações e passeios de alto nível. A pelagem castanha escura brilhante combinada com a crina negra longa destaca o seu aspeto nobre e atlético.', 'cavalo_6a0d73e1e8ca99.34697968.jpg', '2026-05-20 08:42:09'),
(8, 'Silver Meadow', 'Outro', 'Fêmea', '2016-06-09', 1.55, 'Tordilha', 10400.00, 'Reformado', 'Égua tranquila e muito resistente, conhecida pelo seu comportamento dócil e experiência em passeios rurais. Possui uma pelagem tordilha clara com ligeiras marcações acinzentadas, típica da raça Camargue. Apesar de já reformada de trabalhos intensivos, continua apta para atividades leves e contacto com iniciantes, destacando-se pela serenidade e elegância natural.', 'cavalo_6a0d74724a1a39.95329093.jpg', '2026-05-20 08:44:34'),
(9, 'Misty Thunder', 'Outro', 'Macho', '2018-11-14', 1.47, 'Tordilha Negra', 6900.00, 'Disponível', 'Cavalo compacto e resistente, com excelente adaptação a terrenos difíceis e grande facilidade em trilhos longos. Demonstra um temperamento calmo e amigável, sendo ideal para lazer e equitação recreativa. A pelagem tordilha negra juntamente com a crina clara cria um aspeto muito distinto e elegante. Conhecido pela sua resistência física e facilidade de aprendizagem.', 'cavalo_6a0d74df8bfcc7.81850150.jpg', '2026-05-20 08:46:23'),
(10, 'Autumn Flame', 'Paint Horse', 'Macho', '2022-04-05', 1.42, 'Pampa', 7300.00, 'Alugado', 'Cavalo jovem com excelente porte e uma pelagem pampa castanha muito marcante. Demonstra curiosidade, energia e facilidade de adaptação ao treino inicial. Possui movimentos leves e boa estrutura física, sendo promissor para lazer, trilhos e equitação recreativa. A larga lista branca na face e as marcas brancas nas patas dão-lhe um aspeto bastante distinto e elegante.', 'cavalo_6a0d754edabc65.27369517.jpg', '2026-05-20 08:48:14'),
(11, 'Desert Echo', 'Mustang', 'Garanhão', '2019-07-30', 1.57, 'Castanha', 12300.00, 'Disponível', 'Garanhão de grande energia e presença forte, com movimentos rápidos e excelente agilidade. Demonstra um temperamento vivo, mas equilibrado, adaptando-se bem a treino e atividades de resistência. A pelagem escura com reflexos acastanhados destaca a musculatura e o porte atlético. Ideal para trilhos, equitação western e trabalho em campo aberto.', 'cavalo_6a0d75b06a14e6.32768765.jpg', '2026-05-20 08:49:52'),
(12, 'Golden Horizon', 'Akhal-Teke', 'Fêmea', '2020-05-18', 1.60, 'Outra', 21500.00, 'Reservado', 'Égua elegante e muito refinada, conhecida pela sua resistência e aparência distinta. Possui uma pelagem isabela dourada com brilho natural característico da raça Akhal-Teke. Demonstra um comportamento atento e inteligente, com movimentos suaves e excelente desempenho em equitação desportiva e trilhos longos. Destaca-se pela postura nobre e pela grande agilidade.', 'cavalo_6a0d762e5998e5.74851235.jpg', '2026-05-20 08:51:58'),
(13, 'Iron Dust', 'Mustang', 'Macho', '2019-10-12', 1.61, 'Castanha', 13800.00, 'Disponível', 'Cavalo robusto e atlético, com excelente resistência e postura firme. Demonstra um temperamento atento e energético, mantendo boa obediência durante o treino e passeios. Indicado para trilhos, trabalho western e atividades de resistência.', 'cavalo_6a0d769bc75d84.38784432.jpg', '2026-05-20 08:53:47'),
(14, 'Silver Rain', 'Andaluz', 'Fêmea', '2018-03-26', 1.64, 'Tordilha', 15900.00, 'Disponível', 'Égua Andaluz de aparência elegante e porte harmonioso, destacando-se pela sua pelagem tordilha salpicada e movimentos suaves. Possui um temperamento dócil e atento, sendo indicada para dressage, lazer e apresentações equestres. Demonstra excelente equilíbrio corporal e facilidade de aprendizagem, tornando-se uma ótima escolha para cavaleiros intermédios e experientes.', 'cavalo_6a0d770539caa0.86277734.jpg', '2026-05-20 08:55:33'),
(15, 'Forest King', 'Hanoveriano', 'Macho', '2017-09-07', 1.68, 'Castanha', 14600.00, 'Disponível', 'Cavalo Hanoveriano de porte forte e musculatura bem desenvolvida, ideal para saltos e ensino desportivo. Possui um temperamento equilibrado e tranquilo, demonstrando boa obediência e facilidade no maneio diário. A pelagem castanha brilhante com crina e cauda negras destaca a sua aparência elegante e atlética. Excelente escolha para cavaleiros que procuram resistência, potência e conforto nos movimentos.', 'cavalo_6a0d777b62beb9.60714314.jpg', '2026-05-20 08:57:31'),
(16, 'Northern Spirit', 'Outro', 'Garanhão', '2018-04-21', 1.50, 'Baia', 13200.00, 'Disponível', 'Garanhão de porte compacto e musculatura forte, típico da raça Fiorde Norueguês. Possui uma pelagem baia dourada com crina bicolor escura, característica muito valorizada na raça. Demonstra um temperamento dócil, resistente e cooperativo, sendo excelente para lazer, trilhos e trabalho rural. Destaca-se pela elegância natural e pela grande resistência física.', 'cavalo_6a0d77c263a6f8.71386815.jpg', '2026-05-20 08:58:42'),
(17, 'Copper Wind', 'Árabe', 'Fêmea', '2021-06-15', 1.54, 'Alazã', 17400.00, 'Alugado', 'Égua jovem de raça Árabe com excelente elegância e estrutura refinada. Possui uma pelagem alazã uniforme e brilhante, destacando-se pela postura nobre e movimentos leves. Demonstra um temperamento atento, inteligente e muito ágil, sendo ideal para resistência, lazer e apresentações equestres. A sua conformação atlética e expressão alerta tornam-na bastante valorizada.', 'cavalo_6a0d780a81ed43.76445741.jpg', '2026-05-20 08:59:54');

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `tipo_interesse` varchar(50) DEFAULT 'compra',
  `estado` varchar(50) DEFAULT 'potencial',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`, `mensagem`, `tipo_interesse`, `estado`, `criado_em`) VALUES
(1, 'Ricardo Almeida', 'ricardo.almeida87@gmail.com', '+351 913 482 771', '', 'compra', 'potencial', '2026-05-20 10:34:27'),
(2, 'Mariana Lopes', 'mariana.lopes.equestre@outlook.com', '+351 926 140 553', '', 'visita', 'contactado', '2026-05-20 10:35:09'),
(3, 'Tiago Ferreira', 'tiagoferreira.cf@hotmail.com', '+351 918 662 104', '', 'informacao', 'potencial', '2026-05-20 10:35:50'),
(4, 'Beatriz Cardoso', 'beatriz.cardoso22@gmail.com', '+351 936 517 820', '', 'compra', 'cliente', '2026-05-20 10:36:20'),
(5, 'Hugo Martins', 'hugomartins.ranch@mail.com', '+351 912 904 445', '', 'visita', 'contactado', '2026-05-20 10:36:52'),
(6, 'Catarina Neves', 'catarina.neves.eq@gmail.com', '+351 927 315 991', '', 'informacao', 'potencial', '2026-05-20 10:37:26'),
(7, 'João Figueiredo', 'joao.figueiredo91@outlook.pt', '+351 914 770 268', '', 'compra', 'cliente', '2026-05-20 10:38:00'),
(8, 'Leonor Baptista', 'leonor.baptista.equine@gmail.com', '+351 932 884 510', '', 'visita', 'potencial', '2026-05-20 10:38:31'),
(9, 'Sofia Mendes', 'sofia.mendes98@gmail.com', '+351 917 408 226', '', 'compra', 'cliente', '2026-05-20 10:39:20'),
(10, 'André Ribeiro', 'andre.ribeiro.eq@outlook.com', '+351 924 661 380', '', 'compra', 'cliente', '2026-05-20 10:39:49'),
(11, 'Inês Carvalho', 'inescarvalho.equine@gmail.com', '+351 932 770 441', '', 'compra', 'cliente', '2026-05-20 10:40:18'),
(12, 'Duarte Silva', 'duarte.silva.trails@hotmail.com', '+351 915 804 197', '', 'compra', 'cliente', '2026-05-20 10:40:51'),
(13, 'Marta Correia', 'martacorreia.eq@mail.com', '+351 926 113 502', '', 'compra', 'cliente', '2026-05-20 10:41:14'),
(14, 'Gonçalo Pires', 'goncalopires91@gmail.com', '+351 912 447 818', '', 'compra', 'cliente', '2026-05-20 10:41:39');

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
(1, 1, 4, '2026-05-20 10:34:27'),
(2, 1, 14, '2026-05-20 10:34:27'),
(3, 2, 12, '2026-05-20 10:35:09'),
(4, 4, 10, '2026-05-20 10:36:20'),
(5, 5, 11, '2026-05-20 10:36:52'),
(6, 5, 13, '2026-05-20 10:36:52'),
(7, 6, 16, '2026-05-20 10:37:26'),
(8, 7, 2, '2026-05-20 10:38:00'),
(9, 8, 17, '2026-05-20 10:38:31'),
(10, 8, 8, '2026-05-20 10:38:31'),
(11, 9, 12, '2026-05-20 10:39:20'),
(12, 10, 15, '2026-05-20 10:39:49'),
(13, 12, 5, '2026-05-20 10:40:51'),
(14, 12, 6, '2026-05-20 10:40:51'),
(15, 13, 10, '2026-05-20 10:41:14'),
(16, 14, 9, '2026-05-20 10:41:39');

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

--
-- Extraindo dados da tabela `despesas`
--

INSERT INTO `despesas` (`id`, `fornecedor_id`, `cavalo_id`, `categoria`, `descricao`, `valor`, `data_despesa`, `metodo_pagamento`, `estado_pagamento`, `data_criacao`) VALUES
(1, 8, NULL, 'Manutenção', NULL, 150.00, '2026-05-25', 'Automático', 'pago', '2026-05-26 11:20:23'),
(2, 7, 16, 'Medicamentos', NULL, 70.00, '2026-05-30', 'Transferência', 'pendente', '2026-05-26 11:27:16');

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
(1, 'AgroFeno Ibérica', '514872390', '+351 242 881 304', 'geral@agrofenoiberica.pt', 'Zona Industrial de Santarém, Lote 14, 2005-002 Santarém', 'Feno', 'Entregas semanais para grandes encomendas.', '2026-05-26 11:03:29'),
(2, 'EquiVet Clínica Veterinária', '509334187', '+351 918 440 772', 'atendimento@equivet.pt', 'Rua das Cavalariças nº 18, 7000-221 Évora', 'Veterinário', 'Serviço de urgência 24h disponível.', '2026-05-26 11:04:12'),
(3, 'Ferragens do Casco', '517903621', '+351 927 118 540', 'ferrador.cascos@gmail.com', 'Avenida Rural 55, 2100-144 Coruche', 'Ferrador', 'Especializado em ferraduras ortopédicas.', '2026-05-26 11:04:45'),
(4, 'NutriHorse Portugal', '513287044', '+351 932 774 608', 'vendas@nutrihorse.pt', 'Rua do Prado nº 9, 3810-455 Aveiro', 'Alimentação', NULL, '2026-05-26 11:05:13'),
(5, 'Transporte Equestre Almeida', '516490832', '+351 914 228 330', 'transportes.almeida@outlook.pt', 'Estrada Nacional 4, km 92, 7050-302 Montemor-o-Novo', 'Transporte', 'Transporte nacional e internacional de cavalos.', '2026-05-26 11:05:44'),
(6, 'Palhas & Companhia', '510228971', '+351 936 005 194', 'encomendas@palhasecompanhia.pt', 'Rua da Fonte Nova 73, 2300-601 Tomar', 'Palha', NULL, '2026-05-26 11:06:10'),
(7, 'EquiMed Farma', '519004572', '+351 919 660 287', 'suporte@equimedfarma.pt', 'Parque Empresarial do Ribatejo, Lote 7, 2080-101 Almeirim', 'Medicamentos', NULL, '2026-05-26 11:06:46'),
(8, 'Lusoequestre Equipamentos', '515772144', '+351 926 511 432', 'comercial@lusoequestre.pt', 'Rua da Feira nº 31, 4750-803 Barcelos', 'Equipamento', 'Especializado em selas, cabeçadas e acessórios.', '2026-05-26 11:07:19'),
(9, 'RuralFix Serviços', '518337905', '+351 912 345 880', 'ruralfix.manutencao@gmail.com', 'Caminho das Oliveiras, 8600-421 Lagos', 'Manutenção', 'Reparação de cercas, boxes e estruturas equestres.', '2026-05-26 11:07:54');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `cavalos`
--
ALTER TABLE `cavalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `clientes_cavalos`
--
ALTER TABLE `clientes_cavalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `consumos_cavalos`
--
ALTER TABLE `consumos_cavalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
