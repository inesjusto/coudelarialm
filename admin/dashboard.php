<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

function calcularDias($dataInicio, $dataFim = null) {
    if (empty($dataInicio)) {
        return 0;
    }

    $inicio = new DateTime($dataInicio);
    $fim = !empty($dataFim) ? new DateTime($dataFim) : new DateTime();

    if ($fim < $inicio) {
        return 0;
    }

    return $inicio->diff($fim)->days + 1;
}

function calcularReceitaAteHoje($dataInicio, $dataFim, $precoDiario) {
    if (empty($dataInicio)) {
        return 0;
    }

    $hoje = new DateTime();
    $inicio = new DateTime($dataInicio);
    $fim = !empty($dataFim) ? new DateTime($dataFim) : $hoje;

    if ($hoje < $inicio) {
        return 0;
    }

    $limite = $hoje < $fim ? $hoje : $fim;
    $diasDecorridos = calcularDias($dataInicio, $limite->format('Y-m-d'));

    return $diasDecorridos * (float)$precoDiario;
}

$stmtAlugueresAtivos = $conn->query("SELECT COUNT(*) FROM alugueres WHERE estado = 'ativo'");
$totalAlugueresAtivos = (int) $stmtAlugueresAtivos->fetchColumn();

$stmtAlugueresConcluidos = $conn->query("SELECT COUNT(*) FROM alugueres WHERE estado = 'concluido'");
$totalAlugueresConcluidos = (int) $stmtAlugueresConcluidos->fetchColumn();

$stmtAlugueresReceita = $conn->query("
    SELECT data_inicio, data_fim, preco_diario, estado
    FROM alugueres
    WHERE estado != 'cancelado'
");

$alugueresReceita = $stmtAlugueresReceita->fetchAll(PDO::FETCH_ASSOC);
$receitaAlugueres = 0;

foreach ($alugueresReceita as $aluguer) {
    $receitaAlugueres += calcularReceitaAteHoje(
        $aluguer['data_inicio'],
        $aluguer['data_fim'],
        $aluguer['preco_diario']
    );
}

$stmtCavalosDisponiveis = $conn->query("
    SELECT COUNT(*) 
    FROM cavalos 
    WHERE id NOT IN (
        SELECT cavalo_id 
        FROM alugueres 
        WHERE estado = 'ativo'
    )
");
$totalCavalosDisponiveis = (int) $stmtCavalosDisponiveis->fetchColumn();
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
                <a href="alugueres.php" class="nav-link">Alugueres</a>
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
                        <a href="alugueres.php" class="botao-secundario">Gerir Alugueres</a>
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

                <div class="stat-card destaque-verde">
                    <span class="stat-label">Alugueres Ativos</span>
                    <strong class="stat-value"><?= htmlspecialchars($totalAlugueresAtivos) ?></strong>
                </div>

                <div class="stat-card destaque-azul">
                    <span class="stat-label">Alugueres Concluídos</span>
                    <strong class="stat-value"><?= htmlspecialchars($totalAlugueresConcluidos) ?></strong>
                </div>

                <div class="stat-card destaque-verde">
                    <span class="stat-label">Cavalos Disponíveis</span>
                    <strong class="stat-value"><?= htmlspecialchars($totalCavalosDisponiveis) ?></strong>
                </div>

                <div class="stat-card destaque-azul receita">
                    <span class="stat-label">Receita de Alugueres Até Hoje</span>
                    <strong class="stat-value"><?= number_format($receitaAlugueres, 2, ',', '.') ?> €</strong>
                </div>
            </section>

            <section class="graficos-grid">
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