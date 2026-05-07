const TIPOS_IMAGEM_PERMITIDOS = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const TAMANHO_MAXIMO_IMAGEM = 20 * 1024 * 1024;

let graficoCavalos = null;
let graficoClientes = null;
let graficoRacasDetalhe = null;

function pedidoComSucesso(resultado) {
    return resultado?.sucesso === true || resultado?.status === 'success';
}

function mensagemErroResultado(resultado, fallback = 'Ocorreu um erro.') {
    return resultado?.erro || resultado?.message || fallback;
}

function normalizarPreco(valor) {
    if (!valor) return '';

    return valor
        .toString()
        .trim()
        .replace(/\s/g, '')
        .replace(/\./g, '')
        .replace(',', '.');
}

function normalizarAltura(valor) {
    if (!valor) return '';

    return valor
        .toString()
        .trim()
        .replace(/\s/g, '')
        .replace(',', '.');
}

function formatarPreco(valor) {
    return Number(valor ?? 0).toLocaleString('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    });
}

function formatarPrecoParaInput(valor) {
    if (valor === null || valor === undefined || valor === '') return '';

    return Number(valor).toLocaleString('pt-PT', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function formatarAlturaParaInput(valor) {
    if (valor === null || valor === undefined || valor === '') return '';

    return Number(valor).toLocaleString('pt-PT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatarTextoApresentacao(valor, fallback = '-') {
    if (valor === null || valor === undefined) return fallback;

    let texto = valor.toString().trim().toLowerCase();
    if (texto === '') return fallback;

    // Mapa de correções com acentos
    const mapa = {
        'informacao': 'Informação',
        'visita': 'Visita',
        'compra': 'Compra'
    };

    if (mapa[texto]) return mapa[texto];

    // substituir underscores por espaço
    texto = texto.replace(/_/g, ' ');

    // primeira letra maiúscula
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

async function carregarTabelaCavalos() {
    const tabela = document.getElementById('tabela-cavalos');
    if (!tabela) return;

    try {
        const resposta = await fetch('../backend/listar-cavalos.php');

        if (!resposta.ok) {
            throw new Error(`HTTP ${resposta.status}`);
        }

        const cavalos = await resposta.json();

        tabela.innerHTML = '';

        if (cavalos.erro) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="7" class="mensagem-vazia">${cavalos.erro}</td>
                </tr>
            `;
            return;
        }

        if (!Array.isArray(cavalos) || cavalos.length === 0) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="7" class="mensagem-vazia">Nenhum cavalo cadastrado.</td>
                </tr>
            `;
            return;
        }

        cavalos.forEach(cavalo => {
            const linha = document.createElement('tr');

            linha.innerHTML = `
    <td>${cavalo.id ?? '-'}</td>
    <td><strong>${cavalo.nome ?? '-'}</strong></td>
    <td>${formatarTextoApresentacao(cavalo.sexo)}</td>
    <td>${cavalo.idade ?? '-'}</td>
    <td>${formatarTextoApresentacao(cavalo.raca)}</td>
    <td>${formatarPreco(cavalo.preco)}</td>
    <td>${cavalo.estado_aluguer === 'alugado' ? 'Alugado' : 'Disponível'}</td>
    <td>
        <div class="acoes">
            <button class="btn-editar" onclick="editarCavalo(${cavalo.id})">Editar</button>
            <button class="btn-apagar" onclick="apagarCavalo(${cavalo.id})">Apagar</button>
        </div>
    </td>
`;

            tabela.appendChild(linha);
        });
    } catch (erro) {
        tabela.innerHTML = `
            <tr>
                <td colspan="7" class="mensagem-vazia">Erro ao carregar os cavalos.</td>
            </tr>
        `;
        console.error('Erro ao carregar tabela de cavalos:', erro);
    }
}

function editarCavalo(id) {
    window.location.href = `editar-cavalo.php?id=${id}`;
}

async function apagarCavalo(id) {
    const confirmar = confirm('Tens a certeza que queres apagar este cavalo?');
    if (!confirmar) return;

    try {
        const resposta = await fetch('../backend/apagar-cavalo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const resultado = await resposta.json();

        if (pedidoComSucesso(resultado)) {
            alert(resultado.message || 'Cavalo apagado com sucesso.');
            carregarTabelaCavalos();
        } else {
            alert(mensagemErroResultado(resultado, 'Erro ao apagar cavalo.'));
        }
    } catch (erro) {
        console.error('Erro ao apagar cavalo:', erro);
        alert('Erro ao apagar cavalo.');
    }
}

function validarImagemSelecionada(file, obrigatoria = false) {
    if (!file) {
        if (obrigatoria) {
            return { valida: false, erro: 'Selecione uma imagem.' };
        }
        return { valida: true, erro: '' };
    }

    if (!TIPOS_IMAGEM_PERMITIDOS.includes(file.type)) {
        return { valida: false, erro: 'Formato inválido. Use JPG, JPEG, PNG ou WEBP.' };
    }

    if (file.size > TAMANHO_MAXIMO_IMAGEM) {
        return { valida: false, erro: 'A imagem excede o tamanho máximo de 20 MB.' };
    }

    return { valida: true, erro: '' };
}

function configurarPreviewImagem() {
    const inputImagem = document.getElementById('imagem');
    const nomeImagem = document.getElementById('nome-imagem');
    const previewImagem = document.getElementById('preview-imagem');
    const erroImagem = document.getElementById('erro-imagem');
    const formAdicionar = document.getElementById('form-adicionar-cavalo');

    if (!inputImagem) return;

    inputImagem.addEventListener('change', function () {
        const file = inputImagem.files[0];
        const obrigatoria = !!formAdicionar;
        const validacao = validarImagemSelecionada(file, obrigatoria);

        if (!validacao.valida) {
            inputImagem.value = '';

            if (nomeImagem) nomeImagem.textContent = 'Nenhum ficheiro selecionado';
            if (erroImagem) erroImagem.textContent = validacao.erro;

            if (previewImagem && formAdicionar) {
                previewImagem.src = '';
                previewImagem.style.display = 'none';
            }
            return;
        }

        if (erroImagem) erroImagem.textContent = '';

        if (file) {
            if (nomeImagem) nomeImagem.textContent = file.name;
            if (previewImagem) {
                previewImagem.src = URL.createObjectURL(file);
                previewImagem.style.display = 'block';
            }
        } else {
            if (nomeImagem) nomeImagem.textContent = 'Nenhum ficheiro selecionado';
            if (previewImagem && formAdicionar) {
                previewImagem.src = '';
                previewImagem.style.display = 'none';
            }
        }
    });
}

function configurarFormularioAdicionar() {
    const formAdicionar = document.getElementById('form-adicionar-cavalo');
    if (!formAdicionar) return;

    formAdicionar.addEventListener('submit', async function (event) {
        event.preventDefault();

        const mensagem = document.getElementById('mensagem-formulario');
        const erroImagem = document.getElementById('erro-imagem');
        const imagem = document.getElementById('imagem')?.files[0];
        const validacao = validarImagemSelecionada(imagem, true);

        if (!validacao.valida) {
            if (erroImagem) erroImagem.textContent = validacao.erro;
            return;
        }

        if (erroImagem) erroImagem.textContent = '';
        if (mensagem) {
            mensagem.textContent = '';
            mensagem.classList.remove('sucesso', 'erro');
        }

        const preco = normalizarPreco(document.getElementById('preco')?.value || '');
        const altura = normalizarAltura(document.getElementById('altura')?.value || '');

        const formData = new FormData();
        formData.append('nome', document.getElementById('nome').value);
        formData.append('sexo', document.getElementById('sexo').value);
        formData.append('idade', document.getElementById('idade').value);
        formData.append('raca', document.getElementById('raca').value);
        formData.append('altura', altura);
        formData.append('cor', document.getElementById('cor').value);
        formData.append('preco', preco);
        formData.append('estado', document.getElementById('estado').value);
        formData.append('descricao', document.getElementById('descricao').value);
        formData.append('imagem', imagem);

        try {
            const resposta = await fetch('../backend/cadastrar-cavalo.php', {
                method: 'POST',
                body: formData
            });

            const texto = await resposta.text();
            let resultado = {};

            try {
                resultado = JSON.parse(texto);
            } catch (e) {
                console.error('Resposta não JSON:', texto);
                throw new Error('Resposta inválida do servidor.');
            }

            if (pedidoComSucesso(resultado)) {
                if (mensagem) {
                    mensagem.textContent = resultado.message || 'Cavalo adicionado com sucesso.';
                    mensagem.classList.add('sucesso');
                }

                formAdicionar.reset();

                const nomeImagem = document.getElementById('nome-imagem');
                const previewImagem = document.getElementById('preview-imagem');

                if (nomeImagem) nomeImagem.textContent = 'Nenhum ficheiro selecionado';
                if (previewImagem) {
                    previewImagem.src = '';
                    previewImagem.style.display = 'none';
                }

                setTimeout(() => {
                    window.location.href = 'cavalos.php';
                }, 1200);
            } else {
                if (mensagem) {
                    mensagem.textContent = mensagemErroResultado(resultado, 'Erro ao adicionar cavalo.');
                    mensagem.classList.add('erro');
                }
            }
        } catch (erro) {
            console.error('Erro ao adicionar cavalo:', erro);
            if (mensagem) {
                mensagem.textContent = 'Erro ao adicionar cavalo.';
                mensagem.classList.add('erro');
            }
        }
    });
}

function obterParametroId() {
    const parametros = new URLSearchParams(window.location.search);
    return parametros.get('id');
}

async function carregarDadosCavaloParaEdicao() {
    const formEditar = document.getElementById('form-editar-cavalo');
    if (!formEditar) return;

    const id = obterParametroId();
    const mensagem = document.getElementById('mensagem-formulario');

    if (!id) {
        if (mensagem) mensagem.textContent = 'ID do cavalo não encontrado.';
        return;
    }

    try {
        const resposta = await fetch(`../backend/buscar-cavalo.php?id=${id}`);
        const cavalo = await resposta.json();

        if (cavalo.erro) {
            if (mensagem) mensagem.textContent = cavalo.erro;
            return;
        }

        document.getElementById('cavalo-id').value = cavalo.id ?? '';
        document.getElementById('nome').value = cavalo.nome ?? '';
        document.getElementById('sexo').value = cavalo.sexo ?? '';
        document.getElementById('idade').value = cavalo.idade ?? '';
        document.getElementById('raca').value = cavalo.raca ?? '';

        if (document.getElementById('altura')) {
            document.getElementById('altura').value = formatarAlturaParaInput(cavalo.altura);
        }

        if (document.getElementById('cor')) {
            document.getElementById('cor').value = cavalo.cor ?? '';
        }

        if (document.getElementById('preco')) {
            document.getElementById('preco').value = formatarPrecoParaInput(cavalo.preco);
        }

        if (document.getElementById('estado')) {
            document.getElementById('estado').value = cavalo.estado ?? '';
        }

        document.getElementById('descricao').value = cavalo.descricao ?? '';

        const preview = document.getElementById('preview-imagem');
        if (preview && cavalo.imagem) {
            preview.src = `../public/assets/img/cavalos/${cavalo.imagem}`;
            preview.style.display = 'block';
        }
    } catch (erro) {
        console.error('Erro ao carregar dados do cavalo:', erro);
        if (mensagem) mensagem.textContent = 'Erro ao carregar os dados do cavalo.';
    }
}

function configurarFormularioEditar() {
    const formEditar = document.getElementById('form-editar-cavalo');
    if (!formEditar) return;

    carregarDadosCavaloParaEdicao();

    formEditar.addEventListener('submit', async function (event) {
        event.preventDefault();

        const mensagem = document.getElementById('mensagem-formulario');
        const erroImagem = document.getElementById('erro-imagem');
        const imagem = document.getElementById('imagem')?.files[0];
        const validacao = validarImagemSelecionada(imagem, false);

        if (!validacao.valida) {
            if (erroImagem) erroImagem.textContent = validacao.erro;
            return;
        }

        if (erroImagem) erroImagem.textContent = '';
        if (mensagem) {
            mensagem.textContent = '';
            mensagem.classList.remove('sucesso', 'erro');
        }

        const preco = normalizarPreco(document.getElementById('preco')?.value || '');
        const altura = normalizarAltura(document.getElementById('altura')?.value || '');

        const formData = new FormData();
        formData.append('id', document.getElementById('cavalo-id').value);
        formData.append('nome', document.getElementById('nome').value);
        formData.append('sexo', document.getElementById('sexo').value);
        formData.append('idade', document.getElementById('idade').value);
        formData.append('raca', document.getElementById('raca').value);
        formData.append('altura', altura);
        formData.append('cor', document.getElementById('cor')?.value || '');
        formData.append('preco', preco);
        formData.append('estado', document.getElementById('estado')?.value || '');
        formData.append('descricao', document.getElementById('descricao').value);

        if (imagem) {
            formData.append('imagem', imagem);
        }

        try {
            const resposta = await fetch('../backend/editar-cavalo.php', {
                method: 'POST',
                body: formData
            });

            const texto = await resposta.text();
            let resultado = {};

            try {
                resultado = JSON.parse(texto);
            } catch (e) {
                console.error('Resposta não JSON:', texto);
                throw new Error('Resposta inválida do servidor.');
            }

            if (pedidoComSucesso(resultado)) {
                if (mensagem) {
                    mensagem.textContent = resultado.message || 'Cavalo editado com sucesso.';
                    mensagem.classList.add('sucesso');
                }

                setTimeout(() => {
                    window.location.href = 'cavalos.php';
                }, 1200);
            } else {
                if (mensagem) {
                    mensagem.textContent = mensagemErroResultado(resultado, 'Erro ao editar cavalo.');
                    mensagem.classList.add('erro');
                }
            }
        } catch (erro) {
            console.error('Erro ao editar cavalo:', erro);
            if (mensagem) {
                mensagem.textContent = 'Erro ao editar cavalo.';
                mensagem.classList.add('erro');
            }
        }
    });
}

async function carregarTabelaClientes() {
    const tabela = document.getElementById('tabela-clientes');
    if (!tabela) return;

    try {
        const resposta = await fetch('../backend/listar-clientes.php');
        const clientes = await resposta.json();

        tabela.innerHTML = '';

        if (clientes.erro) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="8" class="mensagem-vazia">${clientes.erro}</td>
                </tr>
            `;
            return;
        }

        if (!Array.isArray(clientes) || clientes.length === 0) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="8" class="mensagem-vazia">Sem clientes.</td>
                </tr>
            `;
            return;
        }

        clientes.forEach(cliente => {
            const linha = document.createElement('tr');

            linha.innerHTML = `
                <td>${cliente.id ?? '-'}</td>
                <td>${cliente.nome ?? '-'}</td>
                <td>${cliente.email ?? '-'}</td>
                <td>${cliente.telefone ?? '-'}</td>
                <td>${formatarTextoApresentacao(cliente.tipo_interesse)}</td>
                <td>${formatarTextoApresentacao(cliente.estado)}</td>
                <td>${cliente.cavalos ?? '—'}</td>
                <td>
                    <div class="acoes">
                        <button class="btn-editar" onclick="editarCliente(${cliente.id})">Editar</button>
                        <button class="btn-apagar" onclick="apagarCliente(${cliente.id})">Apagar</button>
                    </div>
                </td>
            `;

            tabela.appendChild(linha);
        });
    } catch (erro) {
        console.error('Erro ao carregar clientes:', erro);
        tabela.innerHTML = `
            <tr>
                <td colspan="8" class="mensagem-vazia">Erro ao carregar clientes.</td>
            </tr>
        `;
    }
}

function editarCliente(id) {
    window.location.href = `editar-cliente.php?id=${id}`;
}

async function apagarCliente(id) {
    const confirmar = confirm('Tens a certeza que queres apagar este cliente?');
    if (!confirmar) return;

    try {
        const resposta = await fetch('../backend/apagar-cliente.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const resultado = await resposta.json();

        if (pedidoComSucesso(resultado)) {
            alert(resultado.message || 'Cliente apagado com sucesso.');
            carregarTabelaClientes();
        } else {
            alert(mensagemErroResultado(resultado, 'Erro ao apagar cliente.'));
        }
    } catch (erro) {
        console.error('Erro ao apagar cliente:', erro);
        alert('Erro ao apagar cliente.');
    }
}

async function carregarClienteEdicao() {
    const form = document.getElementById('form-editar-cliente');
    if (!form) return;

    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (!id) return;

    try {
        const res = await fetch(`../backend/buscar-cliente.php?id=${id}`);
        const cliente = await res.json();

        if (cliente.erro) return;

        document.getElementById('cliente-id').value = cliente.id;
        document.getElementById('nome').value = cliente.nome ?? '';
        document.getElementById('email').value = cliente.email ?? '';
        document.getElementById('telefone').value = cliente.telefone ?? '';
        document.getElementById('mensagem').value = cliente.mensagem ?? '';
    } catch (erro) {
        console.error('Erro ao carregar cliente para edição:', erro);
    }
}

function configurarEditarCliente() {
    const form = document.getElementById('form-editar-cliente');
    if (!form) return;

    carregarClienteEdicao();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const mensagem = document.getElementById('mensagem-formulario');
        if (mensagem) {
            mensagem.textContent = '';
            mensagem.classList.remove('sucesso', 'erro');
        }

        const formData = new FormData();
        formData.append('id', document.getElementById('cliente-id').value);
        formData.append('nome', document.getElementById('nome').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('telefone', document.getElementById('telefone').value);
        formData.append('mensagem', document.getElementById('mensagem').value);

        try {
            const res = await fetch('../backend/editar-cliente.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await res.json();

            if (resultado.sucesso) {
                if (mensagem) {
                    mensagem.textContent = 'Cliente atualizado com sucesso.';
                    mensagem.classList.add('sucesso');
                }

                setTimeout(() => {
                    window.location.href = 'clientes.php';
                }, 1200);
            } else {
                if (mensagem) {
                    mensagem.textContent = resultado.erro || 'Erro ao editar cliente.';
                    mensagem.classList.add('erro');
                }
            }
        } catch (erro) {
            console.error('Erro ao editar cliente:', erro);
            if (mensagem) {
                mensagem.textContent = 'Erro ao editar cliente.';
                mensagem.classList.add('erro');
            }
        }
    });
}

function destruirGrafico(instancia) {
    if (instancia) {
        instancia.destroy();
    }
}

function obterPaleta(quantidade) {
    const base = [
        '#00C853',
        '#3B82F6',
        '#F59E0B',
        '#A855F7',
        '#EF4444',
        '#14B8A6',
        '#F97316',
        '#8B5CF6',
        '#22C55E',
        '#EAB308'
    ];

    const cores = [];
    for (let i = 0; i < quantidade; i++) {
        cores.push(base[i % base.length]);
    }
    return cores;
}

async function carregarGraficoCavalos(agrupamento = 'sexo') {
    const canvas = document.getElementById('grafico-cavalos');
    if (!canvas) return;

    try {
        const resposta = await fetch(`../backend/stats-cavalos.php?agrupamento=${agrupamento}`);
        const resultado = await resposta.json();

        if (resultado.erro) {
            console.error(resultado.erro);
            return;
        }

        document.getElementById('total-cavalos').textContent = resultado.total ?? 0;

        const dados = Array.isArray(resultado.dados) ? resultado.dados : [];
        const labels = dados.map(item => item.label || 'Sem dados');
        const valores = dados.map(item => Number(item.total) || 0);
        const cores = obterPaleta(labels.length);

        destruirGrafico(graficoCavalos);

        graficoCavalos = new Chart(canvas, {
            type: agrupamento === 'raca' ? 'bar' : 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: agrupamento === 'raca' ? 'Quantidade' : 'Cavalos',
                    data: valores,
                    backgroundColor: cores,
                    borderColor: '#24283B',
                    borderWidth: agrupamento === 'raca' ? 0 : 4,
                    borderRadius: agrupamento === 'raca' ? 8 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: agrupamento !== 'raca',
                        labels: {
                            color: '#FFFFFF'
                        }
                    }
                },
                scales: agrupamento === 'raca' ? {
                    x: {
                        ticks: {
                            color: '#FFFFFF'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.06)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#FFFFFF',
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.06)'
                        }
                    }
                } : {}
            }
        });
    } catch (erro) {
        console.error('Erro ao carregar gráfico de cavalos:', erro);
    }
}

async function carregarGraficoClientes(agrupamento = 'tipo') {
    const canvas = document.getElementById('grafico-clientes');
    if (!canvas) return;

    try {
        const resposta = await fetch(`../backend/stats-clientes.php?agrupamento=${agrupamento}`);
        const resultado = await resposta.json();

        if (resultado.erro) {
            console.error(resultado.erro);
            return;
        }

        document.getElementById('total-clientes').textContent = resultado.total ?? 0;

        const dados = Array.isArray(resultado.dados) ? resultado.dados : [];
        const labels = dados.map(item => {
    let texto = (item.label || 'Sem tipo').toString().trim().toLowerCase();

    // Correções de acentos
    const mapa = {
        'informacao': 'Informação',
        'compra': 'Compra',
        'visita': 'Visita'
    };

    return mapa[texto] || (texto.charAt(0).toUpperCase() + texto.slice(1));
});
        const valores = dados.map(item => Number(item.total) || 0);
        const cores = obterPaleta(labels.length);

        destruirGrafico(graficoClientes);

        graficoClientes = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: cores,
                    borderColor: '#24283B',
                    borderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#FFFFFF'
                        }
                    }
                }
            }
        });
    } catch (erro) {
        console.error('Erro ao carregar gráfico de clientes:', erro);
    }
}

async function carregarGraficoRacasDetalhe() {
    const canvas = document.getElementById('grafico-racas-detalhe');
    if (!canvas) return;

    try {
        const resposta = await fetch('../backend/stats-cavalos.php?agrupamento=raca');
        const resultado = await resposta.json();

        if (resultado.erro) {
            console.error(resultado.erro);
            return;
        }

        const dados = Array.isArray(resultado.dados) ? resultado.dados : [];
        const labels = dados.map(item => item.label || 'Sem raça');
        const valores = dados.map(item => Number(item.total) || 0);
        const cores = obterPaleta(labels.length);

        destruirGrafico(graficoRacasDetalhe);

        graficoRacasDetalhe = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Quantidade',
                    data: valores,
                    backgroundColor: cores,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            color: '#FFFFFF',
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.06)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#FFFFFF'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    } catch (erro) {
        console.error('Erro ao carregar gráfico detalhado de raças:', erro);
    }
}

function configurarFiltrosGraficos() {

    function ativarBotoes(containerId, callback) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const botoes = container.querySelectorAll('.filtro-btn');

        botoes.forEach(btn => {
            btn.addEventListener('click', () => {

                botoes.forEach(b => b.classList.remove('ativo'));
                btn.classList.add('ativo');

                callback(btn.dataset.value);
            });
        });
    }

    ativarBotoes('filtro-cavalos', carregarGraficoCavalos);
    ativarBotoes('filtro-clientes', carregarGraficoClientes);
}

async function carregarTabelaFornecedores() {
    const tabela = document.getElementById('tabela-fornecedores');
    if (!tabela) return;

    try {
        const resposta = await fetch('../backend/listar-fornecedores.php');

        if (!resposta.ok) {
            throw new Error(`HTTP ${resposta.status}`);
        }

        const fornecedores = await resposta.json();

        tabela.innerHTML = '';

        if (fornecedores.erro) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="8" class="mensagem-vazia">${fornecedores.erro}</td>
                </tr>
            `;
            return;
        }

        if (!Array.isArray(fornecedores) || fornecedores.length === 0) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="8" class="mensagem-vazia">Nenhum fornecedor registado.</td>
                </tr>
            `;
            return;
        }

        fornecedores.forEach(fornecedor => {
            const linha = document.createElement('tr');

            linha.innerHTML = `
                <td>${fornecedor.id ?? '-'}</td>
                <td><strong>${fornecedor.nome ?? '-'}</strong></td>
                <td>${fornecedor.nif ?? '-'}</td>
                <td>${fornecedor.telefone ?? '-'}</td>
                <td>${fornecedor.email ?? '-'}</td>
                <td>${fornecedor.tipo_fornecedor ?? '-'}</td>
                <td>${fornecedor.data_criacao ?? '-'}</td>
                <td>
                    <div class="acoes">
                        <button class="btn-editar" onclick="editarFornecedor(${fornecedor.id})">Editar</button>
                        <button class="btn-apagar" onclick="apagarFornecedor(${fornecedor.id})">Apagar</button>
                    </div>
                </td>
            `;

            tabela.appendChild(linha);
        });
    } catch (erro) {
        tabela.innerHTML = `
            <tr>
                <td colspan="8" class="mensagem-vazia">Erro ao carregar fornecedores.</td>
            </tr>
        `;
        console.error('Erro ao carregar fornecedores:', erro);
    }
}

function editarFornecedor(id) {
    window.location.href = `editar-fornecedor.php?id=${id}`;
}

async function apagarFornecedor(id) {
    const confirmar = confirm('Tens a certeza que queres apagar este fornecedor?');
    if (!confirmar) return;

    try {
        const resposta = await fetch('../backend/apagar-fornecedor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const resultado = await resposta.json();

        if (pedidoComSucesso(resultado)) {
            alert(resultado.message || 'Fornecedor apagado com sucesso.');
            carregarTabelaFornecedores();
        } else {
            alert(mensagemErroResultado(resultado, 'Erro ao apagar fornecedor.'));
        }
    } catch (erro) {
        console.error('Erro ao apagar fornecedor:', erro);
        alert('Erro ao apagar fornecedor.');
    }
}

async function carregarTabelaDespesas() {
    const tabela = document.getElementById('tabela-despesas');
    if (!tabela) return;

    try {
        const resposta = await fetch('../backend/listar-despesas.php');

        if (!resposta.ok) {
            throw new Error(`HTTP ${resposta.status}`);
        }

        const despesas = await resposta.json();

        tabela.innerHTML = '';

        if (despesas.erro) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="10" class="mensagem-vazia">${despesas.erro}</td>
                </tr>
            `;
            return;
        }

        if (!Array.isArray(despesas) || despesas.length === 0) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="10" class="mensagem-vazia">Nenhuma despesa registada.</td>
                </tr>
            `;
            return;
        }

        despesas.forEach(despesa => {
            const linha = document.createElement('tr');

            linha.innerHTML = `
                <td>${despesa.id ?? '-'}</td>
                <td>${despesa.fornecedor_nome ?? 'Sem fornecedor'}</td>
                <td>${despesa.cavalo_nome ?? 'Despesa geral'}</td>
                <td>${despesa.categoria ?? '-'}</td>
                <td>${despesa.descricao ?? '-'}</td>
                <td><strong>${parseFloat(despesa.valor).toFixed(2)} €</strong></td>
                <td>${despesa.data_despesa ?? '-'}</td>
                <td>${despesa.metodo_pagamento ?? '-'}</td>
                <td>${despesa.estado_pagamento ?? '-'}</td>
                <td>
                    <div class="acoes">
                        <button class="btn-editar" onclick="editarDespesa(${despesa.id})">Editar</button>
                        <button class="btn-apagar" onclick="apagarDespesa(${despesa.id})">Apagar</button>
                    </div>
                </td>
            `;

            tabela.appendChild(linha);
        });
    } catch (erro) {
        console.error('Erro ao carregar despesas:', erro);
        tabela.innerHTML = `
            <tr>
                <td colspan="10" class="mensagem-vazia">Erro ao carregar despesas.</td>
            </tr>
        `;
    }
}

function editarDespesa(id) {
    window.location.href = `editar-despesa.php?id=${id}`;
}

async function apagarDespesa(id) {
    const confirmar = confirm('Tens a certeza que queres apagar esta despesa?');
    if (!confirmar) return;

    try {
        const resposta = await fetch('../backend/apagar-despesa.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            alert(resultado.message);
            carregarTabelaDespesas();
        } else {
            alert(resultado.erro || 'Erro ao apagar despesa.');
        }
    } catch (erro) {
        console.error(erro);
        alert('Erro ao apagar despesa.');
    }
}

async function carregarSelectFornecedores() {
    const select = document.getElementById('fornecedor_id');
    if (!select) return;

    try {
        const resposta = await fetch('../backend/listar-fornecedores.php');
        const fornecedores = await resposta.json();

        if (!Array.isArray(fornecedores)) return;

        fornecedores.forEach(fornecedor => {
            const option = document.createElement('option');
            option.value = fornecedor.id;
            option.textContent = fornecedor.nome;
            select.appendChild(option);
        });
    } catch (erro) {
        console.error('Erro ao carregar fornecedores:', erro);
    }
}

let graficoDespesasCategorias = null;
let graficoCustoCavalos = null;

function formatarEuros(valor) {
    return `${Number(valor || 0).toFixed(2).replace('.', ',')} €`;
}

async function carregarStatsFinanceiro() {
    const totalMes = document.getElementById('financeiro-total-mes');
    const totalAno = document.getElementById('financeiro-total-ano');
    const totalPendente = document.getElementById('financeiro-total-pendente');

    const canvasCategorias = document.getElementById('grafico-despesas-categorias');
    const canvasCavalos = document.getElementById('grafico-custo-cavalos');

    if (!totalMes && !totalAno && !totalPendente && !canvasCategorias && !canvasCavalos) return;

    try {
        const resposta = await fetch('../backend/stats-financeiro.php');
        const dados = await resposta.json();

        if (!dados.sucesso) {
            console.error(dados.erro || 'Erro ao carregar estatísticas financeiras.');
            return;
        }

        if (totalMes) totalMes.textContent = formatarEuros(dados.total_mes);
        if (totalAno) totalAno.textContent = formatarEuros(dados.total_ano);
        if (totalPendente) totalPendente.textContent = formatarEuros(dados.total_pendente);

        if (canvasCategorias) {
            const labelsCategorias = dados.categorias.map(item => item.categoria || 'Sem categoria');
            const valoresCategorias = dados.categorias.map(item => Number(item.total || 0));

            if (graficoDespesasCategorias) {
                graficoDespesasCategorias.destroy();
            }

            graficoDespesasCategorias = new Chart(canvasCategorias, {
                type: 'doughnut',
                data: {
                    labels: labelsCategorias,
                    datasets: [{
                        label: 'Despesas por Categoria',
                        data: valoresCategorias
                    }]
                }
            });
        }

        if (canvasCavalos) {
            const labelsCavalos = dados.custos_cavalos.map(item => item.cavalo || 'Sem cavalo');
            const valoresCavalos = dados.custos_cavalos.map(item => Number(item.total || 0));

            if (graficoCustoCavalos) {
                graficoCustoCavalos.destroy();
            }

            graficoCustoCavalos = new Chart(canvasCavalos, {
                type: 'bar',
                data: {
                    labels: labelsCavalos,
                    datasets: [{
                        label: 'Custo mensal por cavalo (€)',
                        data: valoresCavalos
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

    } catch (erro) {
        console.error('Erro ao carregar dashboard financeiro:', erro);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    configurarPreviewImagem();
    configurarFormularioAdicionar();
    configurarFormularioEditar();
    carregarTabelaCavalos();
    carregarTabelaClientes();
    configurarEditarCliente();
    configurarFiltrosGraficos();
    carregarGraficoCavalos();
    carregarGraficoClientes();
    carregarGraficoRacasDetalhe();
    carregarTabelaFornecedores();
    carregarTabelaDespesas();
    carregarSelectFornecedores();
    carregarStatsFinanceiro();
});