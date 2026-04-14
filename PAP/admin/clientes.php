<?php
include __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Clientes</title>
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
                <a href="cavalos.php" class="nav-link">Cavalos</a>
                <a href="clientes.php" class="nav-link ativo">Clientes</a>
                <a href="logout.php" class="nav-link">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <h1>Gestão de Clientes</h1>
                    <p>Área reservada para a futura tabela de clientes.</p>
                </div>
            </header>

            <section class="info-box">
                <h3>Módulo em preparação</h3>
                <p>Esta página foi criada para organizar a futura gestão de clientes da coudelaria. Aqui poderá ser adicionada a tabela de clientes, formulários de registo e outras funcionalidades.</p>
            </section>
        </main>
    </div>
</body>
</html>