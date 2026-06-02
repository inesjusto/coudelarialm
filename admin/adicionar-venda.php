<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

/*
    Clientes:
    - Só aparecem clientes com estado Cliente
    - Não repete nomes
    - Mostra apenas o nome
*/
$stmtClientes = $conn->prepare("
    SELECT 
        MIN(id) AS id,
        TRIM(nome) AS nome
    FROM clientes
    WHERE TRIM(LOWER(estado)) = 'cliente'
      AND nome IS NOT NULL
      AND TRIM(nome) <> ''
    GROUP BY TRIM(LOWER(nome))
    ORDER BY nome ASC
");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

/*
    Cavalos:
    - Só aparecem cavalos disponíveis
    - Não aparecem cavalos já vendidos
    - Mostra apenas o nome
*/
$stmtCavalos = $conn->prepare("
    SELECT id, nome, preco
    FROM cavalos
    WHERE TRIM(LOWER(estado)) IN ('disponível', 'disponivel')
      AND id NOT IN (
          SELECT cavalo_id
          FROM vendas_cavalos
      )
    ORDER BY nome ASC
");
$stmtCavalos->execute();
$cavalos = $stmtCavalos->fetchAll(PDO::FETCH_ASSOC);

$erro = $_GET['erro'] ?? '';

function mostrarErroVenda($erro) {
    switch ($erro) {
        case 'campos':
            return 'Preencha todos os campos obrigatórios.';
        case 'cliente':
            return 'O cliente selecionado não é válido ou não tem estado Cliente.';
        case 'cavalo':
            return 'O cavalo selecionado não está disponível para venda.';
        case 'duplicado':
            return 'Este cavalo já foi vendido.';
        case 'valor':
            return 'O valor da venda deve ser superior a 0.';
        case 'metodo':
            return 'Método de requisição inválido.';
        default:
            return 'Ocorreu um erro ao registar a venda.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Venda</title>
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
                <a href="despesas.php" class="nav-link">Despesas</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Adicionar Venda</h1>
                    <p>Preencha os dados para registar a venda de um cavalo.</p>
                </div>

                <a href="vendas.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/cadastrar-venda.php" method="POST" id="form-adicionar-venda">

                        <div class="campo">
                            <label>Cliente</label>
                            <select name="cliente_id" required>
                                <option value="" disabled selected>Selecione</option>

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
                            <label for="cavalo_id">Cavalo</label>
                            <select id="cavalo_id" name="cavalo_id" required>
                                <option value="" disabled selected>Selecione</option>

                                <?php if (empty($cavalos)): ?>
                                    <option value="" disabled>Não existem cavalos disponíveis</option>
                                <?php else: ?>
                                    <?php foreach ($cavalos as $cavalo): ?>
                                        <option 
                                            value="<?= htmlspecialchars($cavalo['id']) ?>"
                                            data-preco="<?= htmlspecialchars($cavalo['preco'] ?? '') ?>"
                                        >
                                            <?= htmlspecialchars($cavalo['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="data_venda">Data da Venda</label>
                            <input 
                                type="date" 
                                id="data_venda" 
                                name="data_venda" 
                                value="<?= date('Y-m-d') ?>" 
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="valor">Valor (€)</label>
                            <input 
                                type="text" 
                                id="valor" 
                                name="valor" 
                                placeholder="Ex.: 5000 ou 5.000,00" 
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="metodo_pagamento">Método de Pagamento</label>
                            <select id="metodo_pagamento" name="metodo_pagamento">
                                <option value="">Selecione</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Transferência Bancária">Transferência Bancária</option>
                                <option value="MB Way">MB Way</option>
                                <option value="Cartão">Cartão</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="observacoes">Observações</label>
                            <textarea 
                                id="observacoes" 
                                name="observacoes" 
                                rows="5"
                            ></textarea>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">
                                Guardar Venda
                            </button>

                            <a href="vendas.php" class="btn-cancelar">
                                Cancelar
                            </a>
                        </div>

                        <?php if (!empty($erro)): ?>
                            <p class="mensagem-formulario erro">
                                <?= htmlspecialchars(mostrarErroVenda($erro)) ?>
                            </p>
                        <?php else: ?>
                            <p id="mensagem-formulario" class="mensagem-formulario"></p>
                        <?php endif; ?>

                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectCavalo = document.getElementById('cavalo_id');
            const inputValor = document.getElementById('valor');

            if (!selectCavalo || !inputValor) {
                return;
            }

            selectCavalo.addEventListener('change', function () {
                const optionSelecionada = selectCavalo.options[selectCavalo.selectedIndex];
                const preco = optionSelecionada.getAttribute('data-preco');

                if (preco && inputValor.value.trim() === '') {
                    inputValor.value = preco;
                }
            });
        });
    </script>
</body>
</html>