/**
 * Theme Manager for Nosso Zimbo
 * Handles Dark/Light mode switching and persistence
 */

const ThemeManager = {
    init() {
        const savedTheme = localStorage.getItem('nz-theme') || 'light';
        this.applyTheme(savedTheme);
        this.renderToggle();
    },

    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
    },

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('nz-theme', theme);
        this.updateToggleButton(theme);
    },

    renderToggle() {
        // Find existing topbar-right and insert toggle
        const topbarRight = document.querySelector('.topbar-right');
        if (topbarRight && !document.getElementById('theme-toggle')) {
            const btn = document.createElement('button');
            btn.id = 'theme-toggle';
            btn.className = 'theme-toggle-btn';
            btn.setAttribute('onclick', 'ThemeManager.toggle()');
            btn.setAttribute('title', 'Trocar Tema');
            btn.style.cssText = `
                width: 34px;
                height: 34px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border: 1px solid var(--border);
                background: var(--surface);
                color: var(--text);
                font-size: 14px;
                transition: all 0.2s ease;
                margin-right: 8px;
            `;
            btn.innerHTML = '<i class="fa-solid fa-moon"></i>';
            topbarRight.insertBefore(btn, topbarRight.firstChild);
            
            this.updateToggleButton(localStorage.getItem('nz-theme') || 'light');
        }
    },

    updateToggleButton(theme) {
        const btn = document.getElementById('theme-toggle');
        if (btn) {
            const icon = btn.querySelector('i');
            if (theme === 'dark') {
                icon.className = 'fa-solid fa-sun';
                btn.style.color = '#f59e0b';
            } else {
                icon.className = 'fa-solid fa-moon';
                btn.style.color = '#1e293b';
            }
        }
    }
};

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => ThemeManager.init());
