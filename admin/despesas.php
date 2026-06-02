<?php
include __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despesas</title>
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
                <a href="vendas.php" class="nav-link">Vendas</a>
                <a href="fornecedores.php" class="nav-link">Fornecedores</a>
                <a href="despesas.php" class="nav-link ativo">Despesas</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">

            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Despesas</h1>
                    <p>
                        Gestão financeira das despesas gerais e despesas associadas aos cavalos.
                    </p>
                </div>

                <a href="adicionar-despesa.php" class="botao-adicionar">
                    + Adicionar Despesa
                </a>
            </header>

            <section class="admin-tabela-wrapper">
                <div class="tabela-container">

                    <table class="admin-tabela">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Fornecedor</th>
                                <th>Cavalo</th>
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Pagamento</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody id="tabela-despesas">
                            <tr>
                                <td colspan="10" class="mensagem-vazia">
                                    A carregar despesas...
                                </td>
                            </tr>
                        </tbody>

                    </table>

                </div>
            </section>

        </main>

    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>