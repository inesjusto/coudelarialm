<?php
require_once __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('ID de cliente inválido.');
}

$stmtCliente = $conn->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
$stmtCliente->execute([':id' => $id]);
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die('Cliente não encontrado.');
}

$stmtCavalos = $conn->query("SELECT id, nome FROM cavalos ORDER BY nome ASC");
$cavalos = $stmtCavalos->fetchAll(PDO::FETCH_ASSOC);

$stmtClienteCavalos = $conn->prepare("
    SELECT cavalo_id 
    FROM clientes_cavalos 
    WHERE cliente_id = :cliente_id
");
$stmtClienteCavalos->execute([':cliente_id' => $id]);
$cavalosSelecionados = $stmtClienteCavalos->fetchAll(PDO::FETCH_COLUMN);

$cavalosSelecionados = array_map('intval', $cavalosSelecionados);

$interesse = !empty($cavalosSelecionados) ? 'sim' : 'nao';
$tipoInteresseAtual = $cliente['tipo_interesse'] ?? '';
$estadoAtual = $cliente['estado'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
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
                <a href="clientes.php" class="nav-link ativo">Clientes</a>
                <a href="alugueres.php" class="nav-link">Alugueres</a>
                <a href="fornecedores.php" class="nav-link">Fornecedores</a>
                <a href="despesas.php" class="nav-link">Despesas</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Editar Cliente</h1>
                    <p>Atualize os dados do cliente selecionado.</p>
                </div>

                <a href="clientes.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/editar-cliente.php" method="POST" novalidate>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id']) ?>">

                        <div class="campo">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
                        </div>

                        <div class="campo">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required>
                        </div>

                        <div class="campo">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>">
                        </div>

                        <div class="campo">
                            <label for="tipo_interesse">Tipo de interesse</label>
                            <select id="tipo_interesse" name="tipo_interesse" required>
                                <option value="" disabled <?= $tipoInteresseAtual === '' ? 'selected' : '' ?>>Selecione</option>
                                <option value="compra" <?= $tipoInteresseAtual === 'compra' ? 'selected' : '' ?>>Compra</option>
                                <option value="informacao" <?= $tipoInteresseAtual === 'informacao' ? 'selected' : '' ?>>Informação</option>
                                <option value="visita" <?= $tipoInteresseAtual === 'visita' ? 'selected' : '' ?>>Visita</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado" required>
                                <option value="" disabled <?= $estadoAtual === '' ? 'selected' : '' ?>>Selecione</option>
                                <option value="potencial" <?= $estadoAtual === 'potencial' ? 'selected' : '' ?>>Potencial</option>
                                <option value="contactado" <?= $estadoAtual === 'contactado' ? 'selected' : '' ?>>Contactado</option>
                                <option value="cliente" <?= $estadoAtual === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="interesse">Interessado em cavalo</label>
                            <select id="interesse" name="interesse" onchange="toggleCavalo()" required>
                                <option value="nao" <?= $interesse === 'nao' ? 'selected' : '' ?>>Não</option>
                                <option value="sim" <?= $interesse === 'sim' ? 'selected' : '' ?>>Sim</option>
                            </select>
                        </div>

                        <div class="campo" id="campo-cavalo" style="<?= $interesse === 'sim' ? 'display: block;' : 'display: none;' ?>">
                            <label for="cavalos">Escolher cavalo(s)</label>

                            <select id="cavalos" name="cavalos[]" multiple>
                                <?php foreach ($cavalos as $cavalo): ?>
                                    <option value="<?= htmlspecialchars($cavalo['id']) ?>"
                                        <?= in_array((int) $cavalo['id'], $cavalosSelecionados, true) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cavalo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <small>Para selecionar vários cavalos, mantém CTRL pressionado e clica nos cavalos.</small>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Alterações</button>
                            <a href="clientes.php" class="btn-cancelar">Cancelar</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        function toggleCavalo() {
            const interesse = document.getElementById('interesse').value;
            const campoCavalo = document.getElementById('campo-cavalo');
            const selectCavalos = document.getElementById('cavalos');

            if (interesse === 'sim') {
                campoCavalo.style.display = 'block';
            } else {
                campoCavalo.style.display = 'none';
                selectCavalos.selectedIndex = -1;
            }
        }
    </script>
</body>
</html>