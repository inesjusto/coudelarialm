<?php
require_once __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores</title>
    <link rel="stylesheet" href="assets/css/admin.css">
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
            <a href="fornecedores.php" class="nav-link ativo">Fornecedores</a>
            <a href="despesas.php" class="nav-link">Despesas</a>
            <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
        </nav>
    </aside>

    <main class="admin-main">

        <header class="admin-header admin-header-flex">
            <div class="admin-header-texto">
                <h1>Fornecedores</h1>
                <p>Gestão de fornecedores da coudelaria.</p>
            </div>

            <a href="adicionar-fornecedor.php" class="botao-adicionar">
                + Adicionar Fornecedor
            </a>
        </header>

        <section class="admin-tabela-wrapper">
            <div class="tabela-container">

                <table class="admin-tabela">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>NIF</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody id="tabela-fornecedores">
                        <tr>
                            <td colspan="7" class="mensagem-vazia">
                                A carregar fornecedores...
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