<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

$stmtCavalos = $conn->query("
    SELECT id, nome
    FROM cavalos
    ORDER BY nome ASC
");
$cavalos = $stmtCavalos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Despesa</title>
    <link rel="stylesheet" href="assets/css/admin.css">
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
                    <h1>Adicionar Despesa</h1>
                    <p>Registe uma despesa geral ou uma despesa associada a um cavalo.</p>
                </div>

                <a href="despesas.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/cadastrar-despesa.php" method="POST" novalidate>

                        <div class="campo">
                            <label for="tipo_registo">Tipo de Despesa</label>
                            <select id="tipo_registo" name="tipo_registo" required>
                                <option value="manual">Despesa geral da coudelaria</option>
                                <option value="cavalo">Despesa de cavalo</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="fornecedor_id">Fornecedor</label>
                            <select id="fornecedor_id" name="fornecedor_id">
                                <option value="">Sem fornecedor</option>
                            </select>
                        </div>

                        <div id="campo-cavalo" style="display: none;">
                            <div class="campo">
                                <label for="cavalo_id">Cavalo</label>
                                <select id="cavalo_id" name="cavalo_id">
                                    <option value="">Selecione o cavalo</option>

                                    <?php foreach ($cavalos as $cavalo): ?>
                                        <option value="<?= htmlspecialchars($cavalo['id']) ?>">
                                            <?= htmlspecialchars($cavalo['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="campo">
                            <label for="categoria">Categoria</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Selecione</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="4"></textarea>
                        </div>

                        <div id="campos-valor-manual">
                            <div class="campo">
                                <label for="valor">Valor (€)</label>
                                <input type="text" id="valor" name="valor" placeholder="Ex.: 150.50">
                            </div>
                        </div>

                        <div id="campos-calculo-cavalo" style="display: none;">
                            <div class="campo">
                                <label for="consumo_diario">Consumo Diário</label>
                                <input type="text" id="consumo_diario" name="consumo_diario" placeholder="Ex.: 1">
                            </div>

                            <div class="campo">
                                <label for="unidade">Unidade</label>
                                <select id="unidade" name="unidade">
                                    <option value="kg">kg</option>
                                    <option value="g">g</option>
                                    <option value="L">L</option>
                                    <option value="un">un</option>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="data_inicio">Data de Início</label>
                                <input type="date" id="data_inicio" name="data_inicio">
                            </div>

                            <div class="campo">
                                <label for="data_fim">Data de Fim</label>
                                <input type="date" id="data_fim" name="data_fim">
                            </div>

                            <div class="campo">
                                <label for="quantidade_por_embalagem">Quantidade por Embalagem</label>
                                <input type="text" id="quantidade_por_embalagem" name="quantidade_por_embalagem" placeholder="Ex.: 20">
                            </div>

                            <div class="campo">
                                <label for="preco_embalagem">Preço por Embalagem (€)</label>
                                <input type="text" id="preco_embalagem" name="preco_embalagem" placeholder="Ex.: 18.50">
                            </div>

                            <div class="campo">
                                <label>Resumo do Cálculo</label>
                                <div class="mensagem-formulario" id="resumo-consumo">
                                    Preencha os campos para calcular automaticamente o custo.
                                </div>
                            </div>
                        </div>

                        <div class="campo">
                            <label for="data_despesa">Data da Despesa</label>
                            <input type="date" id="data_despesa" name="data_despesa" required>
                        </div>

                        <div class="campo">
                            <label for="metodo_pagamento">Método de Pagamento</label>
                            <select id="metodo_pagamento" name="metodo_pagamento">
                                <option value="">Selecione</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Cartão">Cartão</option>
                                <option value="Transferência">Transferência</option>
                                <option value="MB Way">MB Way</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Automático">Automático</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="estado_pagamento">Estado do Pagamento</label>
                            <select id="estado_pagamento" name="estado_pagamento">
                                <option value="pendente">Pendente</option>
                                <option value="pago">Pago</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Despesa</button>
                            <a href="despesas.php" class="btn-cancelar">Cancelar</a>
                        </div>

                        <p id="mensagem-formulario" class="mensagem-formulario"></p>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoRegisto = document.getElementById('tipo_registo');
            const campoCavalo = document.getElementById('campo-cavalo');
            const categoria = document.getElementById('categoria');

            const camposValorManual = document.getElementById('campos-valor-manual');
            const camposCalculoCavalo = document.getElementById('campos-calculo-cavalo');

            const valor = document.getElementById('valor');
            const resumoConsumo = document.getElementById('resumo-consumo');

            const consumoDiario = document.getElementById('consumo_diario');
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            const quantidadePorEmbalagem = document.getElementById('quantidade_por_embalagem');
            const precoEmbalagem = document.getElementById('preco_embalagem');
            const unidade = document.getElementById('unidade');

            const categoriasGerais = [
                'Manutenção',
                'Equipamento',
                'Transporte',
                'Água / Luz',
                'Limpeza',
                'Obras',
                'Serviços Administrativos',
                'Outros'
            ];

            const categoriasCavalo = [
                'Ração',
                'Feno',
                'Palha',
                'Suplementos',
                'Medicamentos',
                'Veterinário',
                'Ferrador',
                'Outros'
            ];

            const categoriasComCalculo = [
                'Ração',
                'Feno',
                'Palha',
                'Suplementos'
            ];

            function preencherCategorias(lista) {
                categoria.innerHTML = '<option value="">Selecione</option>';

                lista.forEach(nome => {
                    const option = document.createElement('option');
                    option.value = nome;
                    option.textContent = nome;
                    categoria.appendChild(option);
                });
            }

            function limparCamposCalculo() {
                consumoDiario.value = '';
                dataInicio.value = '';
                dataFim.value = '';
                quantidadePorEmbalagem.value = '';
                precoEmbalagem.value = '';
                resumoConsumo.textContent = 'Preencha os campos para calcular automaticamente o custo.';
            }

            function atualizarTipoDespesa() {
                if (tipoRegisto.value === 'cavalo') {
                    campoCavalo.style.display = 'block';
                    preencherCategorias(categoriasCavalo);
                } else {
                    campoCavalo.style.display = 'none';
                    preencherCategorias(categoriasGerais);
                }

                atualizarCamposCategoria();
            }

            function atualizarCamposCategoria() {
                const categoriaSelecionada = categoria.value;
                const deveCalcular = tipoRegisto.value === 'cavalo' && categoriasComCalculo.includes(categoriaSelecionada);

                if (deveCalcular) {
                    camposValorManual.style.display = 'none';
                    camposCalculoCavalo.style.display = 'block';

                    valor.removeAttribute('required');
                    valor.value = '';
                } else {
                    camposValorManual.style.display = 'block';
                    camposCalculoCavalo.style.display = 'none';

                    valor.setAttribute('required', 'required');
                    limparCamposCalculo();
                }
            }


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

            function formatarValor(valor) {
                return `${Number(valor || 0)
                    .toFixed(2)
                    .replace('.', ',')
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.')} €`;
            }
            function calcularResumoConsumo() {
                const consumo = normalizarValor(consumoDiario.value);
                const inicio = dataInicio.value;
                const fim = dataFim.value;
                const qtdEmbalagem = normalizarValor(quantidadePorEmbalagem.value);
                const preco = normalizarValor(precoEmbalagem.value);

                if (!consumo || !inicio || !fim || !qtdEmbalagem || isNaN(preco)) {
                    resumoConsumo.textContent = 'Preencha os campos para calcular automaticamente o custo.';
                    return;
                }

                const data1 = new Date(inicio);
                const data2 = new Date(fim);

                if (data2 < data1) {
                    resumoConsumo.textContent = 'A data de fim não pode ser anterior à data de início.';
                    return;
                }

                const diferencaMs = data2 - data1;
                const dias = Math.floor(diferencaMs / (1000 * 60 * 60 * 24)) + 1;
                const quantidadeTotal = consumo * dias;
                const embalagens = Math.ceil(quantidadeTotal / qtdEmbalagem);
                const custoTotal = embalagens * preco;

                resumoConsumo.innerHTML = `
                    Dias: <strong>${dias}</strong><br>
                    Quantidade total: <strong>${quantidadeTotal.toFixed(2)} ${unidade.value}</strong><br>
                    Embalagens necessárias: <strong>${embalagens}</strong><br>
                    Custo total estimado: <strong>${formatarValor(custoTotal)}</strong>
                `;
            }

            tipoRegisto.addEventListener('change', atualizarTipoDespesa);
            categoria.addEventListener('change', atualizarCamposCategoria);

            [
                consumoDiario,
                dataInicio,
                dataFim,
                quantidadePorEmbalagem,
                precoEmbalagem,
                unidade
            ].forEach(campo => {
                campo.addEventListener('input', calcularResumoConsumo);
                campo.addEventListener('change', calcularResumoConsumo);
            });

            atualizarTipoDespesa();
        });
    </script>
</body>
</html>