document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-plainte-form]');
    if (!form) {
        return;
    }

    const step1 = form.querySelector('[data-step="1"]');
    const step2 = form.querySelector('[data-step="2"]');
    const resume = (name, value) => {
        const node = form.querySelector(`[data-resume="${name}"]`);
        if (node) {
            node.textContent = value || '—';
        }
    };

    form.querySelector('[data-recap]')?.addEventListener('click', () => {
        if (!form.reportValidity()) {
            return;
        }
        const data = new FormData(form);
        resume('titre', data.get('titre'));
        resume('description', data.get('description'));
        resume('localisation', `${data.get('commune')}, ${data.get('region')} — ${data.get('localisation')}`);
        ['domaine', 'priorite', 'region'].forEach((name) => {
            const field = form.querySelector(`[name="${name}"]`);
            resume(name, field?.selectedOptions?.[0]?.textContent?.trim());
        });
        const files = form.querySelector('[name="pieces[]"]')?.files;
        resume('pieces', files?.length ? Array.from(files).map((file) => file.name).join(', ') : 'Aucune');
        step1.classList.add('is-hidden');
        step2.classList.remove('is-hidden');
    });

    form.querySelector('[data-retour]')?.addEventListener('click', () => {
        step2.classList.add('is-hidden');
        step1.classList.remove('is-hidden');
    });
});
