<?php
require_once __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';
require_once __DIR__ . '/../backend/atualizar-alugueres.php';

function calcularDiasAluguer($dataInicio, $dataFim = null) {
    if (empty($dataInicio)) {
        return 0;
    }

    $inicio = new DateTime($dataInicio);
    $fim = !empty($dataFim) ? new DateTime($dataFim) : new DateTime();

    if ($fim < $inicio) {
        return 0;
    }

    $dias = $inicio->diff($fim)->days + 1;

    if ($fim > $inicio) {
        $dias++;
    }

    return $dias;
}

function formatarData($data) {
    if (empty($data)) {
        return '—';
    }

    return date('d/m/Y', strtotime($data));
}

function formatarValor($valor) {
    return number_format((float) $valor, 2, ',', '.') . ' €';
}

function formatarEstado($estado) {
    $estado = trim((string) $estado);

    if ($estado === '') {
        return '-';
    }

    $estado = strtolower($estado);

    if ($estado === 'ativo') {
        return 'Ativo';
    }

    if ($estado === 'reservado') {
        return 'Reservado';
    }

    if ($estado === 'concluido') {
        return 'Concluído';
    }

    if ($estado === 'cancelado') {
        return 'Cancelado';
    }

    return ucfirst($estado);
}

$stmtClientes = $conn->query("
    SELECT id, nome
    FROM clientes
    WHERE TRIM(LOWER(estado)) = 'cliente'
    ORDER BY nome ASC
");
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$stmtCavalos = $conn->query("
    SELECT id, nome
    FROM cavalos
    WHERE TRIM(LOWER(estado)) IN ('disponível', 'disponivel')
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

$erro = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';

function mensagemErroAluguer($erro) {
    switch ($erro) {
        case 'metodo':
            return 'Método inválido.';
        case 'campos':
            return 'Preencha todos os campos obrigatórios.';
        case 'data_inicio':
            return 'A data de início é inválida.';
        case 'data_fim':
            return 'A data de fim é inválida.';
        case 'datas':
            return 'A data de fim não pode ser anterior à data de início.';
        case 'preco':
            return 'O preço diário deve ser superior a 0.';
        case 'cliente':
            return 'Só é possível criar alugueres para clientes com estado Cliente.';
        case 'cavalo':
            return 'Só é possível alugar cavalos com estado Disponível.';
        case 'sobreposicao':
            return 'Este cavalo já tem um aluguer ativo ou reservado nesse período.';
        case 'guardar':
            return 'Ocorreu um erro ao criar o aluguer. Tente novamente.';
        default:
            return '';
    }
}

function mensagemSucessoAluguer($sucesso) {
    switch ($sucesso) {
        case 'criado':
            return 'Aluguer criado com sucesso.';
        default:
            return '';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alugueres</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
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
                <a href="aulas.php" class="nav-link">Aulas</a>
                <a href="vendas.php" class="nav-link">Vendas</a>
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
                                <option value="" disabled selected>Selecionar</option>

                                <?php if (empty($clientes)): ?>
                                    <option value="" disabled>Não existem clientes disponíveis</option>
                                <?php else: ?>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= htmlspecialchars($cliente['id']) ?>">
                                            <?= htmlspecialchars($cliente['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label>Cavalo</label>

                            <select name="cavalo_id" required>
                                <option value="" disabled selected>Selecionar</option>

                                <?php if (empty($cavalos)): ?>
                                    <option value="" disabled>Não existem cavalos disponíveis</option>
                                <?php else: ?>
                                    <?php foreach ($cavalos as $cavalo): ?>
                                        <option value="<?= htmlspecialchars($cavalo['id']) ?>">
                                            <?= htmlspecialchars($cavalo['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

                        <input type="hidden" name="estado" id="estado_aluguer" value="reservado">

                        <div class="acoes-formulario">
                            <button class="btn-editar btn-form-principal" type="submit">
                                Criar Aluguer
                            </button>
                        </div>

                        <?php if (mensagemErroAluguer($erro) !== ''): ?>
                            <p class="mensagem-formulario mensagem-erro">
                                <?= htmlspecialchars(mensagemErroAluguer($erro)) ?>
                            </p>
                        <?php elseif (mensagemSucessoAluguer($sucesso) !== ''): ?>
                            <p class="mensagem-formulario mensagem-sucesso">
                                <?= htmlspecialchars(mensagemSucessoAluguer($sucesso)) ?>
                            </p>
                        <?php endif; ?>
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
                            <th>Total</th>
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
                            <?php foreach ($alugueres as $aluguer): ?>
                                <?php
                                    $diasTotais = calcularDiasAluguer($aluguer['data_inicio'], $aluguer['data_fim']);
                                    $totalPrevisto = $diasTotais * (float) $aluguer['preco_diario'];
                                    $estadoNormalizado = strtolower(trim($aluguer['estado']));
                                ?>

                                <tr>
                                    <td><?= htmlspecialchars($aluguer['id']) ?></td>
                                    <td><?= htmlspecialchars($aluguer['nome_cliente']) ?></td>
                                    <td><?= htmlspecialchars($aluguer['nome_cavalo']) ?></td>
                                    <td><?= htmlspecialchars(formatarData($aluguer['data_inicio'])) ?></td>
                                    <td><?= htmlspecialchars(formatarData($aluguer['data_fim'])) ?></td>
                                    <td><?= htmlspecialchars(formatarValor($aluguer['preco_diario'])) ?></td>
                                    <td><?= htmlspecialchars($diasTotais) ?></td>
                                    <td><?= htmlspecialchars(formatarValor($totalPrevisto)) ?></td>
                                    <td><?= htmlspecialchars(formatarEstado($aluguer['estado'])) ?></td>

                                    <td>
                                        <?php if ($estadoNormalizado === 'ativo'): ?>
                                            <div class="acoes">
                                                <form method="POST" action="../backend/alterar-estado-aluguer.php">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($aluguer['id']) ?>">
                                                    <input type="hidden" name="estado" value="concluido">
                                                    <button class="btn-editar" type="submit">Concluir</button>
                                                </form>

                                                <form method="POST" action="../backend/alterar-estado-aluguer.php">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($aluguer['id']) ?>">
                                                    <input type="hidden" name="estado" value="cancelado">
                                                    <button class="btn-apagar" type="submit">Cancelar</button>
                                                </form>
                                            </div>
                                        <?php elseif ($estadoNormalizado === 'reservado'): ?>
                                            <div class="acoes">
                                                <form method="POST" action="../backend/alterar-estado-aluguer.php">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($aluguer['id']) ?>">
                                                    <input type="hidden" name="estado" value="cancelado">
                                                    <button class="btn-apagar" type="submit">Cancelar</button>
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
        /* =========================
           CÁLCULO DO ALUGUER
        ========================= */
        document.addEventListener('DOMContentLoaded', function () {
            const inputInicio = document.getElementById('data_inicio');
            const inputFim = document.getElementById('data_fim');
            const inputPrecoDiario = document.getElementById('preco_diario');
            const inputTotalPrevisto = document.getElementById('total_previsto');
            const inputEstadoAluguer = document.getElementById('estado_aluguer');

            /* =========================
               NORMALIZAÇÃO DO PREÇO
            ========================= */
            function normalizarPreco(valor) {
                if (!valor) return 0;

                valor = valor.toString().trim().replace('€', '').replace(/\s/g, '');

                const temVirgula = valor.includes(',');
                const temPonto = valor.includes('.');

                if (temVirgula && temPonto) {
                    valor = valor.replace(/\./g, '').replace(',', '.');
                } else if (temVirgula && !temPonto) {
                    valor = valor.replace(',', '.');
                } else if (temPonto && !temVirgula) {
                    const partes = valor.split('.');

                    if (partes.length === 2 && partes[1].length === 3) {
                        valor = valor.replace(/\./g, '');
                    }
                }

                const numero = parseFloat(valor);

                return isNaN(numero) ? 0 : numero;
            }

            /* =========================
               FORMATAÇÃO EM EUROS
            ========================= */
            function formatarEuros(valor) {
                return `${Number(valor || 0)
                    .toFixed(2)
                    .replace('.', ',')
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.')} €`;
            }

            /* =========================
               DATA ATUAL EM FORMATO ISO
            ========================= */
            function hojeISO() {
                const hoje = new Date();
                const ano = hoje.getFullYear();
                const mes = String(hoje.getMonth() + 1).padStart(2, '0');
                const dia = String(hoje.getDate()).padStart(2, '0');

                return `${ano}-${mes}-${dia}`;
            }

            /* =========================
               CÁLCULO DOS DIAS DO ALUGUER
            ========================= */
            function calcularDiasAluguer(inicio, fim) {
                if (!inicio || !fim) return 0;

                const dataInicio = new Date(inicio + 'T00:00:00');
                const dataFim = new Date(fim + 'T00:00:00');

                if (dataFim < dataInicio) return 0;

                const diferenca = dataFim - dataInicio;
                let dias = Math.floor(diferenca / (1000 * 60 * 60 * 24)) + 1;

                if (dataFim > dataInicio) {
                    dias++;
                }

                return dias;
            }

            /* =========================
               ESTADO AUTOMÁTICO DO ALUGUER
            ========================= */
            function atualizarEstadoAluguer() {
                const inicio = inputInicio.value;
                const fim = inputFim.value;
                const hoje = hojeISO();

                if (!inicio || !fim) {
                    inputEstadoAluguer.value = 'reservado';
                    return;
                }

                if (inicio <= hoje && fim >= hoje) {
                    inputEstadoAluguer.value = 'ativo';
                } else {
                    inputEstadoAluguer.value = 'reservado';
                }
            }

            /* =========================
               TOTAL PREVISTO DO ALUGUER
            ========================= */
            function atualizarTotalPrevisto() {
                const dias = calcularDiasAluguer(inputInicio.value, inputFim.value);
                const precoDiario = normalizarPreco(inputPrecoDiario.value);
                const total = dias * precoDiario;

                inputTotalPrevisto.value = formatarEuros(total);
                atualizarEstadoAluguer();
            }

            const fim = flatpickr("#data_fim", {
                dateFormat: "Y-m-d",
                locale: "pt",
                disableMobile: true,
                onChange: atualizarTotalPrevisto
            });

            flatpickr("#data_inicio", {
                dateFormat: "Y-m-d",
                locale: "pt",
                disableMobile: true,
                onChange: function (selectedDates, dateStr) {
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