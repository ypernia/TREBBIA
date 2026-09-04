import './bootstrap';

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.dataset.disableSubmit === 'false') {
        return;
    }

    const submitter = event.submitter;

    if (!(submitter instanceof HTMLButtonElement) || submitter.disabled) {
        return;
    }

    submitter.dataset.originalLabel = submitter.textContent.trim();
    submitter.disabled = true;
    submitter.setAttribute('aria-busy', 'true');

    if (submitter.dataset.loadingLabel) {
        submitter.textContent = submitter.dataset.loadingLabel;

        return;
    }

    if (! submitter.querySelector('svg')) {
        submitter.textContent = 'Procesando...';
    }
});
