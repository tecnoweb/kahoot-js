'use strict';
// GDPR Cookie Consent — Soffitta.ai
// I testi sono iniettati via PHP tramite window.consentI18n

(function () {
    const STORAGE_KEY = 'soffitta_consent';
    const i18n = window.consentI18n || {};

    function getConsent() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null'); } catch { return null; }
    }

    function saveConsent(prefs) {
        const data = { ...prefs, ts: Date.now(), version: 1 };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        document.cookie = 'consent_given=1; max-age=31536000; path=/; SameSite=Lax; Secure';
        applyConsent(data);
        closeBanner();
    }

    function applyConsent(prefs) {
        // Analytics (Google Analytics stub)
        if (prefs.analytics) {
            window.dataLayer = window.dataLayer || [];
            // gtag('consent', 'update', { analytics_storage: 'granted' });
        }
        // Marketing
        if (prefs.marketing) {
            // Attiva pixel marketing
        }
        document.dispatchEvent(new CustomEvent('consentUpdated', { detail: prefs }));
    }

    function closeBanner() {
        const banner = document.getElementById('cookieBanner');
        if (banner) { banner.style.opacity = '0'; setTimeout(() => banner.remove(), 300); }
        const modal = document.getElementById('cookieModal');
        if (modal) modal.classList.add('hidden');
    }

    function buildBanner() {
        const banner = document.createElement('div');
        banner.id = 'cookieBanner';
        banner.style.cssText = 'position:fixed;bottom:0;left:0;right:0;z-index:9999;transition:opacity .3s;';
        banner.innerHTML = `
            <div class="bg-stone-900 text-white px-4 py-4 md:px-8 shadow-2xl">
                <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="flex-1">
                        <p class="text-sm text-stone-200 leading-relaxed">
                            🍪 ${i18n.banner_text || 'We use cookies.'}
                            <a href="/cookie-policy" class="text-amber-400 hover:underline ml-1">${i18n.cookie_policy || 'Cookie Policy'}</a>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <button id="consentCustomize"
                                class="text-xs text-stone-400 hover:text-white border border-stone-600 hover:border-stone-400 px-3 py-2 rounded-lg transition">
                            ${i18n.customize || 'Customize'}
                        </button>
                        <button id="consentNecessary"
                                class="text-xs bg-stone-700 hover:bg-stone-600 text-white px-3 py-2 rounded-lg transition">
                            ${i18n.necessary || 'Necessary only'}
                        </button>
                        <button id="consentAcceptAll"
                                class="text-xs bg-amber-500 hover:bg-amber-400 text-white font-semibold px-4 py-2 rounded-lg transition">
                            ${i18n.accept_all || 'Accept all'}
                        </button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(banner);

        document.getElementById('consentAcceptAll').addEventListener('click', () =>
            saveConsent({ necessary: true, analytics: true, marketing: true }));

        document.getElementById('consentNecessary').addEventListener('click', () =>
            saveConsent({ necessary: true, analytics: false, marketing: false }));

        document.getElementById('consentCustomize').addEventListener('click', openModal);
    }

    function openModal() {
        const existing = document.getElementById('cookieModal');
        if (existing) { existing.classList.remove('hidden'); return; }

        const current = getConsent() || { necessary: true, analytics: false, marketing: false };
        const modal   = document.createElement('div');
        modal.id      = 'cookieModal';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black/60 z-[10000] flex items-end sm:items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold mb-4">🍪 ${i18n.customize || 'Customize cookies'}</h3>
                    <div class="space-y-4 mb-6">
                        ${buildToggle('necessary', i18n.necessary_label, i18n.necessary_desc, true, true)}
                        ${buildToggle('analytics', i18n.analytics_label, i18n.analytics_desc, current.analytics, false)}
                        ${buildToggle('marketing', i18n.marketing_label, i18n.marketing_desc, current.marketing, false)}
                    </div>
                    <div class="flex gap-3">
                        <button id="modalSave"
                                class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition">
                            ${i18n.save || 'Save'}
                        </button>
                        <button id="modalAcceptAll"
                                class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 rounded-xl transition">
                            ${i18n.accept_all || 'Accept all'}
                        </button>
                    </div>
                    <p class="text-center text-xs text-stone-300 mt-3">
                        <a href="/privacy" class="hover:text-amber-600">${i18n.privacy_policy || 'Privacy Policy'}</a>
                        &nbsp;·&nbsp;
                        <a href="/cookie-policy" class="hover:text-amber-600">${i18n.cookie_policy || 'Cookie Policy'}</a>
                    </p>
                </div>
            </div>`;
        document.body.appendChild(modal);

        document.getElementById('modalAcceptAll').addEventListener('click', () =>
            saveConsent({ necessary: true, analytics: true, marketing: true }));

        document.getElementById('modalSave').addEventListener('click', () => {
            saveConsent({
                necessary: true,
                analytics: document.getElementById('toggle_analytics')?.checked ?? false,
                marketing: document.getElementById('toggle_marketing')?.checked ?? false,
            });
        });
    }

    function buildToggle(key, label, desc, checked, disabled) {
        const dis = disabled ? 'opacity-60 pointer-events-none' : '';
        return `
            <div class="flex items-start gap-4 ${dis}">
                <div class="flex-1">
                    <div class="font-medium text-sm text-stone-800">${label || key}</div>
                    <div class="text-xs text-stone-400 mt-0.5">${desc || ''}</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                    <input type="checkbox" id="toggle_${key}" class="sr-only peer" ${checked ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                    <div class="w-11 h-6 bg-stone-200 peer-checked:bg-amber-500 rounded-full peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
            </div>`;
    }

    // Esponi API globale per "Gestisci cookie" nel footer
    window.openCookieSettings = openModal;

    // Inizializzazione
    const consent = getConsent();
    if (!consent) {
        // Primo accesso: mostra banner dopo 500ms
        setTimeout(buildBanner, 500);
    } else {
        applyConsent(consent);
    }
})();
