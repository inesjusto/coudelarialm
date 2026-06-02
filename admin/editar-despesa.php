<?php
include __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Despesa</title>
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
                    <h1>Editar Despesa</h1>
                    <p>Altere os dados da despesa selecionada.</p>
                </div>

                <a href="despesas.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/editar-despesa.php" method="POST" novalidate>
                        <input type="hidden" id="id" name="id">

                        <div class="campo">
                            <label for="fornecedor_id">Fornecedor</label>
                            <select id="fornecedor_id" name="fornecedor_id">
                                <option value="">Sem fornecedor</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="categoria">Categoria</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Selecione</option>
                                <option value="Alimentação">Alimentação</option>
                                <option value="Palha">Palha</option>
                                <option value="Feno">Feno</option>
                                <option value="Veterinário">Veterinário</option>
                                <option value="Ferrador">Ferrador</option>
                                <option value="Medicamentos">Medicamentos</option>
                                <option value="Transporte">Transporte</option>
                                <option value="Equipamento">Equipamento</option>
                                <option value="Manutenção">Manutenção</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="4"></textarea>
                        </div>

                        <div class="campo">
                            <label for="valor">Valor (€)</label>
                            <input type="text" id="valor" name="valor" required>
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
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Alterações</button>
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
        document.addEventListener('DOMContentLoaded', async function () {
            const params = new URLSearchParams(window.location.search);
            const id = params.get('id');

            if (!id) {
                alert('ID da despesa não encontrado.');
                window.location.href = 'despesas.php';
                return;
            }

            try {

            function formatarValorInput(valor) {
                if (valor === null || valor === undefined || valor === '') return '';

                return Number(valor).toFixed(2)
                    .replace('.', ',')
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

                await carregarSelectFornecedores();

                const resposta = await fetch(`../backend/buscar-despesa.php?id=${id}`);
                const despesa = await resposta.json();

                if (despesa.erro) {
                    alert(despesa.erro);
                    window.location.href = 'despesas.php';
                    return;
                }

                document.getElementById('id').value = despesa.id ?? '';
                document.getElementById('fornecedor_id').value = despesa.fornecedor_id ?? '';
                document.getElementById('categoria').value = despesa.categoria ?? '';
                document.getElementById('descricao').value = despesa.descricao ?? '';
                document.getElementById('valor').value = formatarValorInput(despesa.valor);
                document.getElementById('data_despesa').value = despesa.data_despesa ?? '';
                document.getElementById('metodo_pagamento').value = despesa.metodo_pagamento ?? '';
                document.getElementById('estado_pagamento').value = despesa.estado_pagamento ?? 'pendente';
            } catch (erro) {
                console.error(erro);
                alert('Erro ao carregar os dados da despesa.');
            }
        });
    </script>
</body>
</html>