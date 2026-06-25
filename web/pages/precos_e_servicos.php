<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Preços e Modalidades — Condomínio Nosso Zimbo</title>
  
  <!-- Estilos Globais e Fontes -->
  <link rel="stylesheet" href="../css/nosso-zimbo-admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --gold: #b4914a;
      --gold-light: #d4bc8d;
      --gold-dark: #8c6d2d;
      --text-main: #1e293b;
      --text-soft: #64748b;
      --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      --glass: rgba(255, 255, 255, 0.8);
    }

    body {
      background: var(--bg-gradient);
      color: var(--text-main);
      font-family: 'Outfit', sans-serif;
      margin: 0;
      overflow-x: hidden;
    }

    /* Header and Subnav area */
    .header-fixed-area {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      z-index: 1000;
      padding-top: 20px;
      pointer-events: none; /* Let clicks through to background if needed */
    }
    .nav-float, .tabs-nav-sub { pointer-events: auto; }

    .nav-float {
      width: 90%;
      max-width: 1200px;
      padding: 15px 30px;
      background: var(--glass);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-radius: 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      border: 1px solid rgba(255,255,255,0.3);
      margin-bottom: 15px;
    }

    .tabs-nav-sub {
      display: flex;
      gap: 10px;
      background: var(--glass);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      padding: 8px;
      border-radius: 40px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .tabs-nav-sub .tab-trigger {
      padding: 10px 25px;
      font-size: 0.85rem;
      border: none;
    }

    .nav-logo { font-family: 'DM Serif Display', serif; font-size: 1.4rem; color: var(--gold); text-decoration: none; }
    .nav-btn { background: var(--gold); color: #fff !important; padding: 10px 25px; border-radius: 25px; text-decoration: none; font-weight: 600; transition: 0.3s; }
    .nav-btn:hover { background: var(--gold-dark); transform: scale(1.05); }

    /* Hero Section */
    .hero-pricing {
      padding: 160px 5% 100px;
      background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1600&q=80');
      background-size: cover;
      background-position: center;
      text-align: center;
      clip-path: ellipse(150% 100% at 50% 0%);
    }
    .hero-pricing h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 4rem;
      color: #fff;
      margin-bottom: 20px;
    }
    .hero-pricing p {
      color: rgba(255,255,255,0.7);
      font-size: 1.2rem;
      max-width: 600px;
      margin: 0 auto;
    }

    /* Tabs Component */
    .tabs-container {
      max-width: 1200px;
      margin: -50px auto 0;
      background: #fff;
      border-radius: 30px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.1);
      padding: 40px;
      position: relative;
      z-index: 5;
    }
    .tab-trigger {
      background: none;
      cursor: pointer;
      font-weight: 600;
      color: var(--text-soft);
      transition: 0.3s;
      border-radius: 40px;
    }
    .tab-trigger.active {
      background: var(--gold);
      color: #fff !important;
      box-shadow: 0 5px 15px rgba(180, 145, 74, 0.3);
    }

    /* Tipology Cards */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }
    .luxury-card {
      background: #f8fafc;
      border-radius: 25px;
      padding: 40px;
      border: 1px solid #e2e8f0;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
    }
    .luxury-card:hover { transform: translateY(-15px); border-color: var(--gold); background: #fff; box-shadow: 0 30px 60px rgba(0,0,0,0.08); }
    .card-tag { position: absolute; top: 25px; right: 25px; background: rgba(180, 145, 74, 0.1); color: var(--gold); padding: 5px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
    
    .card-title { font-family: 'DM Serif Display', serif; font-size: 2rem; margin-bottom: 5px; }
    .card-area { color: var(--text-soft); font-size: 0.9rem; display: block; margin-bottom: 25px; }
    .card-price { font-size: 2.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 30px; }
    .card-price span { font-size: 1rem; color: var(--text-soft); font-weight: 400; }
    
    .feature-list { list-style: none; padding: 0; margin-bottom: 35px; }
    .feature-list li { margin-bottom: 15px; display: flex; align-items: center; gap: 12px; color: var(--text-soft); font-size: 0.95rem; }
    .feature-list i { color: var(--gold); width: 20px; text-align: center; }

    /* Info Table */
    .luxury-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .luxury-table tr { border-bottom: 1px solid #e2e8f0; }
    .luxury-table td { padding: 20px 0; }
    .luxury-table .label { font-weight: 600; color: var(--text-soft); width: 250px; }
    .luxury-table .value { font-weight: 700; color: var(--text-main); text-align: right; }

    /* Bank Card */
    .bank-glass {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      color: #fff;
      padding: 50px;
      border-radius: 30px;
      margin-top: 60px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
    }
    .bank-qr { width: 120px; height: 120px; background: #fff; padding: 10px; border-radius: 15px; margin-bottom: 20px; }
    .bank-details p { margin: 8px 0; font-size: 1rem; opacity: 0.8; }
    .copy-field { background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: 0.2s; border: 1px solid rgba(255,255,255,0.1); }
    .copy-field:hover { background: rgba(255,255,255,0.1); }

    /* Animations */
    .reveal { opacity: 0; transform: translateY(30px); transition: 0.8s ease; }
    .reveal.active { opacity: 1; transform: translateY(0); }

    @media (max-width: 900px) {
      .bank-glass { grid-template-columns: 1fr; }
      .hero-pricing h1 { font-size: 2.8rem; }
    }
  </style>
</head>
<body data-theme="light">

  <!-- Floating Navigation -->
  <div class="header-fixed-area">
    <nav class="nav-float">
      <a href="../index.html" class="nav-logo">Nosso Zimbo</a>
      <div style="display: flex; gap: 30px; align-items: center;">
        <a href="../login.html" style="text-decoration: none; color: var(--text-soft); font-weight: 600; font-size: 0.9rem;">Área do Morador</a>
        <a href="registo_externo.php" class="nav-btn">Registrar Agora</a>
      </div>
    </nav>
    
    <!-- Tab Controls moved here -->
    <div class="tabs-nav-sub">
        <button class="tab-trigger active" onclick="switchTab('venda', this)">Aquisição de Imóvel</button>
        <button class="tab-trigger" onclick="switchTab('servicos', this)">Mensalidades e Taxas</button>
    </div>
  </div>

  <header class="hero-pricing">
    <h1 class="reveal">Seu Novo Lar em <br> Detalhes</h1>
    <p class="reveal">Explore nossas opções de investimento e taxas de manutenção para uma experiência de vida sem preocupações.</p>
  </header>

  <main class="tabs-container reveal">
    <!-- TAB 1: VENDA -->
    <div id="tab-venda" class="tab-content">
      <div class="cards-grid">
        <!-- T1 -->
        <div class="luxury-card">
          <span class="card-tag">T1 MODERNO</span>
          <h3 class="card-title">Apartamento T1</h3>
          <span class="card-area">Área: 55m² · Ideal para solteiros</span>
          <div class="card-price">1.200k <span>Kz</span></div>
          <ul class="feature-list">
            <li><i class="fa-solid fa-bed"></i> 1 Suíte Confortável</li>
            <li><i class="fa-solid fa-couch"></i> Varanda Integrada</li>
            <li><i class="fa-solid fa-car"></i> 1 Vaga Privativa</li>
            <li><i class="fa-solid fa-shield-heart"></i> Automação de Luzes</li>
          </ul>
          <a href="registo_externo.php" class="nav-btn" style="display: block; text-align: center;">Agendar Visita</a>
        </div>

        <!-- T3 -->
        <div class="luxury-card" style="border-width: 2px; border-color: var(--gold);">
          <div style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: var(--gold); color: #fff; padding: 5px 20px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;">MAIS PROCURADO</div>
          <span class="card-tag">T3 FAMILIAR</span>
          <h3 class="card-title">Apartamento T3</h3>
          <span class="card-area">Área: 100m² · Espaço para a família</span>
          <div class="card-price">2.500k <span>Kz</span></div>
          <ul class="feature-list">
            <li><i class="fa-solid fa-bed"></i> 3 Quartos (1 Master)</li>
            <li><i class="fa-solid fa-utensils"></i> Cozinha Open Space</li>
            <li><i class="fa-solid fa-car"></i> 2 Vagas de Garagem</li>
            <li><i class="fa-solid fa-wifi"></i> Fibra Óptica Instalada</li>
          </ul>
          <a href="registo_externo.php" class="nav-btn" style="display: block; text-align: center;">Solicitar Orçamento</a>
        </div>

        <!-- T5 -->
        <div class="luxury-card">
          <span class="card-tag">T5 ELITE</span>
          <h3 class="card-title">Apartamento T5</h3>
          <span class="card-area">Área: 180m² · O ápice do luxo</span>
          <div class="card-price">4.500k <span>Kz</span></div>
          <ul class="feature-list">
            <li><i class="fa-solid fa-bed"></i> 5 Quartos Premium</li>
            <li><i class="fa-solid fa-mug-hot"></i> Sala de Cinema/Jogos</li>
            <li><i class="fa-solid fa-car"></i> 3 Vagas Cobertas</li>
            <li><i class="fa-solid fa-star"></i> Acabamentos em Mármore</li>
          </ul>
          <a href="registo_externo.php" class="nav-btn" style="display: block; text-align: center;">Falar com Consultor</a>
        </div>
      </div>
    </div>

    <!-- TAB 2: SERVIÇOS -->
    <div id="tab-servicos" class="tab-content" style="display: none;">
      <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-family: 'DM Serif Display'; font-size: 2.2rem; text-align: center; margin-bottom: 40px;">Custos de Operação Mensal</h2>
        <table class="luxury-table">
          <tr>
            <td class="label">Renda Base (Imóvel)</td>
            <td class="value">140.000 Kz</td>
          </tr>
          <tr>
            <td class="label">Monitoramento & Segurança 24h</td>
            <td class="value">25.000 Kz</td>
          </tr>
          <tr>
            <td class="label">Taxa de Condomínio e Áreas Verdes</td>
            <td class="value">15.000 Kz</td>
          </tr>
          <tr>
            <td class="label">Manutenção de Áreas Comuns</td>
            <td class="value">10.000 Kz</td>
          </tr>
          <tr style="border-bottom: 2px solid var(--gold); background: rgba(180, 145, 74, 0.05);">
            <td class="label" style="color: var(--gold); padding: 30px 0;">Total Mensal Estimado</td>
            <td class="value" style="color: var(--gold); font-size: 1.5rem; padding: 30px 0;">190.000 Kz</td>
          </tr>
        </table>

        <div style="margin-top: 40px; color: var(--text-soft); font-size: 0.9rem; line-height: 1.6;">
          <p><i class="fa-solid fa-circle-info"></i> Notas: O valor da renda inclui consumo de água base. Energia elétrica é faturada individualmente por contador pré-pago. Taxas de condomínio garantem o acesso à piscina, ginásio e áreas de laser.</p>
        </div>
      </div>
    </div>

    <!-- Bank Details Card -->
    <div class="bank-glass reveal">
      <div>
        <h2 style="font-family: 'DM Serif Display'; font-size: 2rem; margin-bottom: 20px;">Informações para Pagamento</h2>
        <p style="opacity: 0.8; margin-bottom: 30px;">Utilize os dados oficiais do Condomínio Nosso Zimbo para realizar depósitos de reserva ou pagamentos de mensalidade.</p>
        <div class="bank-details">
          <p><strong>Banco:</strong> BAI - Banco Angolano de Investimento</p>
          <p><strong>Titular:</strong> Condomínio Nosso Zimbo, Lda</p>
          <div class="copy-field" onclick="copyIBAN()">
            <span><strong>IBAN:</strong> AO06.0040.0000.1234.5678.1018.9</span>
            <i class="fa-solid fa-copy"></i>
          </div>
        </div>
      </div>
      <div style="text-align: center;">
        <div class="bank-qr" style="margin: 0 auto;"><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=IBANAO06004000001234567810189" alt="QR Code" style="width: 100%;"></div>
        <p style="font-size: 0.8rem; margin-top: 15px; opacity: 0.7;">Escaneie o QR Code para detalhes bancários rápidos.</p>
      </div>
    </div>
  </main>

  <footer style="padding: 80px 5%; text-align: center; color: var(--text-soft); font-size: 0.9rem;">
    <div style="margin-bottom: 20px;">
      <i class="fa-brands fa-instagram" style="margin: 0 10px; font-size: 1.2rem;"></i>
      <i class="fa-brands fa-facebook" style="margin: 0 10px; font-size: 1.2rem;"></i>
      <i class="fa-brands fa-linkedin" style="margin: 0 10px; font-size: 1.2rem;"></i>
    </div>
    &copy; 2024 Condomínio Nosso Zimbo · Gestão Imobiliária de Excelência
  </footer>

  <script src="../js/theme-manager.js"></script>
  <script>
    function switchTab(tabName, btn) {
      // Hide all contents
      document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
      // Show targeted
      document.getElementById('tab-' + tabName).style.display = 'block';
      // Handle buttons
      document.querySelectorAll('.tab-trigger').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

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
    window.addEventListener("load", reveal);

    function copyIBAN() {
      const iban = "AO06.0040.0000.1234.5678.1018.9";
      navigator.clipboard.writeText(iban).then(() => {
        alert("IBAN copiado para a área de transferência!");
      });
    }
  </script>
</body>
</html>
