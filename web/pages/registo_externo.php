<?php
session_start();
include(__DIR__ . '/../api/csrf_protection.php');
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registo de Visitante — Condomínio Nosso Zimbo</title>
  <link rel="stylesheet" href="../css/visitante.css">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<div class="bg-glow"></div>

<div class="reg-wrapper">
  <!-- HEADER -->
  <header class="reg-header">
    <a href="../login.html" class="back-link" title="Voltar ao Login">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div class="reg-logo"><i class="fa-solid fa-user-plus"></i></div>
    <div>
      <h1 class="reg-title">Registo de Prospecto</h1>
      <p class="reg-sub">Deseja morar connosco? Comece por aqui.</p>
    </div>
  </header>

  <!-- STEPS -->
  <div class="reg-steps" id="steps-bar">
    <div class="reg-step active" data-step="1">
      <div class="step-num">1</div>
      <span>Interesse</span>
    </div>
    <div class="step-line"></div>
    <div class="reg-step" data-step="2">
      <div class="step-num">2</div>
      <span>Dados</span>
    </div>
    <div class="step-line"></div>
    <div class="reg-step" data-step="3">
      <div class="step-num">3</div>
      <span>Submissão</span>
    </div>
  </div>

  <form id="form-visitante" action="../api/registar_morador.php" method="POST">
    <?php echo csrf_field(); ?>
    <!-- ECRÃ 1: ESCOLHA DE SERVIÇO -->
    <section class="reg-step-content active" id="step-1">
      <h2 class="step-title">O que procura?</h2>
      <p class="step-desc">Selecione a modalidade que melhor se adapta às suas necessidades.</p>
      
      <div class="service-cards">
        <div class="service-card selected" onclick="selectService('Arrendamento', this)">
          <div class="svc-icon"><i class="fa-solid fa-key"></i></div>
          <div class="svc-info">
            <p class="svc-name">Arrendamento (V3)</p>
            <p class="svc-desc">Contrato mensal. Ideal para quem procura flexibilidade.</p>
          </div>
          <div class="svc-check"><i class="fa-solid fa-check"></i></div>
        </div>

        <div class="service-card" onclick="selectService('Compra', this)">
          <div class="svc-icon"><i class="fa-solid fa-house-chimney"></i></div>
          <div class="svc-info">
            <p class="svc-name">Compra Directa</p>
            <p class="svc-desc">Invista no seu futuro. Apartamento próprio com todas as regalias.</p>
          </div>
          <div class="svc-check"><i class="fa-solid fa-check"></i></div>
        </div>
      </div>

      <input type="hidden" name="tipo_interesse" id="it-tipo" value="Arrendamento">

      <div class="step-nav">
        <span></span>
        <button type="button" class="btn-primary" onclick="nextStep(2)">Próximo Passo <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </section>

    <!-- ECRÃ 2: DADOS PESSOAIS + PREFERÊNCIAS DE CASA -->
    <section class="reg-step-content" id="step-2">
      <h2 class="step-title">Conte-nos sobre si e as suas preferências</h2>
      <p class="step-desc">Estes dados serão usados para o primeiro contacto da nossa equipa comercial.</p>
      
      <div class="form-grid">
        <div class="form-group full">
          <label>Nome Completo</label>
          <input type="text" name="nome" required placeholder="Ex: Adão Silva" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" title="O nome deve conter apenas letras e espaços.">
        </div>
<div class="form-group">
           <label>Nº BI</label>
           <input type="text" name="numbi" required placeholder="000XXXXXXLA000">
         </div>
         <div class="form-group">
           <label>Local Emissão BI</label>
           <select name="locale">
             <option value="Luanda">Luanda</option>
             <option value="Porto Alegre">Porto Alegre</option>
             <option value="Benguela">Benguela</option>
           </select>
         </div>
         <div class="form-group">
           <label>Telefone</label>
           <input type="tel" name="telefone" required placeholder="9XX-XXX-XXX" pattern="9[0-9]{2}-[0-9]{3}-[0-9]{3}" title="Formato esperado: 9xx-xxx-xxx" oninput="maskPhone(this)">
         </div>
         <div class="form-group full">
           <label>Email</label>
           <input type="email" name="email" required placeholder="seuemail@exemplo.com">
         </div>
         <div class="form-group full">
           <label>Palavra-passe (para o portal)</label>
           <input type="password" name="senha" required minlength="6" placeholder="******">
         </div>
       </div>

       <h3 style="margin:1.5rem 0 .5rem; font-size:1rem; color:var(--text);">Preferências de Moradia</h3>
       <p style="font-size:.82rem; color:var(--text-muted); margin-bottom:1rem;">Ajude-nos a encontrar o apartamento ideal para si.</p>

       <div class="form-grid">
         <div class="form-group">
           <label>Bloco preferido</label>
           <select name="preferencia_bloco">
             <option value="">— Sem preferência —</option>
             <option value="A">Bloco A</option>
             <option value="B">Bloco B</option>
             <option value="C">Bloco C</option>
           </select>
         </div>
         <div class="form-group">
           <label>Tipologia</label>
           <select name="preferencia_tipologia">
             <option value="">— Sem preferência —</option>
             <option value="T1">T1</option>
             <option value="T2">T2</option>
             <option value="T3">T3</option>
             <option value="V3">V3</option>
             <option value="V4">V4</option>
           </select>
         </div>
         <div class="form-group">
           <label>Andar preferido</label>
           <input type="text" name="preferencia_andar" placeholder="Ex: R/C, 1º, 2º, 3º">
         </div>
         <div class="form-group full">
           <label>Observações adicionais</label>
           <textarea name="observacoes" rows="3" placeholder="Ex:necessidade de vaga de garagem, preferência por varanda, etc."></textarea>
         </div>
       </div>

      <div class="step-nav">
        <button type="button" class="btn-secondary" onclick="nextStep(1)"><i class="fa-solid fa-arrow-left"></i> Voltar</button>
        <button type="button" class="btn-primary" onclick="nextStep(3)">Continuar <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </section>

    <!-- ECRÃ 3: CONFIRMAÇÃO -->
    <section class="reg-step-content" id="step-3">
      <h2 class="step-title">Quase lá!</h2>
      <p class="step-desc">Ao submeter, os seus dados serão analisados. Deverá comparecer à administração para validar o pagamento presencial e receber a atribuição da casa.</p>
      
      <div class="bank-card">
        <div class="svc-detail">
          <div class="svc-detail-title"><i class="fa-solid fa-circle-info"></i> Resumo do Pedido</div>
          <div class="svc-detail-row"><span>Interesse:</span> <strong id="res-tipo">Arrendamento</strong></div>
          <div class="svc-detail-row"><span>Nome:</span> <strong id="res-nome">-</strong></div>
          <div class="svc-detail-row"><span>Email:</span> <strong id="res-email">-</strong></div>
          <div class="svc-detail-row"><span>Bloco preferido:</span> <strong id="res-bloco">—</strong></div>
          <div class="svc-detail-row"><span>Tipologia:</span> <strong id="res-tipologia">—</strong></div>
          <div class="svc-detail-row"><span>Andar preferido:</span> <strong id="res-andar">—</strong></div>
        </div>
      </div>

      <div class="reg-consent">
        <input type="checkbox" id="consent" required>
        <label for="consent">Autorizo o processamento dos meus dados para fins comerciais e agendamento de visitas.</label>
      </div>

      <div class="step-nav">
        <button type="button" class="btn-secondary" onclick="nextStep(2)"><i class="fa-solid fa-arrow-left"></i> Revisar</button>
        <button type="submit" class="btn-primary">Finalizar Registo <i class="fa-solid fa-paper-plane"></i></button>
      </div>
    </section>
  </form>
</div>

<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-msg"></span></div>

<script>
  function maskPhone(i) {
    let v = i.value.replace(/\D/g, "");
    if (v.length > 9) v = v.substring(0, 9);
    let r = "";
    if (v.length > 0) r += v.substring(0, 3);
    if (v.length > 3) r += "-" + v.substring(3, 6);
    if (v.length > 6) r += "-" + v.substring(6, 9);
    i.value = r;
  }

  function selectService(tipo, el) {
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('it-tipo').value = tipo;
    document.getElementById('res-tipo').textContent = tipo;
  }

  function nextStep(n) {
    if (n === 3) {
      const nome = document.querySelector('input[name="nome"]').value;
      const email = document.querySelector('input[name="email"]').value;
      if (!nome || !email) {
        showToast('Preencha os campos obrigatórios', true);
        return;
      }
      document.getElementById('res-nome').textContent = nome;
      document.getElementById('res-email').textContent = email;
      document.getElementById('res-bloco').textContent = document.querySelector('select[name="preferencia_bloco"]').value || '—';
      document.getElementById('res-tipologia').textContent = document.querySelector('select[name="preferencia_tipologia"]').value || '—';
      document.getElementById('res-andar').textContent = document.querySelector('input[name="preferencia_andar"]').value || '—';
    }

    document.querySelectorAll('.reg-step-content').forEach(s => s.classList.remove('active'));
    document.getElementById('step-' + n).classList.add('active');

    document.querySelectorAll('.reg-step').forEach(s => {
      const sNum = parseInt(s.dataset.step);
      s.classList.remove('active', 'done');
      if (sNum === n) s.classList.add('active');
      if (sNum < n) s.classList.add('done');
    });
  }

  function showToast(msg, err) {
    const t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.className = 'toast' + (err ? ' error' : '') + ' show';
    setTimeout(() => t.classList.remove('show'), 3500);
  }
</script>
</body>
</html>
