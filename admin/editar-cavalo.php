<?php
include __DIR__ . '/../backend/proteger.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cavalo</title>
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
                <a href="cavalos.php" class="nav-link ativo">Cavalos</a>
                <a href="clientes.php" class="nav-link">Clientes</a>
                <a href="alugueres.php" class="nav-link">Alugueres</a>
                <a href="logout.php" class="nav-link nav-link-sair">Terminar Sessão</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header admin-header-flex">
                <div class="admin-header-texto">
                    <h1>Editar Cavalo</h1>
                    <p>Atualize os dados do cavalo selecionado.</p>
                </div>

                <a href="cavalos.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form id="form-editar-cavalo" enctype="multipart/form-data" novalidate>
                        <input type="hidden" id="cavalo-id" name="id">

                        <div class="campo">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>

                        <div class="campo">
                            <label for="raca">Raça</label>
                            <input type="text" id="raca" name="raca" required>
                        </div>

                        <div class="campo">
                            <label for="sexo">Sexo</label>
                            <select id="sexo" name="sexo">
                                <option value="">Selecione</option>
                                <option value="Macho">Macho</option>
                                <option value="Fêmea">Fêmea</option>
                                <option value="Garanhão">Garanhão</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="idade">Idade</label>
                            <input type="number" id="idade" name="idade" min="0" required>
                        </div>

                        <div class="campo">
                            <label for="altura">Altura (m)</label>
                            <input type="text" id="altura" name="altura" placeholder="Ex.: 1,60">
                        </div>

                        <div class="campo">
                            <label for="cor">Cor</label>
                            <input type="text" id="cor" name="cor">
                        </div>

                        <div class="campo">
                            <label for="preco">Preço (€)</label>
                            <input type="text" id="preco" name="preco" placeholder="Ex.: 50.000" required>
                        </div>

                        <div class="campo">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado">
                                <option value="">Selecione</option>
                                <option value="Disponível">Disponível</option>
                                <option value="Reservado">Reservado</option>
                                <option value="Vendido">Vendido</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="5"></textarea>
                        </div>

                        <div class="campo">
                            <label>Imagem Atual / Nova Pré-visualização</label>
                            <img id="preview-imagem" class="imagem-preview" alt="Imagem do cavalo" style="display: none;">
                        </div>

                        <div class="campo">
                            <label>Nova Imagem (opcional)</label>

                            <div class="upload-container">
                                <label for="imagem" class="botao-upload">Selecionar nova imagem</label>
                                <span id="nome-imagem">Nenhum ficheiro selecionado</span>
                            </div>

                            <input type="file" id="imagem" name="imagem" accept="image/*" hidden>

                            <p class="ajuda-upload">Formatos permitidos: JPG, JPEG, PNG, WEBP. Tamanho máximo 20MB.</p>
                            <p id="erro-imagem" class="mensagem-erro"></p>
                        </div>

                        <div id="mensagem-formulario" class="mensagem-formulario"></div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Alterações</button>
                            <a href="cavalos.php" class="btn-cancelar">Cancelar</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>