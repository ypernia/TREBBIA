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

document.addEventListener('DOMContentLoaded', () => {
    const tabs = [...document.querySelectorAll('[data-settings-tab]')];
    const panels = [...document.querySelectorAll('[data-settings-panel]')];

    if (! tabs.length || ! panels.length) {
        return;
    }

    const hashToTab = {
        '#agenda-preferences': 'agenda',
        '#whatsapp-channel': 'whatsapp',
        '#assisted-activation': 'whatsapp',
        '#branches': 'branches',
        '#team': 'team',
    };

    const activate = (tabName, scroll = false) => {
        const selected = tabs.some((tab) => tab.dataset.settingsTab === tabName) ? tabName : 'profile';

        tabs.forEach((tab) => {
            tab.setAttribute('aria-selected', tab.dataset.settingsTab === selected ? 'true' : 'false');
        });

        panels.forEach((panel) => {
            panel.hidden = panel.dataset.settingsPanel !== selected;
        });

        if (scroll) {
            document.querySelector(`[data-settings-panel="${selected}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activate(tab.dataset.settingsTab, true);
            history.replaceState(null, '', `#${tab.dataset.settingsTab}`);
        });
    });

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a[href^="#"]');

        if (! anchor) {
            return;
        }

        const tabName = hashToTab[anchor.getAttribute('href')];

        if (tabName) {
            activate(tabName, true);
        }
    });

    activate(hashToTab[window.location.hash] || window.location.hash.replace('#', '') || 'profile');
});
