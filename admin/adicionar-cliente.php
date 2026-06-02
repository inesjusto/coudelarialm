<?php
require_once __DIR__ . '/../backend/proteger.php';
require_once __DIR__ . '/../backend/conexao.php';

$stmt = $conn->query("SELECT id, nome FROM cavalos ORDER BY nome ASC");
$cavalos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$erro = $_GET['erro'] ?? '';

function mensagemErroCliente($erro) {
    switch ($erro) {
        case 'campos':
            return 'Preencha todos os campos obrigatórios.';
        case 'email':
            return 'Indique um email válido.';
        case 'nif':
            return 'O NIF deve conter exatamente 9 dígitos numéricos.';
        case 'tipo':
            return 'O tipo de interesse selecionado não é válido.';
        case 'estado':
            return 'O estado selecionado não é válido.';
        case 'guardar':
            return 'Ocorreu um erro ao guardar o cliente. Tente novamente.';
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
    <title>Adicionar Cliente</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
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
                    <h1>Adicionar Cliente</h1>
                    <p>Preencha os dados para registar um novo cliente.</p>
                </div>

                <a href="clientes.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/cadastrar-cliente.php" method="POST" novalidate>

                        <div class="campo">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>

                        <div class="campo">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="campo">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone">
                        </div>

                        <div class="campo">
                            <label for="nif">NIF</label>
                            <input 
                                type="text" 
                                id="nif" 
                                name="nif" 
                                maxlength="9" 
                                pattern="[0-9]{9}"
                                inputmode="numeric"
                                placeholder="Ex.: 123456789"
                                >
                        </div>

                        <div class="campo">
                            <label for="tipo_interesse">Tipo de interesse</label>
                            <select id="tipo_interesse" name="tipo_interesse" required>
                                <option value="" selected disabled>Selecione</option>
                                <option value="compra">Compra</option>
                                <option value="informacao">Informação</option>
                                <option value="visita">Visita</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado" required>
                                <option value="" selected disabled>Selecione</option>
                                <option value="potencial">Potencial</option>
                                <option value="contactado">Contactado</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="interesse">Interessado em cavalo</label>
                            <select id="interesse" name="interesse" onchange="toggleCavalo()" required>
                                <option value="" selected disabled>Selecione</option>
                                <option value="nao">Não</option>
                                <option value="sim">Sim</option>
                            </select>
                        </div>

                        <div class="campo" id="campo-cavalo" style="display: none;">
                            <label for="cavalos">Escolher cavalo(s)</label>

                            <select id="cavalos" name="cavalos[]" multiple>
                                <?php foreach ($cavalos as $cavalo): ?>
                                    <option value="<?= htmlspecialchars($cavalo['id']) ?>">
                                        <?= htmlspecialchars($cavalo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <small>Para selecionar vários cavalos, mantém CTRL pressionado e clica nos cavalos.</small>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Cliente</button>
                            <a href="clientes.php" class="btn-cancelar">Cancelar</a>
                        </div>

                        <?php if (mensagemErroCliente($erro) !== ''): ?>
                            <p id="mensagem-formulario" class="mensagem-formulario mensagem-erro">
                                <?= htmlspecialchars(mensagemErroCliente($erro)) ?>
                            </p>
                        <?php else: ?>
                            <p id="mensagem-formulario" class="mensagem-formulario"></p>
                        <?php endif; ?>

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