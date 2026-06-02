<?php
include __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

$stmtClientes = $conn->query("
    SELECT id, nome
    FROM clientes
    WHERE TRIM(estado) = 'Cliente'
    ORDER BY nome ASC
");
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Aula</title>
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
                <a href="aulas.php" class="nav-link ativo">Aulas</a>
                <a href="vendas.php" class="nav-link">Vendas</a>
                <a href="fornecedores.php" class="nav-link">Fornecedores</a>
                <a href="despesas.php" class="nav-link">Despesas</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Adicionar Aula</h1>
                    <p>Registe uma nova aula da coudelaria.</p>
                </div>

                <a href="aulas.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/cadastrar-aula.php" method="POST" novalidate>
                        <div class="campo">
                            <label for="cliente_id">Cliente</label>
                            <select id="cliente_id" name="cliente_id">
                                <option value="">Sem cliente</option>

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
                            <select id="cavalo_id" name="cavalo_id">
                                <option value="">Escolha primeiro a data da aula</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="data_aula">Data da Aula</label>
                            <input type="date" id="data_aula" name="data_aula" required>
                        </div>

                        <div class="campo">
                            <label for="hora_inicio">Hora de Início</label>
                            <input type="time" id="hora_inicio" name="hora_inicio" required>
                        </div>

                        <div class="campo">
                            <label for="hora_fim">Hora de Fim</label>
                            <input type="time" id="hora_fim" name="hora_fim" required>
                        </div>

                        <div class="campo">
                            <label for="tipo_aula">Tipo de Aula</label>
                            <select id="tipo_aula" name="tipo_aula">
                                <option value="">Selecione</option>
                                <option value="Individual">Individual</option>
                                <option value="Grupo">Grupo</option>
                                <option value="Iniciação">Iniciação</option>
                                <option value="Avançada">Avançada</option>
                                <option value="Passeio">Passeio</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="preco">Preço (€)</label>
                            <input type="text" id="preco" name="preco" placeholder="Ex.: 25.00" required>
                        </div>

                        <div class="campo">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado">
                                <option value="marcada">Marcada</option>
                                <option value="realizada">Realizada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="observacoes">Observações</label>
                            <textarea id="observacoes" name="observacoes" rows="4"></textarea>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Aula</button>
                            <a href="aulas.php" class="btn-cancelar">Cancelar</a>
                        </div>

                        <p id="mensagem-formulario" class="mensagem-formulario"></p>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataAula = document.getElementById('data_aula');
            const cavaloSelect = document.getElementById('cavalo_id');

            if (!dataAula || !cavaloSelect) return;

            async function carregarCavalosDisponiveis() {
                const data = dataAula.value;

                cavaloSelect.innerHTML = '<option value="">A carregar...</option>';

                if (!data) {
                    cavaloSelect.innerHTML = '<option value="">Escolha primeiro a data da aula</option>';
                    return;
                }

                try {
                    const resposta = await fetch(`../backend/cavalos-disponiveis-aula.php?data=${encodeURIComponent(data)}`);
                    const cavalos = await resposta.json();

                    cavaloSelect.innerHTML = '<option value="">Sem cavalo</option>';

                    if (!Array.isArray(cavalos) || cavalos.length === 0) {
                        cavaloSelect.innerHTML += '<option value="" disabled>Nenhum cavalo disponível nesta data</option>';
                        return;
                    }

                    cavalos.forEach(cavalo => {
                        const option = document.createElement('option');
                        option.value = cavalo.id;
                        option.textContent = cavalo.nome;
                        cavaloSelect.appendChild(option);
                    });

                } catch (erro) {
                    console.error('Erro ao carregar cavalos disponíveis:', erro);
                    cavaloSelect.innerHTML = '<option value="">Erro ao carregar cavalos</option>';
                }
            }

            dataAula.addEventListener('change', carregarCavalosDisponiveis);
        });
    </script>
</body>
</html>