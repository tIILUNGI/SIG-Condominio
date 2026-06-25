<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div>
            <p class="brand-name">Nosso Zimbo</p>
            <p class="brand-sub">Administrativo</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-section">Gestão</p>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_portal.php') ? 'active' : ''; ?>" onclick="window.location.href='admin_portal.php'">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </button>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_funcionarios.php') ? 'active' : ''; ?>" onclick="window.location.href='admin_funcionarios.php'">
            <i class="fa-solid fa-inbox"></i><span>Cadastro de Funcionários</span>
        </button>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_moradores.php') ? 'active' : ''; ?>" onclick="window.location.href='admin_moradores.php'">
            <i class="fa-solid fa-users"></i><span>Cadastro de Moradores</span>
        </button>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_casas.php') ? 'active' : ''; ?>" onclick="window.location.href='admin_casas.php'">
            <i class="fa-solid fa-house-chimney"></i><span>Gestão de Casas</span>
        </button>
        <p class="nav-section">Finanças</p>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_pagamentos_visitantes.php') ? 'active' : ''; ?>" onclick="window.location.href='admin_pagamentos_visitantes.php'">
            <i class="fa-solid fa-money-bill-transfer"></i><span>Pagamentos</span>
        </button>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_pagamentos_moradores.php') ? 'active' : ''; ?>" onclick="window.location.href='admin_pagamentos_moradores.php'">
            <i class="fa-solid fa-id-badge"></i><span>Pagamentos Moradores</span>
        </button>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin-comunicacao.php') ? 'active' : ''; ?>" onclick="window.location.href='admin-comunicacao.php'">
            <i class="fa-solid fa-comments"></i><span>Comunicação</span>
        </button>
        <p class="nav-section">Relatórios</p>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_relatorio_mensal.php') ? 'active' : ''; ?>" onclick="window.location.href='admin_relatorio_mensal.php'">
            <i class="fa-solid fa-chart-pie"></i><span>Relatório Mensal</span>
        </button>
        <p class="nav-section">Utilizador</p>
        <button class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'perfil_admin.php') ? 'active' : ''; ?>" onclick="window.location.href='perfil_admin.php'">
            <i class="fa-solid fa-user-gear"></i><span>Meu Perfil</span>
        </button>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar-admin"><?php echo strtoupper(substr($_SESSION['nome'] ?? 'AD', 0, 2)); ?></div>
        <div style="flex:1;">
            <p class="af-name"><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Administrador'); ?></p>
            <p class="af-role"><?php echo ucfirst($_SESSION['tipo'] ?? 'Admin'); ?></p>
        </div>
        <a href="../api/logout.php" title="Sair" style="color:var(--text-muted); font-size:1rem;"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</aside>
