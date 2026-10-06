/**
 * AquaFlow — core front-end utilities.
 * Provides: AJAX helper (fetch wrapper with CSRF header), toast notifications,
 * generic modal open/close, dark mode toggle, and mobile sidebar toggle.
 */

const AQ = {
    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },

    /** Perform an AJAX request and return parsed JSON. Throws on network error. */
    async request(url, { method = 'GET', body = null } = {}) {
        const options = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.csrfToken(),
            },
        };
        if (body) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify({ ...body, csrf_token: this.csrfToken() });
        }
        const res = await fetch(url, options);
        let data;
        try { data = await res.json(); } catch { data = { success: res.ok }; }
        return data;
    },

    toast(message, type = 'success') {
        let stack = document.querySelector('.aq-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'aq-toast-stack';
            document.body.appendChild(stack);
        }
        const el = document.createElement('div');
        el.className = `aq-toast ${type === 'error' ? 'error' : ''}`;
        el.textContent = message;
        stack.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    },

    openModal(id) {
        document.getElementById(id)?.classList.add('show');
    },
    closeModal(id) {
        document.getElementById(id)?.classList.remove('show');
    },

    toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', current);
        localStorage.setItem('aq-theme', current);
    },

    initTheme() {
        const saved = localStorage.getItem('aq-theme');
        if (saved) document.documentElement.setAttribute('data-theme', saved);
    },

    toggleSidebar() {
        document.querySelector('.aq-sidebar')?.classList.toggle('show');
    },
};

document.addEventListener('DOMContentLoaded', () => {
    AQ.initTheme();

    // Close modal when clicking backdrop
    document.querySelectorAll('.aq-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) backdrop.classList.remove('show');
        });
    });

    // Generic [data-confirm] handler for destructive actions (delete buttons)
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm(btn.dataset.confirm)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
});
