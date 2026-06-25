/* ========================= */
/* ANIMAÇÃO AO ROLAR         */
/* ========================= */
function reveal() {
  var reveals = document.querySelectorAll(".reveal");
  for (var i = 0; i < reveals.length; i++) {
    var windowHeight = window.innerHeight;
    var elementTop = reveals[i].getBoundingClientRect().top;
    if (elementTop < windowHeight - 50) {
      reveals[i].classList.add("active");
    }
  }
}
window.addEventListener("scroll", reveal);
window.addEventListener("load", reveal); // Trigger on load too

/* ========================= */
/* CHAT FLUTUANTE            */
/* ========================= */

var chatToggle    = document.getElementById("chatToggle");
var chatWindow    = document.getElementById("chatWindow");
var chatFechar    = document.getElementById("chatFechar");
var chatMensagens = document.getElementById("chatMensagens");
var chatInput     = document.getElementById("chatInput");
var chatSend      = document.getElementById("chatSend");

var respostas = {
  "🏡 Informações": "O Condomínio Nosso Zimbo fica em Luanda, Camama. Oferecemos apartamentos de T1 a T5 com piscina, segurança 24h e áreas verdes. Áreas: 55m² a 180m². Preços de entrada: 1.200.000 Kz (T1) a 4.500.000 Kz (T5).",
  "🛠️ Reportar problema":"Lamentamos o incidente. Por favor, ligue para <strong>+244 931 612 489</strong> ou envie um e-mail para <strong>contacto@condominionossozimbo.com</strong> descrevendo o problema. 🛠️",
  "📞 Administração": "Pode contactar a administração:<br>📞 <strong>+244 931 612 489</strong><br>📧 <strong>contacto@condominionossozimbo.com</strong><br>🕗 Seg-Sex: 08h00 – 16h30",
  "💰 Preços": "Preços de entrada: T1 (55m²) - 1.200.000 Kz, T2 (75m²) - 1.800.000 Kz, T3 (100m²) - 2.500.000 Kz, T4 (130m²) - 3.200.000 Kz, T5 (180m²) - 4.500.000 Kz.",
  "💵 Mensalidade": "Renda mensal: 140.000 Kz. Taxa de serviços: 15.000 Kz. Segurança: 25.000 Kz. Manutenção: 10.000 Kz. Total: 190.000 Kz.",
  "🏠 Tipologias": "Tipologias disponíveis: T1 (55m²), T2 (75m²), T3 (100m²), T4 (130m²), T5 (180m²).",
  "🔑 Acesso": "Para acessar a área do morador, clique em 'Login' no menu e entre com as credenciais fornecidas pela administração.",
  "📍 Localização": "Estamos localizados em Luanda, Camama, Angola. Consulte o mapa no final da página.",
  "🕐 Horário": "Funcionamento: Segunda a Sexta, das 08h00 às 16h30.",
  "📅 Visitas": "Para marcar visita, contacte: +244 931 612 489 ou envie e-mail para agendar.",
  "📷 Fotos": "Veja fotos das residências na seção 'Compartimentos' desta página.",
  "🏊 Piscina": "Sim, o condomínio possui piscina para moradores.",
  "🚗 Estacionamento": "Estacionamento privado disponível para todos os apartamentos.",
  "🌿 Áreas verdes": "O condomínio conta com áreas verdes e espaços de lazer.",
  "🛡️ Segurança": "Vigilância 24h, portaria com controlo de acesso e segurança física.",
  "💳 Pagamento": "Pagamentos via transferência bancária ou depósito. IBAN disponível no portal do morador."
};

var opcoesHTML =
  '<button class="opcao-btn" onclick="enviarOpcao(this)">🏡 Informações</button>' +
  '<button class="opcao-btn" onclick="enviarOpcao(this)">💵 Mensalidade</button>' +
  '<button class="opcao-btn" onclick="enviarOpcao(this)">💰 Preços</button>' +
  '<button class="opcao-btn" onclick="enviarOpcao(this)">📍 Localização</button>' +
  '<button class="opcao-btn" onclick="enviarOpcao(this)">📞 Administração</button>';

var opcoesReduzidasHTML =
  '<button class="opcao-btn" onclick="enviarOpcao(this)">🏡 Informações</button>' +
  '<button class="opcao-btn" onclick="enviarOpcao(this)">💵 Mensalidade</button>' +
  '<button class="opcao-btn" onclick="enviarOpcao(this)">💰 Preços</button>' +
  '<button class="opcao-btn" onclick="enviarOpcao(this)">📍 Localização</button>';

function adicionarMsg(texto, tipo) {
  if(!chatMensagens) return;
  var div = document.createElement("div");
  div.className = "msg " + tipo;
  div.innerHTML = texto;
  chatMensagens.appendChild(div);
  chatMensagens.scrollTop = chatMensagens.scrollHeight;
}

function mostrarTyping(callback) {
  if(!chatMensagens) return;
  var dot = document.createElement("div");
  dot.className = "typing";
  dot.innerHTML = "<span></span><span></span><span></span>";
  chatMensagens.appendChild(dot);
  chatMensagens.scrollTop = chatMensagens.scrollHeight;
  setTimeout(function () {
    dot.remove();
    callback();
  }, 1000);
}

function mostrarOpcoes(html) {
  if(!chatMensagens) return;
  var opcoes = document.createElement("div");
  opcoes.className = "chat-opcoes";
  opcoes.innerHTML = html;
  chatMensagens.appendChild(opcoes);
  chatMensagens.scrollTop = chatMensagens.scrollHeight;
}

function enviarOpcao(btn) {
  var texto = btn.textContent.trim();
  var opcoesCont = btn.closest(".chat-opcoes");
  if (opcoesCont) opcoesCont.remove();

  adicionarMsg(texto, "user");

  var resposta = respostas[texto] || "Obrigado pela sua mensagem! Um agente irá responder em breve. ";

  mostrarTyping(function () {
    adicionarMsg(resposta, "bot");
    mostrarTyping(function () {
      adicionarMsg("Posso ajudar com mais alguma coisa?", "bot");
      mostrarOpcoes(opcoesHTML);
    });
  });
}

function enviarMensagem() {
  if(!chatInput) return;
  var texto = chatInput.value.trim();
  if (!texto) return;

  adicionarMsg(texto, "user");
  chatInput.value = "";

  var lowerText = texto.toLowerCase();
  var respostaEncontrada = null;
  
  for (var key in respostas) {
    var keyLower = key.toLowerCase().replace(/[🐗🛠️📞📍💰💵🏡]/g, '').trim();
    if (lowerText.includes(keyLower) || lowerText.includes(keyLower.slice(0, -1))) {
      respostaEncontrada = respostas[key];
      break;
    }
  }

  if (!respostaEncontrada) {
    var keywords = [
      {keys: ["preço", "preco", "preços", "valor", "valores", "custo", "custos", "t1", "t2", "t3", "t4", "t5", "apartamento", "tipologia", "tipologias"], resposta: respostas["💰 Preços"]},
      {keys: ["mensalidade", "renda", "quota", "taxa", "manutenção", "segurança", "serviço", "servico"], resposta: respostas["💵 Mensalidade"]},
      {keys: ["login", "acesso", "acessar", "conta", "entrar"], resposta: respostas["🔑 Acesso"]},
      {keys: ["onde fica", "localização", "localizacao", "endereco", "endereço", "camama"], resposta: respostas["📍 Localização"]},
      {keys: ["horário", "horario", "funciona", "aberto", "fechado"], resposta: respostas["🕐 Horário"]},
      {keys: ["visita", "visitar", "agendar"], resposta: respostas["📅 Visitas"]},
      {keys: ["foto", "fotos", "imagem", "imagens", "compartimento"], resposta: respostas["📷 Fotos"]},
      {keys: ["piscina", "piscinas"], resposta: respostas["🏊 Piscina"]},
      {keys: ["estacionamento", "garagem", "estacionar"], resposta: respostas["🚗 Estacionamento"]},
      {keys: ["verde", "área verde", "areas verdes", "lazer"], resposta: respostas["🌿 Áreas verdes"]},
      {keys: ["pagamento", "transferencia", "transferência", "iban", "banco", "depósito", "deposito"], resposta: respostas["💳 Pagamento"]},
      {keys: ["segurança", "vigilancia", "vigilância", "portaria"], resposta: respostas["🛡️ Segurança"]},
      {keys: ["recibo", "upload", "comprovativo", "1mb"], resposta: respostas["💳 Pagamento"]},
      {keys: ["morador", "informação", "informacao"], resposta: respostas["🏡 Informações"]}
    ];
    for (var i = 0; i < keywords.length; i++) {
      for (var j = 0; j < keywords[i].keys.length; j++) {
        if (lowerText.includes(keywords[i].keys[j])) {
          respostaEncontrada = keywords[i].resposta;
          break;
        }
      }
      if (respostaEncontrada) break;
    }
  }

  mostrarTyping(function () {
    if (respostaEncontrada) {
      adicionarMsg(respostaEncontrada, "bot");
      mostrarTyping(function () {
        adicionarMsg("Posso ajudar com mais alguma coisa?", "bot");
        mostrarOpcoes(opcoesHTML);
      });
    } else {
      adicionarMsg(
        "Para essa dúvida específica, consulte a administração: +244 931 612 489 ou contacto@condominionossozimbo.com",
        "bot"
      );
      mostrarOpcoes(opcoesReduzidasHTML);
    }
  });
}

if (chatToggle && chatWindow && chatFechar) {
  chatToggle.addEventListener("click", function () {
    chatWindow.classList.toggle("aberto");
    var badge = chatToggle.querySelector(".chat-badge");
    if (badge) badge.style.display = "none";
  });

  chatFechar.addEventListener("click", function () {
    chatWindow.classList.remove("aberto");
  });
  
  if(chatSend) {
    chatSend.addEventListener("click", enviarMensagem);
  }
  if(chatInput) {
    chatInput.addEventListener("keypress", function(e) {
      if(e.key === "Enter") enviarMensagem();
    });
  }
}

/* ========================= */
/* LUXURY PARALLAX           */
/* ========================= */
const luxuryVisuals = document.getElementById('luxuryParallax');
if (luxuryVisuals) {
  const images = luxuryVisuals.querySelectorAll('.luxury-image-wrapper, .luxury-glass-card');
  
  luxuryVisuals.addEventListener('mousemove', (e) => {
    const { width, height } = luxuryVisuals.getBoundingClientRect();
    const centerX = width / 2;
    const centerY = height / 2;
    
    // Position adjusted for scroll
    const rect = luxuryVisuals.getBoundingClientRect();
    const mouseX = e.clientX - (rect.left + centerX);
    const mouseY = e.clientY - (rect.top + centerY);
    
    images.forEach(img => {
      const speed = img.getAttribute('data-speed') || 0.5;
      const x = (mouseX * speed) / 25;
      const y = (mouseY * speed) / 25;
      
      img.style.transform = `translate3d(${x}px, ${y}px, 0)`;
    });
  });
  
  luxuryVisuals.addEventListener('mouseleave', () => {
    images.forEach(img => {
      img.style.transform = `translate3d(0, 0, 0)`;
    });
  });
}
