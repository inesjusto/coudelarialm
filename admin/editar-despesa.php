<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('ID da despesa inválido.');
}

$stmtDespesa = $conn->prepare("
    SELECT *
    FROM despesas
    WHERE id = :id
    LIMIT 1
");

$stmtDespesa->execute([
    ':id' => $id
]);

$despesa = $stmtDespesa->fetch(PDO::FETCH_ASSOC);

if (!$despesa) {
    die('Despesa não encontrada.');
}

$stmtFornecedores = $conn->query("
    SELECT id, nome
    FROM fornecedores
    ORDER BY nome ASC
");

$fornecedores = $stmtFornecedores->fetchAll(PDO::FETCH_ASSOC);

function selecionado($valorAtual, $valorOpcao) {
    return (string) $valorAtual === (string) $valorOpcao ? 'selected' : '';
}

function formatarValorInput($valor) {
    if ($valor === null || $valor === '') {
        return '';
    }

    return number_format((float) $valor, 2, ',', '.');
}

$categorias = [
    'Manutenção',
    'Equipamento',
    'Transporte',
    'Água / Luz',
    'Limpeza',
    'Obras',
    'Serviços Administrativos',
    'Alimentação',
    'Ração',
    'Feno',
    'Palha',
    'Suplementos',
    'Medicamentos',
    'Veterinário',
    'Ferrador',
    'Outros'
];

$metodosPagamento = [
    'Dinheiro',
    'Cartão',
    'Transferência',
    'MB Way',
    'Cheque',
    'Automático',
    'Outro'
];

$estadosPagamento = [
    'pendente' => 'Pendente',
    'pago' => 'Pago',
    'cancelado' => 'Cancelado'
];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Despesa</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
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
                <a href="alugueres.php" class="nav-link">Alugueres</a>
                <a href="aulas.php" class="nav-link">Aulas</a>
                <a href="vendas.php" class="nav-link">Vendas</a>
                <a href="fornecedores.php" class="nav-link">Fornecedores</a>
                <a href="despesas.php" class="nav-link ativo">Despesas</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Editar Despesa</h1>
                    <p>Altere os dados da despesa selecionada.</p>
                </div>

                <a href="despesas.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/editar-despesa.php" method="POST" novalidate>
                        <input
                            type="hidden"
                            id="id"
                            name="id"
                            value="<?= htmlspecialchars($despesa['id']) ?>"
                        >

                        <div class="campo">
                            <label for="fornecedor_id">Fornecedor</label>

                            <select id="fornecedor_id" name="fornecedor_id">
                                <option value="">Sem fornecedor</option>

                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option
                                        value="<?= htmlspecialchars($fornecedor['id']) ?>"
                                        <?= selecionado($despesa['fornecedor_id'] ?? '', $fornecedor['id']) ?>
                                    >
                                        <?= htmlspecialchars($fornecedor['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="categoria">Categoria</label>

                            <select id="categoria" name="categoria" required>
                                <option value="">Selecione</option>

                                <?php foreach ($categorias as $categoria): ?>
                                    <option
                                        value="<?= htmlspecialchars($categoria) ?>"
                                        <?= selecionado($despesa['categoria'] ?? '', $categoria) ?>
                                    >
                                        <?= htmlspecialchars($categoria) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="descricao">Descrição</label>

                            <textarea
                                id="descricao"
                                name="descricao"
                                rows="4"
                            ><?= htmlspecialchars($despesa['descricao'] ?? '') ?></textarea>
                        </div>

                        <div class="campo">
                            <label for="valor">Valor (€)</label>

                            <input
                                type="text"
                                id="valor"
                                name="valor"
                                value="<?= htmlspecialchars(formatarValorInput($despesa['valor'] ?? '')) ?>"
                                placeholder="Ex.: 150,50"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="usar_calculo">Recalcular valor automaticamente?</label>

                            <select id="usar_calculo">
                                <option value="nao">Não</option>
                                <option value="sim">Sim</option>
                            </select>
                        </div>

                        <div id="campos-calculo-cavalo" style="display: none;">
                            <div class="campo">
                                <label for="consumo_diario">Consumo Diário</label>
                                <input type="text" id="consumo_diario" placeholder="Ex.: 1">
                            </div>

                            <div class="campo">
                                <label for="unidade">Unidade</label>

                                <select id="unidade">
                                    <option value="kg">kg</option>
                                    <option value="g">g</option>
                                    <option value="L">L</option>
                                    <option value="un">un</option>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="data_inicio_calculo">Data de Início</label>

                                <input
                                    type="text"
                                    id="data_inicio_calculo"
                                    class="input-data"
                                    placeholder="Selecione a data"
                                >
                            </div>

                            <div class="campo">
                                <label for="data_fim_calculo">Data de Fim</label>

                                <input
                                    type="text"
                                    id="data_fim_calculo"
                                    class="input-data"
                                    placeholder="Selecione a data"
                                >
                            </div>

                            <div class="campo">
                                <label for="quantidade_por_embalagem">Quantidade por Embalagem</label>
                                <input type="text" id="quantidade_por_embalagem" placeholder="Ex.: 20">
                            </div>

                            <div class="campo">
                                <label for="preco_embalagem">Preço por Embalagem (€)</label>
                                <input type="text" id="preco_embalagem" placeholder="Ex.: 18,50">
                            </div>

                            <div class="campo">
                                <label>Resumo do Cálculo</label>

                                <div class="mensagem-formulario" id="resumo-consumo">
                                    Preencha os campos para recalcular automaticamente o valor.
                                </div>
                            </div>
                        </div>

                        <div class="campo">
                            <label for="data_despesa">Data da Despesa</label>

                            <input
                                type="text"
                                id="data_despesa"
                                name="data_despesa"
                                class="input-data"
                                value="<?= htmlspecialchars($despesa['data_despesa'] ?? '') ?>"
                                placeholder="Selecione a data"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="metodo_pagamento">Método de Pagamento</label>

                            <select id="metodo_pagamento" name="metodo_pagamento">
                                <option value="">Selecione</option>

                                <?php foreach ($metodosPagamento as $metodo): ?>
                                    <option
                                        value="<?= htmlspecialchars($metodo) ?>"
                                        <?= selecionado($despesa['metodo_pagamento'] ?? '', $metodo) ?>
                                    >
                                        <?= htmlspecialchars($metodo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="estado_pagamento">Estado do Pagamento</label>

                            <select id="estado_pagamento" name="estado_pagamento">
                                <?php foreach ($estadosPagamento as $valor => $texto): ?>
                                    <option
                                        value="<?= htmlspecialchars($valor) ?>"
                                        <?= selecionado($despesa['estado_pagamento'] ?? 'pendente', $valor) ?>
                                    >
                                        <?= htmlspecialchars($texto) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">
                                Guardar Alterações
                            </button>

                            <a href="despesas.php" class="btn-cancelar">
                                Cancelar
                            </a>
                        </div>

                        <p id="mensagem-formulario" class="mensagem-formulario"></p>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script src="assets/js/admin.js"></script>

    <script>
        /* =========================
           RECÁLCULO AUTOMÁTICO DA DESPESA
        ========================= */
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('.input-data', {
                    dateFormat: 'Y-m-d',
                    locale: 'pt',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function () {
                        calcularResumoConsumo();
                    }
                });
            }

            const usarCalculo = document.getElementById('usar_calculo');
            const camposCalculo = document.getElementById('campos-calculo-cavalo');

            const valor = document.getElementById('valor');
            const resumoConsumo = document.getElementById('resumo-consumo');

            const consumoDiario = document.getElementById('consumo_diario');
            const dataInicio = document.getElementById('data_inicio_calculo');
            const dataFim = document.getElementById('data_fim_calculo');
            const quantidadePorEmbalagem = document.getElementById('quantidade_por_embalagem');
            const precoEmbalagem = document.getElementById('preco_embalagem');
            const unidade = document.getElementById('unidade');

            /* =========================
               NORMALIZAÇÃO DE VALORES
            ========================= */
            function normalizarValor(valor) {
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
               FORMATAÇÃO DE VALORES
            ========================= */
            function formatarValor(valor) {
                return `${Number(valor || 0)
                    .toFixed(2)
                    .replace('.', ',')
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
            }

            /* =========================
               CÁLCULO DO CONSUMO
            ========================= */
            function calcularResumoConsumo() {
                if (!resumoConsumo) return;

                const consumo = normalizarValor(consumoDiario.value);
                const inicio = dataInicio.value;
                const fim = dataFim.value;
                const qtdEmbalagem = normalizarValor(quantidadePorEmbalagem.value);
                const preco = normalizarValor(precoEmbalagem.value);

                if (!consumo || !inicio || !fim || !qtdEmbalagem || !preco) {
                    resumoConsumo.textContent = 'Preencha os campos para recalcular automaticamente o valor.';
                    return;
                }

                const data1 = new Date(inicio + 'T00:00:00');
                const data2 = new Date(fim + 'T00:00:00');

                if (data2 < data1) {
                    resumoConsumo.textContent = 'A data de fim não pode ser anterior à data de início.';
                    return;
                }

                const diferencaMs = data2 - data1;
                const dias = Math.floor(diferencaMs / (1000 * 60 * 60 * 24)) + 1;
                const quantidadeTotal = consumo * dias;
                const embalagens = Math.ceil(quantidadeTotal / qtdEmbalagem);
                const custoTotal = embalagens * preco;

                valor.value = formatarValor(custoTotal);

                resumoConsumo.innerHTML = `
                    Dias: <strong>${dias}</strong><br>
                    Quantidade total: <strong>${quantidadeTotal.toFixed(2)} ${unidade.value}</strong><br>
                    Embalagens necessárias: <strong>${embalagens}</strong><br>
                    Novo valor da despesa: <strong>${formatarValor(custoTotal)} €</strong>
                `;
            }

            if (usarCalculo) {
                usarCalculo.addEventListener('change', function () {
                    camposCalculo.style.display = usarCalculo.value === 'sim' ? 'block' : 'none';
                });
            }

            [
                consumoDiario,
                dataInicio,
                dataFim,
                quantidadePorEmbalagem,
                precoEmbalagem,
                unidade
            ].forEach(campo => {
                if (campo) {
                    campo.addEventListener('input', calcularResumoConsumo);
                    campo.addEventListener('change', calcularResumoConsumo);
                }
            });
        });
    </script>
</body>
</html>