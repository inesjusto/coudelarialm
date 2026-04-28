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
                        <p>Bem-vinda, <?php echo htmlspecialchars($_SESSION['admin_nome'] ?? 'Administrador'); ?>.</p>
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
                    <span class="stat-extra">Registos disponíveis no sistema</span>
                </div>

                <div class="stat-card destaque-azul">
                    <span class="stat-label">Total de Clientes</span>
                    <strong class="stat-value" id="total-clientes">0</strong>
                    <span class="stat-extra">Contactos e entidades registadas</span>
                </div>

                <a href="cavalos.php" class="stat-card stat-card-link">
                    <span class="stat-label">Gestão</span>
                    <strong class="stat-value stat-link-title">Cavalos</strong>
                    <span class="stat-extra">Adicionar, editar e apagar</span>
                </a>

                <a href="clientes.php" class="stat-card stat-card-link">
                    <span class="stat-label">Gestão</span>
                    <strong class="stat-value stat-link-title">Clientes</strong>
                    <span class="stat-extra">Consultar e organizar registos</span>
                </a>
            </section>

            <section class="graficos-grid">
                <div class="grafico-box">
                    <div class="grafico-header">
                        <h3>Distribuição de Cavalos</h3>
                        <select id="filtro-cavalos" class="grafico-select">
                            <option value="sexo">Por sexo</option>
                            <option value="raca">Por raça</option>
                        </select>
                    </div>

                    <div class="chart-container">
                        <canvas id="grafico-cavalos"></canvas>
                    </div>
                </div>

                <div class="grafico-box">
                    <div class="grafico-header">
                        <h3>Distribuição de Clientes</h3>
                        <select id="filtro-clientes" class="grafico-select">
                            <option value="tipo">Por tipo</option>
                        </select>
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