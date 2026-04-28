<?php
include __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Cavalos</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="/public/index.html" class="sidebar-logo-link">
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
                <a href="cavalos.php" class="nav-link ativo">Cavalos</a>
                <a href="clientes.php" class="nav-link">Clientes</a>
                <a href="logout.php" class="nav-link">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div>
                    <h1>Gestão de Cavalos</h1>
                    <p>Gerencie todos os cavalos disponíveis para venda.</p>
                </div>

                <a href="adicionar-cavalo.html" class="botao-adicionar">+ Adicionar Cavalo</a>
            </header>

            <section class="tabela-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Sexo</th>
                            <th>Idade</th>
                            <th>Raça</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-cavalos"></tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>