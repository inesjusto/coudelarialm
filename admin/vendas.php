<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

$stmt = $conn->query("
    SELECT 
        vendas_cavalos.id,
        vendas_cavalos.data_venda,
        vendas_cavalos.valor,
        vendas_cavalos.metodo_pagamento,
        clientes.nome AS cliente_nome,
        cavalos.nome AS cavalo_nome
    FROM vendas_cavalos
    INNER JOIN clientes ON clientes.id = vendas_cavalos.cliente_id
    INNER JOIN cavalos ON cavalos.id = vendas_cavalos.cavalo_id
    ORDER BY vendas_cavalos.data_venda DESC, vendas_cavalos.id DESC
");

$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatarDataVenda($data) {
    if (empty($data)) {
        return '-';
    }

    return date('d/m/Y', strtotime($data));
}

function formatarValorVenda($valor) {
    return number_format((float) $valor, 2, ',', '.') . ' €';
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas de Cavalos</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="../public/index.html" class="sidebar-logo-link">
                <div class="sidebar-logo">
                    <img src="assets/img/logo.png" alt="Logo da Coudelaria">

                    <div class="sidebar-titulo">
                        <h2>Coudelaria</h2>
                        <h3>Lima Monteiro</h3>
                    </div>
                </div>
            </a>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="cavalos.php" class="nav-link">Cavalos</a>
                <a href="clientes.php" class="nav-link">Clientes</a>
                <a href="alugueres.php" class="nav-link">Alugueres</a>
                <a href="aulas.php" class="nav-link">Aulas</a>
                <a href="vendas.php" class="nav-link ativo">Vendas</a>
                <a href="fornecedores.php" class="nav-link">Fornecedores</a>
                <a href="despesas.php" class="nav-link">Despesas</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Vendas de Cavalos</h1>
                    <p>Consulte as vendas realizadas e gere a fatura de cada cavalo vendido.</p>
                </div>

                <a href="adicionar-venda.php" class="botao-adicionar">+ Adicionar Venda</a>
            </header>

            <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'venda_criada'): ?>
                <p class="mensagem-formulario sucesso">
                    Venda registada com sucesso.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['erro'])): ?>
                <p class="mensagem-formulario erro">
                    Ocorreu um erro ao carregar a venda.
                </p>
            <?php endif; ?>

            <section class="tabela-container">
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Cavalo</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Método</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($vendas) === 0): ?>
                            <tr>
                                <td colspan="7" class="mensagem-vazia">
                                    Ainda não existem vendas registadas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vendas as $venda): ?>
                                <tr>
                                    <td><?= htmlspecialchars($venda['id']) ?></td>
                                    <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                                    <td><?= htmlspecialchars($venda['cavalo_nome']) ?></td>
                                    <td><?= htmlspecialchars(formatarDataVenda($venda['data_venda'])) ?></td>
                                    <td><?= htmlspecialchars(formatarValorVenda($venda['valor'])) ?></td>
                                    <td>
                                        <?= !empty($venda['metodo_pagamento'])
                                            ? htmlspecialchars($venda['metodo_pagamento'])
                                            : '-'
                                        ?>
                                    </td>
                                    <td>
                                        <a
                                            href="fatura-venda.php?id=<?= htmlspecialchars($venda['id']) ?>"
                                            class="btn-editar"
                                            target="_blank"
                                        >
                                            Fatura PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>