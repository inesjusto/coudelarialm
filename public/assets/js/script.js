const menuToggle = document.getElementById("menu-toggle");
const navbar = document.getElementById("navbar");

let todosOsCavalos = [];

if (menuToggle && navbar) {
  menuToggle.addEventListener("click", () => {
    navbar.classList.toggle("show");
  });
}

document.addEventListener("DOMContentLoaded", () => {
  carregarCavalos();
  carregarCavalosDestaque();
  configurarFormularioContacto();
});

async function carregarCavalos() {
  const lista = document.getElementById("lista-cavalos");
  const loading = document.getElementById("loading-cavalos");
  const erro = document.getElementById("erro-cavalos");
  const vazio = document.getElementById("sem-cavalos");

  if (!lista) return;

  try {
    const response = await fetch("../backend/listar-cavalos.php");

    if (!response.ok) {
      throw new Error(`Erro HTTP ao carregar cavalos: ${response.status}`);
    }

    const data = await response.json();
    todosOsCavalos = normalizarRespostaCavalos(data);

    if (loading) loading.style.display = "none";

    if (!Array.isArray(todosOsCavalos) || todosOsCavalos.length === 0) {
      if (vazio) vazio.style.display = "block";
      return;
    }

    await renderizarCavalos(todosOsCavalos);
    configurarFiltrosCavalos();

  } catch (error) {
    console.error("Erro ao carregar cavalos:", error);

    if (loading) loading.style.display = "none";
    if (erro) erro.style.display = "block";
  }
}

async function renderizarCavalos(cavalos) {
  const lista = document.getElementById("lista-cavalos");
  const vazio = document.getElementById("sem-cavalos");

  if (!lista) return;

  lista.innerHTML = "";

  if (!Array.isArray(cavalos) || cavalos.length === 0) {
    if (vazio) vazio.style.display = "block";
    return;
  }

  if (vazio) vazio.style.display = "none";

  for (const cavalo of cavalos) {
    const card = await criarCardCavalo(cavalo, true);
    lista.appendChild(card);
  }
}

function configurarFiltrosCavalos() {
  const botoes = document.querySelectorAll(".horse-filter-btn");

  if (!botoes.length) return;

  botoes.forEach((botao) => {
    botao.addEventListener("click", () => {
      botoes.forEach((b) => b.classList.remove("active"));
      botao.classList.add("active");

      const sexo = botao.dataset.sexo;

      if (sexo === "todos") {
        renderizarCavalos(todosOsCavalos);
        return;
      }

      const filtrados = todosOsCavalos.filter((cavalo) => {
        return String(cavalo.sexo || "").toLowerCase() === sexo.toLowerCase();
      });

      renderizarCavalos(filtrados);
    });
  });
}

async function carregarCavalosDestaque() {
  const lista = document.getElementById("lista-cavalos-destaque");
  const loading = document.getElementById("loading-cavalos-destaque");
  const erro = document.getElementById("erro-cavalos-destaque");
  const vazio = document.getElementById("sem-cavalos-destaque");

  if (!lista) return;

  try {
    const response = await fetch("../backend/listar-cavalos.php");

    if (!response.ok) {
      throw new Error(`Erro HTTP ao carregar cavalos em destaque: ${response.status}`);
    }

    const data = await response.json();
    const cavalos = normalizarRespostaCavalos(data).slice(0, 3);

    if (loading) loading.style.display = "none";

    if (!Array.isArray(cavalos) || cavalos.length === 0) {
      if (vazio) vazio.style.display = "block";
      return;
    }

    lista.innerHTML = "";

    for (const cavalo of cavalos) {
      const card = await criarCardCavalo(cavalo, false);
      lista.appendChild(card);
    }
  } catch (error) {
    console.error("Erro ao carregar cavalos em destaque:", error);

    if (loading) loading.style.display = "none";
    if (erro) erro.style.display = "block";
  }
}

async function criarCardCavalo(cavalo, mostrarDescricao = true) {
  const imagem = await resolverImagemCavalo(cavalo.imagem);

  const card = document.createElement("div");
  card.className = "horse-card";

  card.innerHTML = `
    <div class="horse-image">
      <img 
        src="${imagem}" 
        alt="${escapeHtml(cavalo.nome || "Cavalo")}"
        onerror="this.onerror=null;this.src='assets/img/cavalos/default.jpg';"
      >
    </div>

    <div class="horse-content">
      <h3>${escapeHtml(cavalo.nome || "Sem nome")}</h3>
      <p><strong>Raça:</strong> ${escapeHtml(cavalo.raca || "Não definida")}</p>
      <p><strong>Sexo:</strong> ${escapeHtml(cavalo.sexo || "Não definido")}</p>
      <p><strong>Idade:</strong> ${escapeHtml(formatarIdade(cavalo.idade))}</p>
      <p><strong>Preço:</strong> ${escapeHtml(formatarPreco(cavalo.preco))}</p>
      ${
        mostrarDescricao
          ? `<p><strong>Descrição:</strong> ${escapeHtml(cavalo.descricao || "Sem descrição disponível.")}</p>`
          : ""
      }
      <a href="${mostrarDescricao ? "contactos.html" : "cavalos.html"}" class="btn btn-outline">
        ${mostrarDescricao ? "Pedir Informações" : "Ver Mais"}
      </a>
    </div>
  `;

  return card;
}

function normalizarRespostaCavalos(data) {
  if (Array.isArray(data)) return data;
  if (data && Array.isArray(data.cavalos)) return data.cavalos;
  if (data && Array.isArray(data.data)) return data.data;
  if (data && Array.isArray(data.resultado)) return data.resultado;
  return [];
}

async function resolverImagemCavalo(imagem) {
  const fallback = "assets/img/cavalos/default.jpg";

  if (!imagem || String(imagem).trim() === "") {
    return fallback;
  }

  const nomeImagem = String(imagem).trim();
  const candidatos = [];

  if (
    nomeImagem.startsWith("http://") ||
    nomeImagem.startsWith("https://")
  ) {
    candidatos.push(nomeImagem);
  }

  if (nomeImagem.includes("assets/img/cavalos/")) {
    const caminhoNormalizado = nomeImagem.substring(
      nomeImagem.indexOf("assets/img/cavalos/")
    );
    candidatos.push(caminhoNormalizado);
    candidatos.push("/" + caminhoNormalizado);
  }

  candidatos.push(`assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`/public/assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`../public/assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`../assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`assets/img/${nomeImagem}`);
  candidatos.push(`/public/assets/img/${nomeImagem}`);

  const unicos = [...new Set(candidatos)];

  for (const caminho of unicos) {
    const existe = await testarImagem(caminho);
    if (existe) {
      return caminho;
    }
  }

  return fallback;
}

function testarImagem(src) {
  return new Promise((resolve) => {
    const img = new Image();

    img.onload = () => resolve(true);
    img.onerror = () => resolve(false);

    img.src = src;
  });
}

function formatarPreco(preco) {
  if (preco === null || preco === undefined || preco === "") {
    return "Sob consulta";
  }

  const valor = Number(preco);

  if (Number.isNaN(valor)) {
    return String(preco);
  }

  return valor.toLocaleString("pt-PT", {
    style: "currency",
    currency: "EUR"
  });
}

function formatarIdade(idade) {
  if (idade === null || idade === undefined || idade === "") {
    return "Não definida";
  }

  return `${idade} anos`;
}

function configurarFormularioContacto() {
  const formularioContacto = document.querySelector(".contact-form");
  const mensagemContacto = document.getElementById("mensagem-contacto");

  if (!formularioContacto) return;

  formularioContacto.addEventListener("submit", async (event) => {
    event.preventDefault();

    const botao = formularioContacto.querySelector('button[type="submit"]');
    const textoOriginal = botao ? botao.textContent : "";

    if (mensagemContacto) {
      mensagemContacto.textContent = "";
      mensagemContacto.className = "mensagem-contacto";
    }

    if (botao) {
      botao.disabled = true;
      botao.textContent = "A enviar...";
    }

    try {
      const formData = new FormData(formularioContacto);

      const response = await fetch("../backend/enviar-contacto.php", {
        method: "POST",
        body: formData
      });

      const resultado = await response.json();

      if (mensagemContacto) {
        mensagemContacto.textContent = resultado.mensagem;
        mensagemContacto.className = resultado.sucesso
          ? "mensagem-contacto sucesso"
          : "mensagem-contacto erro";
      }

      if (resultado.sucesso) {
        formularioContacto.reset();
      }

    } catch (error) {
      console.error("Erro ao enviar formulário:", error);

      if (mensagemContacto) {
        mensagemContacto.textContent = "Erro ao enviar mensagem. Tente novamente.";
        mensagemContacto.className = "mensagem-contacto erro";
      }
    } finally {
      if (botao) {
        botao.disabled = false;
        botao.textContent = textoOriginal;
      }
    }
  });
}

function escapeHtml(valor) {
  return String(valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}