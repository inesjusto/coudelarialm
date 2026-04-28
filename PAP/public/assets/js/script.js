const menuToggle = document.getElementById("menu-toggle");
const navbar = document.getElementById("navbar");

if (menuToggle && navbar) {
  menuToggle.addEventListener("click", () => {
    navbar.classList.toggle("show");
  });
}

document.addEventListener("DOMContentLoaded", () => {
  carregarCavalos();
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
    const cavalos = normalizarRespostaCavalos(data);

    if (loading) loading.style.display = "none";

    if (!Array.isArray(cavalos) || cavalos.length === 0) {
      if (vazio) vazio.style.display = "block";
      return;
    }

    lista.innerHTML = "";

    for (const cavalo of cavalos) {
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
          <p><strong>Descrição:</strong> ${escapeHtml(cavalo.descricao || "Sem descrição disponível.")}</p>
          <a href="contactos.html" class="btn btn-outline">Pedir Informações</a>
        </div>
      `;

      lista.appendChild(card);
    }
  } catch (error) {
    console.error("Erro ao carregar cavalos:", error);

    if (loading) loading.style.display = "none";
    if (erro) erro.style.display = "block";
  }
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
    console.warn("Imagem vazia ou null. A usar fallback.");
    return fallback;
  }

  const nomeImagem = String(imagem).trim();

  const candidatos = [];

  // Se já vier URL absoluta
  if (
    nomeImagem.startsWith("http://") ||
    nomeImagem.startsWith("https://")
  ) {
    candidatos.push(nomeImagem);
  }

  // Se já vier com caminho tipo assets/...
  if (nomeImagem.includes("assets/img/cavalos/")) {
    const caminhoNormalizado = nomeImagem.substring(
      nomeImagem.indexOf("assets/img/cavalos/")
    );
    candidatos.push(caminhoNormalizado);
    candidatos.push("/" + caminhoNormalizado);
  }

  // Caso mais comum: só nome do ficheiro
  candidatos.push(`assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`/public/assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`../public/assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`../assets/img/cavalos/${nomeImagem}`);
  candidatos.push(`assets/img/${nomeImagem}`);
  candidatos.push(`/public/assets/img/${nomeImagem}`);

  // Remove duplicados
  const unicos = [...new Set(candidatos)];

  for (const caminho of unicos) {
    const existe = await testarImagem(caminho);
    if (existe) {
      console.log(`Imagem encontrada para "${nomeImagem}":`, caminho);
      return caminho;
    }
  }

  console.warn(`Nenhum caminho funcionou para a imagem "${nomeImagem}". A usar fallback.`);
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

function escapeHtml(valor) {
  return String(valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}