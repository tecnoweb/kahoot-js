'use strict';

const dropzone  = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const preview   = document.getElementById('preview');
const resultBox = document.getElementById('result');
const loader    = document.getElementById('loader');
const csrf      = document.getElementById('csrfToken')?.value ?? '';

if (dropzone) {
    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('dropzone-active');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dropzone-active');
    });

    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('dropzone-active');
        const file = e.dataTransfer.files[0];
        if (file) handleFile(file);
    });

    dropzone.addEventListener('click', () => fileInput?.click());
}

if (fileInput) {
    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) handleFile(fileInput.files[0]);
    });
}

function handleFile(file) {
    if (!file.type.startsWith('image/')) {
        showError('Seleziona un file immagine (JPG, PNG o WEBP).');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        showError('Il file è troppo grande. Dimensione massima: 10MB.');
        return;
    }

    // Mostra anteprima
    const reader = new FileReader();
    reader.onload = e => {
        if (preview) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            document.getElementById('dropzoneContent')?.classList.add('hidden');
        }
    };
    reader.readAsDataURL(file);

    uploadAndAnalyze(file);
}

async function uploadAndAnalyze(file) {
    loader?.classList.remove('hidden');
    resultBox?.classList.add('hidden');

    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', csrf);

    try {
        const res  = await fetch('/api/upload.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.success) {
            showError(data.message || 'Errore durante l\'analisi. Riprova.');
            return;
        }

        renderResult(data.data, data.scan_id, data.image);
    } catch (err) {
        showError('Errore di connessione. Controlla la tua rete e riprova.');
    } finally {
        loader?.classList.add('hidden');
    }
}

function renderResult(data, scanId, imageUrl) {
    if (!resultBox) return;

    const suggestions = Array.isArray(data.sell_suggestions)
        ? data.sell_suggestions.map(s => `<li>${escHtml(s)}</li>`).join('')
        : '';

    const confidenceColor = data.confidence_score >= 70
        ? 'text-green-600'
        : data.confidence_score >= 40
            ? 'text-amber-600'
            : 'text-red-500';

    resultBox.innerHTML = `
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mt-4 text-left">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <h2 class="text-2xl font-bold text-stone-800">${escHtml(data.object_name)}</h2>
                <span class="text-sm text-amber-700 bg-amber-100 px-3 py-1 rounded-full">
                    ${escHtml(data.era)} &middot; ${escHtml(data.category)}
                </span>
            </div>

            <p class="text-stone-600 leading-relaxed">${escHtml(data.description)}</p>

            ${data.condition_notes ? `<p class="mt-2 text-sm text-stone-400 italic">Condizioni: ${escHtml(data.condition_notes)}</p>` : ''}

            <div class="mt-5 grid grid-cols-2 gap-4">
                <div class="bg-white rounded-xl p-4 text-center border border-amber-200">
                    <div class="text-xs text-stone-400 mb-1">Valore stimato</div>
                    <div class="text-2xl font-bold text-amber-600">€${data.estimated_min} – €${data.estimated_max}</div>
                </div>
                <div class="bg-white rounded-xl p-4 text-center border border-amber-200">
                    <div class="text-xs text-stone-400 mb-1">Confidenza AI</div>
                    <div class="text-2xl font-bold ${confidenceColor}">${data.confidence_score}%</div>
                </div>
            </div>

            ${suggestions ? `
            <div class="mt-5">
                <div class="font-semibold text-stone-700 mb-2">Dove venderlo:</div>
                <ul class="list-disc list-inside text-stone-600 space-y-1 text-sm">
                    ${suggestions}
                </ul>
            </div>` : ''}

            ${data.curiosity ? `<p class="mt-5 text-sm italic text-stone-400 bg-white rounded-xl p-3 border border-stone-100">💡 ${escHtml(data.curiosity)}</p>` : ''}

            <a href="/checkout.php?scan_id=${encodeURIComponent(scanId)}"
               class="mt-6 block w-full text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition">
                Ottieni perizia dettagliata — €2,99
            </a>
            <a href="/perizia/${encodeURIComponent(scanId)}"
               class="mt-2 block w-full text-center text-stone-400 hover:text-stone-600 text-sm py-2 transition">
                Condividi questa valutazione
            </a>
        </div>
    `;
    resultBox.classList.remove('hidden');
    resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showError(msg) {
    if (!resultBox) return;
    resultBox.innerHTML = `
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mt-4 flex items-start gap-3">
            <span class="text-xl">⚠️</span>
            <div>
                <p class="font-semibold">Si è verificato un errore</p>
                <p class="text-sm mt-1">${escHtml(msg)}</p>
            </div>
        </div>
    `;
    resultBox.classList.remove('hidden');
    // Ripristina dropzone
    preview?.classList.add('hidden');
    document.getElementById('dropzoneContent')?.classList.remove('hidden');
}

function escHtml(str) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(str ?? '').replace(/[&<>"']/g, m => map[m]);
}
