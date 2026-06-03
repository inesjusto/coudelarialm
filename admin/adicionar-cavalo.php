<?php
include __DIR__ . '/../backend/proteger.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Cavalo</title>
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
                <a href="cavalos.php" class="nav-link ativo">Cavalos</a>
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
                    <h1>Adicionar Cavalo</h1>
                    <p>Preencha os dados para registar um novo cavalo.</p>
                </div>

                <a href="cavalos.php" class="botao-adicionar">← Voltar à Tabela</a>
            </header>

            <section class="admin-form-wrapper">
                <div class="form-container">
                    <form id="form-adicionar-cavalo" enctype="multipart/form-data" novalidate>
                        <div class="campo">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>

                        <div class="campo">
                            <label for="raca">Raça</label>

                            <select id="raca" name="raca" required>
                                <option value="">Selecione</option>
                                <option value="Akhal-Teke">Akhal-Teke</option>
                                <option value="Alter-Real">Alter-Real</option>
                                <option value="Andaluz">Andaluz</option>
                                <option value="Anglo-Árabe">Anglo-Árabe</option>
                                <option value="Appaloosa">Appaloosa</option>
                                <option value="Árabe">Árabe</option>
                                <option value="Asturcón">Asturcón</option>
                                <option value="Azteca">Azteca</option>
                                <option value="Bretão">Bretão</option>
                                <option value="Campolina">Campolina</option>
                                <option value="Cavalo Crioulo">Cavalo Crioulo</option>
                                <option value="Cavalo de Desporto Português">Cavalo de Desporto Português</option>
                                <option value="Cavalo do Sorraia">Cavalo do Sorraia</option>
                                <option value="Clydesdale">Clydesdale</option>
                                <option value="Cruzado Português">Cruzado Português</option>
                                <option value="Falabella">Falabella</option>
                                <option value="Frísio">Frísio</option>
                                <option value="Garrano">Garrano</option>
                                <option value="Gypsy Vanner">Gypsy Vanner</option>
                                <option value="Haflinger">Haflinger</option>
                                <option value="Hanoveriano">Hanoveriano</option>
                                <option value="Holsteiner">Holsteiner</option>
                                <option value="Islandês">Islandês</option>
                                <option value="KWPN">KWPN</option>
                                <option value="Lipizzan">Lipizzan</option>
                                <option value="Mangalarga">Mangalarga</option>
                                <option value="Mangalarga Marchador">Mangalarga Marchador</option>
                                <option value="Mustang">Mustang</option>
                                <option value="Oldemburgo">Oldemburgo</option>
                                <option value="Paint Horse">Paint Horse</option>
                                <option value="Palomino">Palomino</option>
                                <option value="Percheron">Percheron</option>
                                <option value="Pônei">Pônei</option>
                                <option value="Pônei Shetland">Pônei Shetland</option>
                                <option value="Puro Sangue Inglês">Puro Sangue Inglês</option>
                                <option value="Puro Sangue Lusitano">Puro Sangue Lusitano</option>
                                <option value="Quarto de Milha">Quarto de Milha</option>
                                <option value="Saddlebred Americano">Saddlebred Americano</option>
                                <option value="Selle Français">Selle Français</option>
                                <option value="Sela Francês">Sela Francês</option>
                                <option value="Shire">Shire</option>
                                <option value="Trotador Francês">Trotador Francês</option>
                                <option value="Outro">Outro</option>
                            </select>
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

                        <div class="campo campo-data-idade">
                            <div>
                                <label for="data_nascimento">Data de Nascimento</label>

                                <input
                                    type="text"
                                    id="data_nascimento"
                                    name="data_nascimento"
                                    class="input-data"
                                    placeholder="Selecione a data"
                                    required
                                >
                            </div>

                            <div class="idade-preview">
                                <span>Idade:</span>
                                <strong id="idade-calculada">—</strong>
                            </div>
                        </div>

                        <div class="campo">
                            <label for="altura">Altura (m)</label>
                            <input type="text" id="altura" name="altura" placeholder="Ex.: 1,60">
                        </div>

                        <div class="campo">
                            <label for="cor">Pelagem</label>

                            <select id="cor" name="cor">
                                <option value="">Selecione</option>
                                <option value="Alazã">Alazã</option>
                                <option value="Alazã Amarilha">Alazã Amarilha</option>
                                <option value="Alazã Tostada">Alazã Tostada</option>
                                <option value="Baia">Baia</option>
                                <option value="Baia Amarilha">Baia Amarilha</option>
                                <option value="Baia Cerdeira">Baia Cerdeira</option>
                                <option value="Branca">Branca</option>
                                <option value="Castanha">Castanha</option>
                                <option value="Castanha Clara">Castanha Clara</option>
                                <option value="Castanha Escura">Castanha Escura</option>
                                <option value="Lobuna">Lobuna</option>
                                <option value="Preta">Preta</option>
                                <option value="Rosilha">Rosilha</option>
                                <option value="Ruça">Ruça</option>
                                <option value="Ruça Pedrês">Ruça Pedrês</option>
                                <option value="Tordilha">Tordilha</option>
                                <option value="Tordilha Negra">Tordilha Negra</option>
                                <option value="Tordilha Rodada">Tordilha Rodada</option>
                                <option value="Pampa">Pampa</option>
                                <option value="Palomina">Palomina</option>
                                <option value="Zaina">Zaina</option>
                                <option value="Outra">Outra</option>
                            </select>
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
                                <option value="Alugado">Alugado</option>
                                <option value="Reservado">Reservado</option>
                                <option value="Vendido">Vendido</option>
                                <option value="Indisponível">Indisponível</option>
                                <option value="Em Tratamento">Em Tratamento</option>
                                <option value="Reformado">Reformado</option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="5"></textarea>
                        </div>

                        <div class="campo">
                            <label>Imagem</label>

                            <div class="upload-container">
                                <label for="imagem" class="botao-upload">Selecionar imagem</label>
                                <span id="nome-imagem">Nenhum ficheiro selecionado</span>
                            </div>

                            <input type="file" id="imagem" name="imagem" accept="image/*" hidden>

                            <p class="ajuda-upload">Formatos permitidos: JPG, JPEG, PNG, WEBP. Tamanho máximo: 20 MB.</p>
                            <p id="erro-imagem" class="erro-upload"></p>

                            <img id="preview-imagem" class="imagem-preview" alt="Pré-visualização da imagem" style="display: none;">
                        </div>

                        <div class="acoes-formulario">
                            <button type="submit" class="btn-editar btn-form-principal">Guardar Cavalo</button>
                            <a href="cavalos.php" class="btn-cancelar">Cancelar</a>
                        </div>

                        <p id="mensagem-formulario" class="mensagem-formulario"></p>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <script>
        /* =========================
           CALENDÁRIO DA DATA DE NASCIMENTO
        ========================= */
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('.input-data', {
                    dateFormat: 'Y-m-d',
                    locale: 'pt',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>

    <script src="assets/js/admin.js"></script>
</body>
</html>