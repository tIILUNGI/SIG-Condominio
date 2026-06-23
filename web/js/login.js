/**
 * ============================================================================
 * LOGIN.JS — Script de Autenticação
 * Condomínio Nosso Zimbo — 2026
 * ============================================================================
 * Funções de validação e processamento de login para moradores e funcionários
 */

/**
 * Alterna entre formulário de morador e funcionário
 */
function alternar(){
  const portal = document.getElementById("portal")
  if(portal){
    portal.classList.toggle("active")
  }
}

/**
 * Obtém elemento de boas-vindas (fallback múltiplo)
 */
function getWelcome(){
  return document.getElementById("welcome") || document.getElementById("bem-vindo")
}

/**
 * FUNÇÃO: Validar dados de login
 * Executa validações básicas e deixa o formulário fazer submit automático
 * @param {HTMLFormElement} form - O formulário a validar
 * @returns {boolean} true se válido, false caso contrário
 */
function validarLogin(form) {
    const numbi = form.numbi.value.trim();
    const senha = form.senha.value;
    
    // Validação 1: Campos não vazios
    if (!numbi) { 
        alert('Por favor, preencha o número do BI'); 
        form.numbi.focus(); 
        return false; 
    }
    
    // Validação 2: Formato de BI (9-20 caracteres alfanuméricos)
    if (!/^[A-Za-z0-9]{9,20}$/.test(numbi)) { 
        alert('O número do BI deve conter 9 a 20 caracteres alfanuméricos'); 
        form.numbi.focus(); 
        return false; 
    }
    
    // Validação 3: Senha mínima 6 caracteres
    if (!senha || senha.length < 6) { 
        alert('A senha deve ter pelo menos 6 caracteres'); 
        form.senha.focus(); 
        return false; 
    }
    
    // Validação passou — permite submit do formulário
    return true;
}
