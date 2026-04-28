<?php
include __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="dashboard.php" class="nav-link ativo">Dashboard</a>
                <a href="cavalos.php" class="nav-link">Cavalos</a>
                <a href="clientes.php" class="nav-link">Clientes</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-topo">
                    <div>
                        <h1>Painel Administrativo</h1>
                        <p>Bem-vinda, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrador'); ?>.</p>
                    </div>

                    <div class="admin-header-acoes">
                        <a href="cavalos.php" class="botao-secundario">Gerir Cavalos</a>
                        <a href="clientes.php" class="botao-principal">Gerir Clientes</a>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card destaque-verde">
                    <span class="stat-label">Total de Cavalos</span>
                    <strong class="stat-value" id="total-cavalos">0</strong>
                </div>

                <div class="stat-card destaque-azul">
                    <span class="stat-label">Total de Clientes</span>
                    <strong class="stat-value" id="total-clientes">0</strong>
                </div>
            </section>

            <section class="graficos-grid">
                <!-- CAVALOS -->
                <div class="grafico-box">
                    <div class="grafico-header">
                        <h3>Distribuição de Cavalos</h3>

                        <div class="grafico-filtros" id="filtro-cavalos">
                            <button class="filtro-btn ativo" data-value="sexo">Sexo</button>
                            <button class="filtro-btn" data-value="raca">Raça</button>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="grafico-cavalos"></canvas>
                    </div>
                </div>

                <!-- CLIENTES -->
                <div class="grafico-box">
                    <div class="grafico-header">
                        <h3>Distribuição de Clientes</h3>

                        <div class="grafico-filtros" id="filtro-clientes">
                            <button class="filtro-btn ativo" data-value="tipo">Tipo</button>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="grafico-clientes"></canvas>
                    </div>
                </div>
            </section>

            <section class="graficos-grid graficos-grid-inferior">
                <div class="grafico-box grafico-box-largo">
                    <div class="grafico-header">
                        <h3>Cavalos por Raça</h3>
                    </div>

                    <div class="chart-container chart-container-largo">
                        <canvas id="grafico-racas-detalhe"></canvas>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>