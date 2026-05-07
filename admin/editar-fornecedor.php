<?php
include __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Fornecedor</title>
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
                <a href="fornecedores.php" class="nav-link ativo">Fornecedores</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Editar Fornecedor</h1>
                    <p>Altere os dados do fornecedor selecionado.</p>
                </div>

                <a href="fornecedores.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form action="../backend/editar-fornecedor.php" method="POST" novalidate>
                        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">

                        <div class="campo">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>

                        <div class="campo">
                            <label for="nif">NIF</label>
                            <input type="text" id="nif" name="nif">
                        </div>

                        <div class="campo">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone">
                        </div>

                        <div class="campo">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>

                        <div class="campo">
                            <label for="morada">Morada</label>
                            <textarea id="morada" name="morada" rows="4"></textarea>
                        </div>

                        <div class="campo">
                            <label for="tipo_fornecedor">Tipo de Fornecedor</label>
                            <select id="tipo_fornecedor" name="tipo_fornecedor">
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
                            <label for="observacoes">Observações</label>
                            <textarea id="observacoes" name="observacoes" rows="5"></textarea>
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Alterações</button>
                            <a href="fornecedores.php" class="btn-cancelar">Cancelar</a>
                        </div>

                        <p id="mensagem-formulario" class="mensagem-formulario"></p>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const params = new URLSearchParams(window.location.search);
            const id = params.get('id');

            if (!id) {
                alert('ID do fornecedor não encontrado.');
                window.location.href = 'fornecedores.php';
                return;
            }

            try {
                const resposta = await fetch(`../backend/buscar-fornecedor.php?id=${id}`);
                const fornecedor = await resposta.json();

                if (fornecedor.erro) {
                    alert(fornecedor.erro);
                    window.location.href = 'fornecedores.php';
                    return;
                }

                document.getElementById('id').value = fornecedor.id ?? '';
                document.getElementById('nome').value = fornecedor.nome ?? '';
                document.getElementById('nif').value = fornecedor.nif ?? '';
                document.getElementById('telefone').value = fornecedor.telefone ?? '';
                document.getElementById('email').value = fornecedor.email ?? '';
                document.getElementById('morada').value = fornecedor.morada ?? '';
                document.getElementById('tipo_fornecedor').value = fornecedor.tipo_fornecedor ?? '';
                document.getElementById('observacoes').value = fornecedor.observacoes ?? '';

            } catch (erro) {
                alert('Erro ao carregar os dados do fornecedor.');
                console.error(erro);
            }
        });
    </script>
</body>
</html>