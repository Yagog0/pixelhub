/* Pequenas melhorias progressivas; navegação e formulários funcionam sem JavaScript. */
'use strict';
const menu = document.querySelector('.mobile-menu');
const sidebar = document.querySelector('.sidebar');
menu?.addEventListener('click', () => {
    const open = sidebar.classList.toggle('is-open');
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
});
document.addEventListener('click', (event) => {
    if (
        sidebar?.classList.contains('is-open') &&
        !sidebar.contains(event.target) &&
        !menu.contains(event.target)
    ) {
        sidebar.classList.remove('is-open');
        menu.setAttribute('aria-expanded', 'false');
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        sidebar?.classList.remove('is-open');
        menu?.setAttribute('aria-expanded', 'false');
    }
    if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
        event.preventDefault();
        document.querySelector('.header-search input[name="q"]')?.focus();
    }
});
document
    .querySelectorAll('.dismiss-flash')
    .forEach((button) => button.addEventListener('click', () => button.closest('.flash').remove()));
document.querySelectorAll('[data-confirm]').forEach((form) =>
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    }),
);
