<?php
require_once __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('ID da aula inválido.');
}

$stmtAula = $conn->prepare("
    SELECT *
    FROM aulas
    WHERE id = :id
    LIMIT 1
");

$stmtAula->execute([
    ':id' => $id
]);

$aula = $stmtAula->fetch(PDO::FETCH_ASSOC);

if (!$aula) {
    die('Aula não encontrada.');
}

$stmtClientes = $conn->query("
    SELECT id, nome
    FROM clientes
    WHERE TRIM(LOWER(estado)) = 'cliente'
    ORDER BY nome ASC
");

$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$stmtCavalos = $conn->prepare("
    SELECT 
        c.id,
        c.nome
    FROM cavalos c
    WHERE 
        (
            c.id = :cavalo_atual
            OR TRIM(LOWER(c.estado)) IN ('disponível', 'disponivel', 'alugado')
        )
        AND NOT EXISTS (
            SELECT 1
            FROM alugueres a
            WHERE a.cavalo_id = c.id
              AND TRIM(LOWER(a.estado)) IN ('ativo', 'concluido')
              AND DATE(:data_aula) >= DATE(a.data_inicio)
              AND DATE(:data_aula) <= DATE(COALESCE(a.data_fim, '9999-12-31'))
        )
    ORDER BY c.nome ASC
");

$stmtCavalos->execute([
    ':cavalo_atual' => $aula['cavalo_id'] ?? 0,
    ':data_aula' => $aula['data_aula']
]);

$cavalos = $stmtCavalos->fetchAll(PDO::FETCH_ASSOC);

function selecionado($valorAtual, $valorOpcao) {
    return (string)$valorAtual === (string)$valorOpcao ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aula</title>
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
                    <h1>Editar Aula</h1>
                    <p>Altere os dados da aula selecionada.</p>
                </div>

                <a href="aulas.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/editar-aula.php" method="POST">
                        <input 
                            type="hidden" 
                            name="id" 
                            value="<?= htmlspecialchars($aula['id']) ?>"
                        >

                        <div class="campo">
                            <label for="cliente_id">Cliente</label>
                            <select id="cliente_id" name="cliente_id">
                                <option value="">Sem cliente</option>

                                <?php foreach ($clientes as $cliente): ?>
                                    <option 
                                        value="<?= htmlspecialchars($cliente['id']) ?>"
                                        <?= selecionado($aula['cliente_id'], $cliente['id']) ?>
                                    >
                                        <?= htmlspecialchars($cliente['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="cavalo_id">Cavalo</label>
                            <select id="cavalo_id" name="cavalo_id">
                                <option value="">Sem cavalo</option>

                                <?php foreach ($cavalos as $cavalo): ?>
                                    <option 
                                        value="<?= htmlspecialchars($cavalo['id']) ?>"
                                        <?= selecionado($aula['cavalo_id'], $cavalo['id']) ?>
                                    >
                                        <?= htmlspecialchars($cavalo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="data_aula">Data da Aula</label>
                            <input 
                                type="text" 
                                id="data_aula" 
                                name="data_aula" 
                                class="input-data"
                                value="<?= htmlspecialchars($aula['data_aula']) ?>" 
                                placeholder="Selecione a data"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="hora_inicio">Hora de Início</label>
                            <input 
                                type="time" 
                                id="hora_inicio" 
                                name="hora_inicio" 
                                value="<?= htmlspecialchars(substr($aula['hora_inicio'], 0, 5)) ?>" 
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="hora_fim">Hora de Fim</label>
                            <input 
                                type="time" 
                                id="hora_fim" 
                                name="hora_fim" 
                                value="<?= htmlspecialchars(substr($aula['hora_fim'], 0, 5)) ?>" 
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="tipo_aula">Tipo de Aula</label>
                            <select id="tipo_aula" name="tipo_aula">
                                <option value="" <?= selecionado($aula['tipo_aula'], '') ?>>Selecione</option>
                                <option value="Individual" <?= selecionado($aula['tipo_aula'], 'Individual') ?>>Individual</option>
                                <option value="Grupo" <?= selecionado($aula['tipo_aula'], 'Grupo') ?>>Grupo</option>
                                <option value="Iniciação" <?= selecionado($aula['tipo_aula'], 'Iniciação') ?>>Iniciação</option>
                                <option value="Avançada" <?= selecionado($aula['tipo_aula'], 'Avançada') ?>>Avançada</option>
                                <option value="Passeio" <?= selecionado($aula['tipo_aula'], 'Passeio') ?>>Passeio</option>
                                <option value="Outro" <?= selecionado($aula['tipo_aula'], 'Outro') ?>>Outro</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="preco">Preço (€)</label>
                            <input 
                                type="text" 
                                id="preco" 
                                name="preco" 
                                value="<?= htmlspecialchars(number_format((float)$aula['preco'], 2, ',', '.')) ?>" 
                                placeholder="Ex.: 25,00"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado">
                                <option value="marcada" <?= selecionado($aula['estado'], 'marcada') ?>>Marcada</option>
                                <option value="realizada" <?= selecionado($aula['estado'], 'realizada') ?>>Realizada</option>
                                <option value="cancelada" <?= selecionado($aula['estado'], 'cancelada') ?>>Cancelada</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="observacoes">Observações</label>
                            <textarea 
                                id="observacoes" 
                                name="observacoes" 
                                rows="4"
                            ><?= htmlspecialchars($aula['observacoes'] ?? '') ?></textarea>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">
                                Guardar Alterações
                            </button>

                            <a href="aulas.php" class="btn-cancelar">
                                Cancelar
                            </a>
                        </div>
                    </form>

                    <form 
                        action="../backend/apagar-aula.php" 
                        method="POST" 
                        onsubmit="return confirm('Tens a certeza que queres apagar esta aula?');" 
                        style="margin-top: 20px;"
                    >
                        <input 
                            type="hidden" 
                            name="id" 
                            value="<?= htmlspecialchars($aula['id']) ?>"
                        >

                        <button type="submit" class="btn-apagar">
                            Apagar Aula
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataAula = document.getElementById('data_aula');
            const cavaloSelect = document.getElementById('cavalo_id');
            const cavaloAtual = "<?= htmlspecialchars((string)($aula['cavalo_id'] ?? '')) ?>";

            if (typeof flatpickr !== 'undefined') {
                flatpickr('#data_aula', {
                    dateFormat: 'Y-m-d',
                    locale: 'pt',
                    allowInput: true,
                    disableMobile: true,
                    onChange: carregarCavalosDisponiveis
                });
            }

            if (!dataAula || !cavaloSelect) {
                return;
            }

            async function carregarCavalosDisponiveis() {
                const data = dataAula.value;

                if (!data) {
                    return;
                }

                try {
                    const resposta = await fetch(`../backend/cavalos-disponiveis-aula.php?data=${encodeURIComponent(data)}`);
                    const cavalos = await resposta.json();

                    const valorSelecionado = cavaloSelect.value || cavaloAtual;

                    cavaloSelect.innerHTML = '<option value="">Sem cavalo</option>';

                    if (!Array.isArray(cavalos) || cavalos.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Nenhum cavalo disponível nesta data';
                        option.disabled = true;
                        cavaloSelect.appendChild(option);
                        return;
                    }

                    cavalos.forEach(cavalo => {
                        const option = document.createElement('option');
                        option.value = cavalo.id;
                        option.textContent = cavalo.nome;

                        if (String(cavalo.id) === String(valorSelecionado)) {
                            option.selected = true;
                        }

                        cavaloSelect.appendChild(option);
                    });

                } catch (erro) {
                    console.error('Erro ao carregar cavalos disponíveis:', erro);
                }
            }

            dataAula.addEventListener('change', carregarCavalosDisponiveis);
        });
    </script>
</body>
</html>