// Wide data tables keep their own native scroll area and a visible mobile hint.
document.querySelectorAll('.overflow-x-auto').forEach((region) => {
    if (!region.querySelector('table')) return;

    const hint = document.createElement('p');
    hint.className = 'hidden px-4 mt-3 mb-2 text-xs text-slate-400 sm:px-6';
    hint.textContent = 'Geser tabel ke samping untuk melihat kolom lainnya.';
    region.before(hint);

    const update = () => {
        const scrollable = region.scrollWidth > region.clientWidth + 1;
        hint.classList.toggle('hidden', !scrollable || window.innerWidth >= 768);
        if (scrollable) {
            region.setAttribute('tabindex', '0');
            region.setAttribute('role', 'region');
            region.setAttribute('aria-label', 'Tabel dapat digeser horizontal');
        } else {
            region.removeAttribute('tabindex');
            region.removeAttribute('role');
            region.removeAttribute('aria-label');
        }
    };

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(update);
        observer.observe(region);
        observer.observe(region.querySelector('table'));
    } else {
        window.addEventListener('resize', update);
    }
    update();
});
