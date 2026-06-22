/* ========================= */
/* ANIMAÇÃO AO ROLAR         */
/* ========================= */
function reveal() {
  var reveals = document.querySelectorAll(".reveal");
  for (var i = 0; i < reveals.length; i++) {
    var windowHeight = window.innerHeight;
    var elementTop = reveals[i].getBoundingClientRect().top;
    if (elementTop < windowHeight - 100) {
      reveals[i].classList.add("active");
    }
  }
}
window.addEventListener("scroll", reveal);

/* ========================= */
/* CHAT FLUTUANTE            */
/* ========================= */

var chatToggle    = document.getElementById("chatToggle");
var chatWindow    = document.getElementById("chatWindow");
var chatFechar    = document.getElementById("chatFechar");
var chatMensagens = document.getElementById("chatMensagens");
var chatInput     = document.getElementById("chatInput");
var chatSend      = document.getElementById("chatSend");

if (chatToggle && chatWindow && chatFechar && chatMensagens && chatInput && chatSend) {
  chatToggle.addEventListener("click", function () {
    chatWindow.classList.toggle("aberto");
    var badge = chatToggle.querySelector(".chat-badge");
    if (badge) badge.style.display = "none";
  });

  chatFechar.addEventListener("click", function () {
    chatWindow.classList.remove("aberto");
  });

  var respostas = {
    "🏡 Informações":      "O Condomínio Nosso Zimbo fica em Luanda, Camama. Oferecemos apartamentos V3 com piscina, segurança 24h por dia e áreas verdes. ",
    "🛠️ Reportar problema":"Lamentamos o incidente. Por favor, ligue para <strong>+244 931 612 489</strong> ou envie um e-mail para <strong>contacto@condominionossozimbo.com</strong> descrevendo o problema. 🛠️",
    "📞 Administração":    "Pode contactar a administração:<br>📞 <strong>+244 931 612 489</strong><br>📧 <strong>contacto@condominionossozimbo.com</strong><br>🕗 Seg-Sex: 08h00 – 16h30"
  };

  var opcoesHTML =
    '<button class="opcao-btn" onclick="enviarOpcao(this)">🏡 Informações</button>' +
    '<button class="opcao-btn" onclick="enviarOpcao(this)">📞 Administração</button>';

  var opcoesReduzidasHTML =
    '<button class="opcao-btn" onclick="enviarOpcao(this)">📞 Administração</button>' +
    '<button class="opcao-btn" onclick="enviarOpcao(this)">📅 Marcar visita</button>';

  function adicionarMsg(texto, tipo) {
    var div = document.createElement("div");
    div.className = "msg " + tipo;
    div.innerHTML = texto;
    chatMensagens.appendChild(div);
    chatMensagens.scrollTop = chatMensagens.scrollHeight;
  }

  function mostrarTyping(callback) {
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
    var texto = chatInput.value.trim();
    if (!texto) return;

    adicionarMsg(texto, "user");
    chatInput.value = "";

    mostrarTyping(function () {
      adicionarMsg(
        "Obrigado pela sua mensagem! Um membro da nossa equipa irá responder em breve. Enquanto isso, pode escolher uma das opções abaixo:",
        "bot"
      );
      mostrarOpcoes(opcoesReduzidasHTML);
    });
  }

  chatSend.addEventListener("click", enviarMensagem);
  chatInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") enviarMensagem();
  });
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
    
    const mouseX = e.offsetX - centerX;
    const mouseY = e.offsetY - centerY;
    
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
