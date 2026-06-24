$enc = [System.Text.Encoding]::GetEncoding(1252)
$content = [System.IO.File]::ReadAllText('dashboard.php', $enc)

$jsToInject = @"

/* --- FUNCIONARIOS --- */
let allFuncs = JSON.parse(localStorage.getItem('nz_funcionarios') || '[]');
function saveFuncs() { localStorage.setItem('nz_funcionarios', JSON.stringify(allFuncs)); }
function resetFuncForm() {
    document.getElementById('f-nome').value = '';
    document.getElementById('f-senha').value = '';
    document.getElementById('f-telefone').value = '';
    document.getElementById('f-email').value = '';
    document.getElementById('f-nascimento').value = '';
    document.getElementById('f-funcao').value = '';
    document.getElementById('f-nacionalidade').value = 'Angolana';
    document.getElementById('f-estado-func').value = 'Activo';
    document.getElementById('f-morada').value = '';
    document.getElementById('f-editing-id').value = '';
    document.getElementById('func-form-titulo').innerText = 'Novo Funcionário';
    document.getElementById('func-btn-txt').innerText = 'Registar Funcionário';
}
function salvarFuncionario() {
    const idField = document.getElementById('f-editing-id').value;
    const item = {
        id: idField ? parseInt(idField) : Date.now(),
        nome: document.getElementById('f-nome').value.trim(),
        senha: document.getElementById('f-senha').value,
        telefone: document.getElementById('f-telefone').value,
        email: document.getElementById('f-email').value,
        nasc: document.getElementById('f-nascimento').value,
        funcao: document.getElementById('f-funcao').value,
        nac: document.getElementById('f-nacionalidade').value,
        estado: document.getElementById('f-estado-func').value,
        morada: document.getElementById('f-morada').value
    };
    if(!item.nome || !item.telefone || !item.funcao) { showToast('Preencha os campos obrigatórios (*)', true); return; }
    if(idField) {
        const idx = allFuncs.findIndex(f => f.id === item.id);
        if(idx >= 0) allFuncs[idx] = item;
    } else {
        allFuncs.push(item);
    }
    saveFuncs();
    resetFuncForm();
    renderFuncionarios();
    showToast('Funcionário guardado com sucesso!');
}
function renderFuncionarios() {
    const tbody = document.getElementById('funcionarios-tbody');
    if(!tbody) return;
    if(!allFuncs.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem funcionários registados</td></tr>';
        return;
    }
    tbody.innerHTML = allFuncs.map(f => \`
        <tr>
            <td><strong>\${f.nome}</strong></td>
            <td>\${f.funcao}</td>
            <td>\${f.telefone}</td>
            <td><span class="badge \${f.estado === 'Activo' ? 'pago' : 'vencido'}">\${f.estado}</span></td>
            <td>
                <div style="display:flex;gap:.4rem;">
                    <button class="btn-secondary btn-sm" onclick="verFunc(\${f.id})" title="Ver"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-secondary btn-sm" onclick="editFunc(\${f.id})" title="Editar"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-danger btn-sm" onclick="delFunc(\${f.id})" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                </div>
            </td>
        </tr>
    \`).join('');
}
function editFunc(id) {
    const f = allFuncs.find(x => x.id === id);
    if(!f) return;
    document.getElementById('f-nome').value = f.nome;
    document.getElementById('f-senha').value = f.senha || '';
    document.getElementById('f-telefone').value = f.telefone;
    document.getElementById('f-email').value = f.email;
    document.getElementById('f-nascimento').value = f.nasc;
    document.getElementById('f-funcao').value = f.funcao;
    document.getElementById('f-nacionalidade').value = f.nac;
    document.getElementById('f-estado-func').value = f.estado;
    document.getElementById('f-morada').value = f.morada;
    document.getElementById('f-editing-id').value = f.id;
    document.getElementById('func-form-titulo').innerText = 'Editar Funcionário';
    document.getElementById('func-btn-txt').innerText = 'Actualizar Funcionário';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function delFunc(id) {
    if(!confirm('Deseja eliminar este funcionário?')) return;
    allFuncs = allFuncs.filter(f => f.id !== id);
    saveFuncs();
    renderFuncionarios();
    showToast('Funcionário eliminado.');
}
function verFunc(id) {
    const f = allFuncs.find(x => x.id === id);
    if(!f) return;
    alert(\`DETALHES DO FUNCIONÁRIO:\\n\\nNome: \${f.nome}\\nCargo: \${f.funcao}\\nTelefone: \${f.telefone}\\nEmail: \${f.email}\\nNasc: \${f.nasc}\\nMorada: \${f.morada}\\nEstado: \${f.estado}\`);
}

/* --- MORADORES --- */
let allMors = JSON.parse(localStorage.getItem('nz_moradores_admin') || '[]');
function saveMors() { localStorage.setItem('nz_moradores_admin', JSON.stringify(allMors)); }
function resetMorForm() {
    document.getElementById('m-nome').value = '';
    document.getElementById('m-senha').value = '';
    document.getElementById('m-telefone').value = '';
    document.getElementById('m-email').value = '';
    document.getElementById('m-nascimento').value = '';
    document.getElementById('m-nacionalidade').value = 'Angolana';
    document.getElementById('m-morada').value = '';
    document.getElementById('m-numbi').value = '';
    document.getElementById('m-estado').value = 'Activo';
    document.getElementById('m-editing-id').value = '';
    document.getElementById('mor-form-titulo').innerText = 'Novo Morador';
    document.getElementById('mor-btn-txt').innerText = 'Registar Morador';
}
function salvarMorador() {
    const idField = document.getElementById('m-editing-id').value;
    const item = {
        id: idField ? parseInt(idField) : Date.now(),
        nome: document.getElementById('m-nome').value.trim(),
        senha: document.getElementById('m-senha').value,
        telefone: document.getElementById('m-telefone').value,
        email: document.getElementById('m-email').value,
        nasc: document.getElementById('m-nascimento').value,
        nac: document.getElementById('m-nacionalidade').value,
        morada: document.getElementById('m-morada').value,
        numbi: document.getElementById('m-numbi').value,
        estado: document.getElementById('m-estado').value
    };
    if(!item.nome || !item.telefone || !item.numbi) { showToast('Preencha os campos obrigatórios (*)', true); return; }
    if(idField) {
        const idx = allMors.findIndex(f => f.id === item.id);
        if(idx >= 0) allMors[idx] = item;
    } else {
        allMors.push(item);
    }
    saveMors();
    resetMorForm();
    renderMoradoresAdmin();
    showToast('Morador guardado com sucesso!');
}
function renderMoradoresAdmin() {
    const tbody = document.getElementById('moradores-admin-tbody');
    if(!tbody) return;
    if(!allMors.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">Sem moradores registados</td></tr>';
        return;
    }
    tbody.innerHTML = allMors.map(m => \`
        <tr>
            <td><strong>\${m.nome}</strong></td>
            <td>\${m.telefone}</td>
            <td>\${m.email || '---'}</td>
            <td style="font-family:monospace;font-size:.78rem;">\${m.numbi}</td>
            <td><span class="badge \${m.estado === 'Activo' ? 'pago' : 'vencido'}">\${m.estado}</span></td>
            <td>
                <div style="display:flex;gap:.4rem;">
                    <button class="btn-secondary btn-sm" onclick="verMor(\${m.id})" title="Ver"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn-secondary btn-sm" onclick="editMor(\${m.id})" title="Editar"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-danger btn-sm" onclick="delMor(\${m.id})" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                </div>
            </td>
        </tr>
    \`).join('');
}
function editMor(id) {
    const m = allMors.find(x => x.id === id);
    if(!m) return;
    document.getElementById('m-nome').value = m.nome;
    document.getElementById('m-senha').value = m.senha || '';
    document.getElementById('m-telefone').value = m.telefone;
    document.getElementById('m-email').value = m.email;
    document.getElementById('m-nascimento').value = m.nasc;
    document.getElementById('m-nacionalidade').value = m.nac;
    document.getElementById('m-morada').value = m.morada;
    document.getElementById('m-numbi').value = m.numbi;
    document.getElementById('m-estado').value = m.estado;
    document.getElementById('m-editing-id').value = m.id;
    document.getElementById('mor-form-titulo').innerText = 'Editar Morador';
    document.getElementById('mor-btn-txt').innerText = 'Actualizar Morador';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function delMor(id) {
    if(!confirm('Deseja eliminar este morador?')) return;
    allMors = allMors.filter(x => x.id !== id);
    saveMors();
    renderMoradoresAdmin();
    showToast('Morador eliminado.');
}
function verMor(id) {
    const m = allMors.find(x => x.id === id);
    if(!m) return;
    alert(\`DETALHES DO MORADOR:\\n\\nNome: \${m.nome}\\nTelefone: \${m.telefone}\\nEmail: \${m.email}\\nBI: \${m.numbi}\\nNasc: \${m.nasc}\\nMorada: \${m.morada}\\nEstado: \${m.estado}\`);
}

window.addEventListener('load', () => {
    localStorage.setItem('nz_test', '1'); // Force check
    renderFuncionarios();
    renderMoradoresAdmin();
});

"@

if ($content -match '</script>') {
    $content = $content -replace '</script>', "$jsToInject`n</script>"
}

[System.IO.File]::WriteAllText('dashboard.php', $content, $enc)
Write-Host "Dashboard.php injected."
