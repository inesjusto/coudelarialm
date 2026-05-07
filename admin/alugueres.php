<?php
require_once __DIR__ . '/../backend/proteger.php';
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

$stmtClientes = $conn->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$stmtCavalos = $conn->query("
    SELECT id, nome 
    FROM cavalos 
    WHERE id NOT IN (
        SELECT cavalo_id 
        FROM alugueres 
        WHERE estado = 'ativo'
    )
    ORDER BY nome ASC
");
$cavalos = $stmtCavalos->fetchAll(PDO::FETCH_ASSOC);

$stmtAlugueres = $conn->query("
    SELECT 
        a.id,
        a.data_inicio,
        a.data_fim,
        a.preco_diario,
        a.estado,
        c.nome AS nome_cliente,
        cv.nome AS nome_cavalo
    FROM alugueres a
    INNER JOIN clientes c ON a.cliente_id = c.id
    INNER JOIN cavalos cv ON a.cavalo_id = cv.id
    ORDER BY a.id DESC
");
$alugueres = $stmtAlugueres->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alugueres</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            <a href="alugueres.php" class="nav-link ativo">Alugueres</a>
            <a href="fornecedores.php" class="nav-link">Fornecedores</a>
            <a href="despesas.php" class="nav-link">Despesas</a>
            <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
        </nav>
    </aside>

    <main class="admin-main">

        <header class="admin-header admin-header-flex">
            <div class="admin-header-texto">
                <h1>Alugueres</h1>
                <p>Gestão de alugueres de cavalos por cliente.</p>
            </div>
        </header>

        <section class="admin-form-wrapper">
            <div class="form-container">
                <h2>Novo Aluguer</h2>

                <form action="../backend/cadastrar-aluguer.php" method="POST">

                    <div class="campo">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option disabled selected>Selecionar</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>">
                                    <?= htmlspecialchars($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Cavalo</label>
                        <select name="cavalo_id" required>
                            <option disabled selected>Selecionar</option>
                            <?php foreach ($cavalos as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>">
                                    <?= htmlspecialchars($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Data início</label>
                        <input type="text" id="data_inicio" name="data_inicio" required>
                    </div>

                    <div class="campo">
                        <label>Data fim</label>
                        <input type="text" id="data_fim" name="data_fim" required>
                    </div>

                    <div class="campo">
                        <label>Preço diário (€)</label>
                        <input type="text" id="preco_diario" name="preco_diario" placeholder="Ex: 25,00" required>
                    </div>

                    <div class="campo">
                        <label>Total previsto</label>
                        <input type="text" id="total_previsto" value="0,00 €" readonly>
                    </div>

                    <input type="hidden" name="estado" value="ativo">

                    <div class="acoes-formulario">
                        <button class="btn-editar btn-form-principal">Criar</button>
                    </div>

                </form>
            </div>
        </section>

        <section class="tabela-container">
            <table class="admin-tabela">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Cavalo</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Preço diário</th>
                        <th>Dias</th>
                        <th>Total previsto</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($alugueres)): ?>
                        <tr>
                            <td colspan="10" class="mensagem-vazia">Sem alugueres</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($alugueres as $a): ?>
                            <?php
                                $diasTotais = calcularDias($a['data_inicio'], $a['data_fim']);
                                $totalPrevisto = $diasTotais * (float)$a['preco_diario'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($a['id']) ?></td>
                                <td><?= htmlspecialchars($a['nome_cliente']) ?></td>
                                <td><?= htmlspecialchars($a['nome_cavalo']) ?></td>
                                <td><?= htmlspecialchars($a['data_inicio']) ?></td>
                                <td><?= htmlspecialchars($a['data_fim'] ?? '—') ?></td>
                                <td><?= number_format((float)$a['preco_diario'], 2, ',', '.') ?> €</td>
                                <td><?= htmlspecialchars($diasTotais) ?></td>
                                <td><?= number_format($totalPrevisto, 2, ',', '.') ?> €</td>
                                <td><?= ucfirst(htmlspecialchars($a['estado'])) ?></td>
                                <td>
                                    <?php if ($a['estado'] === 'ativo'): ?>
                                        <div class="acoes">
                                            <form method="POST" action="../backend/alterar-estado-aluguer.php">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($a['id']) ?>">
                                                <input type="hidden" name="estado" value="concluido">
                                                <button class="btn-editar">Concluir</button>
                                            </form>

                                            <form method="POST" action="../backend/alterar-estado-aluguer.php">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($a['id']) ?>">
                                                <input type="hidden" name="estado" value="cancelado">
                                                <button class="btn-apagar">Cancelar</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
        </section>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputInicio = document.getElementById('data_inicio');
    const inputFim = document.getElementById('data_fim');
    const inputPrecoDiario = document.getElementById('preco_diario');
    const inputTotalPrevisto = document.getElementById('total_previsto');

    function normalizarPreco(valor) {
        if (!valor) return 0;

        valor = valor.toString().trim().replace(/\s/g, '');

        if (valor.includes(',') && valor.includes('.')) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        } else {
            valor = valor.replace(',', '.');
        }

        const numero = parseFloat(valor);
        return isNaN(numero) ? 0 : numero;
    }

    function calcularDias(inicio, fim) {
        if (!inicio || !fim) return 0;

        const dataInicio = new Date(inicio + 'T00:00:00');
        const dataFim = new Date(fim + 'T00:00:00');

        if (dataFim < dataInicio) return 0;

        const diferenca = dataFim - dataInicio;
        return Math.floor(diferenca / (1000 * 60 * 60 * 24)) + 1;
    }

    function atualizarTotalPrevisto() {
        const dias = calcularDias(inputInicio.value, inputFim.value);
        const precoDiario = normalizarPreco(inputPrecoDiario.value);
        const total = dias * precoDiario;

        inputTotalPrevisto.value = total.toLocaleString('pt-PT', {
            style: 'currency',
            currency: 'EUR'
        });
    }

    const fim = flatpickr("#data_fim", {
        dateFormat: "Y-m-d",
        locale: "pt",
        onChange: atualizarTotalPrevisto
    });

    flatpickr("#data_inicio", {
        dateFormat: "Y-m-d",
        locale: "pt",
        onChange: function(selectedDates, dateStr) {
            fim.set("minDate", dateStr);

            if (inputFim.value && inputFim.value < dateStr) {
                inputFim.value = '';
            }

            atualizarTotalPrevisto();
        }
    });

    inputPrecoDiario.addEventListener('input', atualizarTotalPrevisto);
    inputInicio.addEventListener('change', atualizarTotalPrevisto);
    inputFim.addEventListener('change', atualizarTotalPrevisto);
});
</script>

</body>
</html>