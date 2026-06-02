<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';
require_once __DIR__ . '/../backend/atualizar-alugueres.php';

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

$stmtVendasResumo = $conn->query("
    SELECT 
        COUNT(*) AS total_vendas,
        COALESCE(SUM(valor), 0) AS receita_vendas
    FROM vendas_cavalos
");

$vendasResumo = $stmtVendasResumo->fetch(PDO::FETCH_ASSOC);
$totalVendas = (int) ($vendasResumo['total_vendas'] ?? 0);
$receitaVendas = (float) ($vendasResumo['receita_vendas'] ?? 0);

$stmtUltimaVenda = $conn->query("
    SELECT 
        vendas_cavalos.data_venda,
        vendas_cavalos.valor,
        clientes.nome AS cliente_nome,
        cavalos.nome AS cavalo_nome
    FROM vendas_cavalos
    INNER JOIN clientes ON clientes.id = vendas_cavalos.cliente_id
    INNER JOIN cavalos ON cavalos.id = vendas_cavalos.cavalo_id
    ORDER BY vendas_cavalos.data_venda DESC, vendas_cavalos.id DESC
    LIMIT 1
");

$ultimaVenda = $stmtUltimaVenda->fetch(PDO::FETCH_ASSOC);

$anoAtual = (int) date('Y');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="dashboard.php" class="nav-link ativo">Dashboard</a>
                <a href="cavalos.php" class="nav-link">Cavalos</a>
                <a href="clientes.php" class="nav-link">Clientes</a>
                <a href="alugueres.php" class="nav-link">Alugueres</a>
                <a href="aulas.php" class="nav-link">Aulas</a>
                <a href="vendas.php" class="nav-link">Vendas</a>
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
                        <a href="vendas.php" class="botao-principal">Gerir Vendas</a>

                        <div class="pdf-financeiro-box">
    <div class="pdf-financeiro-info">
        <span class="pdf-financeiro-label">Relatório financeiro</span>
        <strong>Exportar resumo financeiro</strong>
        <small>Escolha o período e gere um PDF com receitas, despesas e lucro.</small>
    </div>

    <form action="exportar-financeiro.php" method="GET" target="_blank" class="form-pdf-financeiro">
        <div class="campo-pdf">
            <label for="ano-pdf">Ano</label>
            <select id="ano-pdf" name="ano" required>
                <?php for ($ano = $anoAtual; $ano >= 2024; $ano--): ?>
                    <option value="<?= htmlspecialchars($ano) ?>">
                        <?= htmlspecialchars($ano) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="campo-pdf">
            <label for="mes-pdf">Período</label>
            <select id="mes-pdf" name="mes">
                <option value="">Ano completo</option>
                <option value="1">Janeiro</option>
                <option value="2">Fevereiro</option>
                <option value="3">Março</option>
                <option value="4">Abril</option>
                <option value="5">Maio</option>
                <option value="6">Junho</option>
                <option value="7">Julho</option>
                <option value="8">Agosto</option>
                <option value="9">Setembro</option>
                <option value="10">Outubro</option>
                <option value="11">Novembro</option>
                <option value="12">Dezembro</option>
            </select>
        </div>

        <button type="submit" class="botao-pdf-financeiro">
            Gerar PDF
        </button>
    </form>
</div>
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
                    <h2>Vendas</h2>
                    <p>Resumo das vendas de cavalos registadas no sistema.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card destaque-verde">
                        <span class="stat-label">Vendas Realizadas</span>
                        <strong class="stat-value"><?= htmlspecialchars($totalVendas) ?></strong>
                    </div>

                    <div class="stat-card destaque-verde receita">
                        <span class="stat-label">Receita de Vendas</span>
                        <strong class="stat-value"><?= number_format($receitaVendas, 2, ',', '.') ?> €</strong>
                    </div>

                    <div class="stat-card destaque-azul">
                        <span class="stat-label">Última Venda</span>
                        <strong class="stat-value" style="font-size: 1rem;">
                            <?php if ($ultimaVenda): ?>
                                <?= htmlspecialchars($ultimaVenda['cavalo_nome']) ?>
                                <br>
                                <small><?= htmlspecialchars(date('d/m/Y', strtotime($ultimaVenda['data_venda']))) ?></small>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </strong>
                    </div>
                </div>
            </section>

            <section class="dashboard-secao">
                <div class="dashboard-secao-header">
                    <h2>Financeiro</h2>
                    <p>Receitas, despesas, lucro geral e custos associados aos cavalos.</p>
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