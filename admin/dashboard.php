<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

function calcularDias($dataInicio, $dataFim = null) {
    if (empty($dataInicio)) return 0;

    $inicio = new DateTime($dataInicio);
    $fim = !empty($dataFim) ? new DateTime($dataFim) : new DateTime();

    if ($fim < $inicio) return 0;

    return $inicio->diff($fim)->days + 1;
}

function calcularReceitaAteHoje($dataInicio, $dataFim, $precoDiario) {
    if (empty($dataInicio)) return 0;

    $hoje = new DateTime();
    $inicio = new DateTime($dataInicio);
    $fim = !empty($dataFim) ? new DateTime($dataFim) : $hoje;

    if ($hoje < $inicio) return 0;

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
    WHERE TRIM(estado) = 'Disponível'
");
$totalCavalosDisponiveis = (int) $stmtCavalosDisponiveis->fetchColumn();

$stmtCavalosIndisponiveis = $conn->query("
    SELECT COUNT(*) 
    FROM cavalos 
    WHERE estado IS NULL 
       OR TRIM(estado) = '' 
       OR TRIM(estado) <> 'Disponível'
");
$totalCavalosIndisponiveis = (int) $stmtCavalosIndisponiveis->fetchColumn();
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
                <a href="aulas.php" class="nav-link">Aulas</a>
                <a href="fornecedores.php" class="nav-link">Fornecedores</a>
                <a href="despesas.php" class="nav-link">Despesas</a>
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
                        <a href="cavalos.php" class="botao-principal">Gerir Cavalos</a>
                        <a href="clientes.php" class="botao-principal">Gerir Clientes</a>
                        <a href="alugueres.php" class="botao-principal">Gerir Alugueres</a>
                        <a href="aulas.php" class="botao-principal">Gerir Aulas</a>
                        <a href="fornecedores.php" class="botao-principal">Gerir Fornecedores</a>
                        <a href="despesas.php" class="botao-principal">Gerir Despesas</a>
                        <a href="exportar-financeiro.php?periodo=geral" target="_blank" class="botao-principal">PDF Geral</a>
                        <a href="exportar-financeiro.php?periodo=mes" target="_blank" class="botao-principal">PDF do Mês</a>
                        <a href="exportar-financeiro.php?periodo=ano" target="_blank" class="botao-principal">PDF do Ano</a>
                    </div>
                </div>
            </header>

            <section class="dashboard-secao">
                <div class="dashboard-secao-header">
                    <h2>Cavalos</h2>
                    <p>Resumo e análise dos cavalos da coudelaria.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card destaque-verde">
                        <span class="stat-label">Total de Cavalos</span>
                        <strong class="stat-value" id="total-cavalos">0</strong>
                    </div>

                    <div class="stat-card destaque-verde">
    <span class="stat-label">Cavalos Disponíveis</span>
    <strong class="stat-value"><?= htmlspecialchars($totalCavalosDisponiveis) ?></strong>
</div>

<div class="stat-card destaque-azul">
    <span class="stat-label">Cavalos Indisponíveis</span>
    <strong class="stat-value"><?= htmlspecialchars($totalCavalosIndisponiveis) ?></strong>
</div>
                </div>

                <div class="graficos-grid">
                    <div class="grafico-box">
                        <div class="grafico-header">
                            <h3>Distribuição de Cavalos</h3>

                            <div class="grafico-filtros" id="filtro-cavalos">
                                <button class="filtro-btn ativo" data-value="sexo">Sexo</button>
                                <button class="filtro-btn" data-value="idade">Idade</button>
                            </div>
                        </div>

                        <div class="chart-container">
                            <canvas id="grafico-cavalos"></canvas>
                        </div>
                    </div>

                    <div class="grafico-box">
                        <div class="grafico-header">
                            <h3>Cavalos por Raça</h3>
                        </div>

                        <div class="chart-container">
                            <canvas id="grafico-racas-detalhe"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dashboard-secao">
    <div class="dashboard-secao-header">
        <h2>Clientes</h2>
        <p>Resumo e distribuição dos clientes registados.</p>
    </div>

<div class="stats-grid">

    <div class="stat-card destaque-verde">
        <span class="stat-label">Clientes</span>
        <strong class="stat-value" id="clientes-estado">0</strong>
    </div>

    <div class="stat-card destaque-amarelo">
        <span class="stat-label">Potenciais Clientes</span>
        <strong class="stat-value" id="clientes-potenciais">0</strong>
    </div>

    <div class="stat-card destaque-roxo">
        <span class="stat-label">Potenciais Clientes (Contactados)</span>
        <strong class="stat-value" id="clientes-contactados">0</strong>
    </div>

</div>

    <div class="graficos-grid">

    <div class="grafico-box">
        <div class="grafico-header">
            <h3>Clientes por Tipo</h3>
        </div>

        <div class="chart-container">
            <canvas id="grafico-clientes-tipo"></canvas>
        </div>
    </div>

    <div class="grafico-box">
        <div class="grafico-header">
            <h3>Clientes Interessados em Cavalos</h3>
        </div>

        <div class="chart-container">
            <canvas id="grafico-clientes-estado"></canvas>
        </div>
    </div>

</div>
</section>

            <section class="dashboard-secao">
                <div class="dashboard-secao-header">
                    <h2>Alugueres</h2>
                    <p>Estado dos alugueres e receita acumulada.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card destaque-verde">
                        <span class="stat-label">Alugueres Ativos</span>
                        <strong class="stat-value"><?= htmlspecialchars($totalAlugueresAtivos) ?></strong>
                    </div>

                    <div class="stat-card destaque-azul">
                        <span class="stat-label">Alugueres Concluídos</span>
                        <strong class="stat-value"><?= htmlspecialchars($totalAlugueresConcluidos) ?></strong>
                    </div>

                    <div class="stat-card destaque-azul receita">
                        <span class="stat-label">Receita de Alugueres Até Hoje</span>
                        <strong class="stat-value"><?= number_format($receitaAlugueres, 2, ',', '.') ?> €</strong>
                    </div>
                </div>
            </section>

            <section class="dashboard-secao">
                <div class="dashboard-secao-header">
                    <h2>Financeiro</h2>
                    <p>Despesas gerais, despesas por categoria e custo mensal por cavalo.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card destaque-azul receita">
                        <span class="stat-label">Despesas do Mês</span>
                        <strong class="stat-value" id="financeiro-total-mes">0,00 €</strong>
                    </div>

                    <div class="stat-card destaque-verde receita">
                        <span class="stat-label">Despesas do Ano</span>
                        <strong class="stat-value" id="financeiro-total-ano">0,00 €</strong>
                    </div>

                    <div class="stat-card destaque-azul receita">
                        <span class="stat-label">Despesas Pendentes</span>
                        <strong class="stat-value" id="financeiro-total-pendente">0,00 €</strong>
                    </div>

                    <div class="stat-card destaque-verde receita">
    <span class="stat-label">Receita Total</span>
    <strong class="stat-value" id="financeiro-receita-total">0,00 €</strong>
</div>

<div class="stat-card destaque-azul receita">
    <span class="stat-label">Despesas Totais</span>
    <strong class="stat-value" id="financeiro-despesas-total">0,00 €</strong>
</div>

<div class="stat-card destaque-verde receita">
    <span class="stat-label">Lucro Geral</span>
    <strong class="stat-value" id="financeiro-lucro-geral">0,00 €</strong>
</div> 

                </div>

                <div class="graficos-grid">
                    <div class="grafico-box">
                        <div class="grafico-header">
                            <h3>Despesas por Categoria</h3>
                        </div>

                        <div class="chart-container">
                            <canvas id="grafico-despesas-categorias"></canvas>
                        </div>
                    </div>

                    <div class="grafico-box">
                        <div class="grafico-header">
                            <h3>Custo Mensal por Cavalo</h3>
                        </div>

                        <div class="chart-container">
                            <canvas id="grafico-custo-cavalos"></canvas>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>