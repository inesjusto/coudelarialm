<?php
require_once __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
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
    <a href="clientes.php" class="nav-link ativo">Clientes</a>
    <a href="alugueres.php" class="nav-link">Alugueres</a>
    <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
</nav>
    </aside>

    <main class="admin-main">
        <header class="admin-header admin-header-flex">
            <div class="admin-header-texto">
                <h1>Clientes</h1>
                <p>Gestão de clientes registados.</p>
            </div>

            <a href="adicionar-cliente.php" class="botao-adicionar">+ Adicionar Cliente</a>
        </header>

        <section class="admin-tabela-wrapper">
            <div class="tabela-container">
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Tipo de Interesse</th>
                            <th>Estado</th>
                            <th>Cavalos</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody id="tabela-clientes">
                        <tr>
                            <td colspan="8" class="mensagem-vazia">A carregar clientes...</td>
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