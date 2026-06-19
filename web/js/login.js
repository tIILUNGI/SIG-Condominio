function alternar(){
  const portal = document.getElementById("portal")
  if(portal){
    portal.classList.toggle("active")
  }
}

function getWelcome(){
  return document.getElementById("welcome") || document.getElementById("bem-vindo")
}

// LOGIN MORADOR
function loginMorador(e){
  e.preventDefault()

  const form = e.target
  const zimbo = (form.querySelector('#zimbo, input[name="numbi"]') || {}).value || ''
  const senha = (form.querySelector('#senhaMorador, input[name="senha"]') || {}).value || ''

  if(zimbo.trim()==="" || senha===""){
    alert("Preencha Número do BI e Senha")
    return
  }

  const tela = getWelcome()
  if(tela){
    tela.style.display="flex"
    tela.innerHTML=" Bem-vindo Morador<br>Vivenda "+zimbo.trim()
  }
}

// LOGIN FUNCIONARIO
function loginFuncionario(e){
  e.preventDefault()

  const form = e.target
  const nome = (form.querySelector('#nome, input[name="nome"], input[name="numbi"]') || {}).value || ''
  const funcao = (form.querySelector('#funcao, input[name="funcao"]') || {}).value || 'Funcionário'
  const codigo = (form.querySelector('#codigo, input[name="codigo"], input[name="senha"]') || {}).value || ''

  if(nome.trim()==="" || codigo===""){
    alert("Preencha número do BI e senha")
    return
  }

  const processando = document.getElementById("processando")
  if(processando){
    processando.style.display="block"
  }

  setTimeout(function(){
    if(processando){
      processando.style.display="none"
    }

    const tela = getWelcome()
    if(tela){
      tela.style.display="flex"
      tela.innerHTML=" Bem-vindo "+nome.trim()+"<br>"+funcao
    }
  },2000)
}
