export function initKeyboardShortcuts(router) {
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;

        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.querySelector('[data-search-input]')?.focus();
        }

        if (e.key === 'g') {
            const handler = (e2) => {
                if (e2.key === 'd') { router.visit('/apps/dashboard'); }
                if (e2.key === 'i') { router.visit('/apps/items'); }
                if (e2.key === 's') { router.visit('/apps/stock-ins'); }
                if (e2.key === 'o') { router.visit('/apps/stock-outs'); }
                if (e2.key === 'w') { router.visit('/apps/warehouses'); }
                if (e2.key === 'r') { router.visit('/apps/reports'); }
                if (e2.key === 'm') { router.visit('/apps/stock-movements'); }
                if (e2.key === 'f') { router.visit('/apps/forecasting'); }
                document.removeEventListener('keydown', handler);
            };
            setTimeout(() => document.addEventListener('keydown', handler, { once: true }), 0);
        }

        if (e.key === '?' && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('show-shortcuts-help'));
        }

        if (e.key === 'Escape') {
            window.dispatchEvent(new CustomEvent('hide-shortcuts-help'));
        }
    });
}
