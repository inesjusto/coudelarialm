<?php
include __DIR__ . '/../backend/proteger.php';
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
                <a href="fornecedores.php" class="nav-link">Fornecedores</a>
                <a href="despesas.php" class="nav-link ativo">Despesas</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Adicionar Despesa</h1>
                    <p>Registe uma nova despesa geral ou associada a um cavalo.</p>
                </div>

                <a href="despesas.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/cadastrar-despesa.php" method="POST" novalidate>
                        <div class="campo">
                            <label for="fornecedor_id">Fornecedor</label>
                            <select id="fornecedor_id" name="fornecedor_id">
                                <option value="">Sem fornecedor</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="cavalo_id">Cavalo</label>
                            <select id="cavalo_id" name="cavalo_id">
                                <option value="">Despesa geral</option>
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
                            <input type="text" id="valor" name="valor" placeholder="Ex.: 150.50" required>
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
</body>
</html>