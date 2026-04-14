const TIPOS_IMAGEM_PERMITIDOS = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const TAMANHO_MAXIMO_IMAGEM = 5 * 1024 * 1024;

let graficoCavalos = null;
let graficoClientes = null;
let graficoRacasDetalhe = null;

function pedidoComSucesso(resultado) {
    return resultado?.sucesso === true || resultado?.status === 'success';
}

function mensagemErroResultado(resultado, fallback = 'Ocorreu um erro.') {
    return resultado?.erro || resultado?.message || fallback;
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
                <td>${cavalo.sexo ?? '-'}</td>
                <td>${cavalo.idade ?? '-'}</td>
                <td>${cavalo.raca ?? '-'}</td>
                <td>€ ${Number(cavalo.preco ?? 0).toFixed(2)}</td>
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
    window.location.href = `editar-cavalo.html?id=${id}`;
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
        return { valida: false, erro: 'A imagem excede o tamanho máximo de 5 MB.' };
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

        const formData = new FormData();
        formData.append('nome', document.getElementById('nome').value);
        formData.append('sexo', document.getElementById('sexo').value);
        formData.append('idade', document.getElementById('idade').value);
        formData.append('raca', document.getElementById('raca').value);
        formData.append('preco', document.getElementById('preco').value);
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

            console.log('Resposta cadastrar-cavalo:', resultado);

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

                if (resultado?.debug) {
                    console.error('Debug backend:', resultado.debug);
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
        document.getElementById('preco').value = cavalo.preco ?? '';
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

        const formData = new FormData();
        formData.append('id', document.getElementById('cavalo-id').value);
        formData.append('nome', document.getElementById('nome').value);
        formData.append('sexo', document.getElementById('sexo').value);
        formData.append('idade', document.getElementById('idade').value);
        formData.append('raca', document.getElementById('raca').value);
        formData.append('preco', document.getElementById('preco').value);
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

                if (resultado?.debug) {
                    console.error('Debug backend:', resultado.debug);
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
        const labels = dados.map(item => item.label || 'Sem tipo');
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
    const filtroCavalos = document.getElementById('filtro-cavalos');
    const filtroClientes = document.getElementById('filtro-clientes');

    if (filtroCavalos) {
        filtroCavalos.addEventListener('change', function () {
            carregarGraficoCavalos(this.value);
        });
    }

    if (filtroClientes) {
        filtroClientes.addEventListener('change', function () {
            carregarGraficoClientes(this.value);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    configurarPreviewImagem();
    configurarFormularioAdicionar();
    configurarFormularioEditar();
    carregarTabelaCavalos();
    configurarFiltrosGraficos();
    carregarGraficoCavalos();
    carregarGraficoClientes();
    carregarGraficoRacasDetalhe();
});