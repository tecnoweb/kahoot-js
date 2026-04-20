/* ============================================================
   Lido Torre Conca – main.js
   Vanilla JS, no dependencies
   ============================================================ */

// ── Navbar scroll effect ────────────────────────────────────
(function () {
  const nav = document.getElementById('navbar');
  if (!nav) return;
  const update = () => nav.classList.toggle('scrolled', window.scrollY > 40);
  update();
  window.addEventListener('scroll', update, { passive: true });
})();

// ── Mobile burger menu ──────────────────────────────────────
(function () {
  const burger = document.getElementById('burger');
  const menu   = document.getElementById('mobile-menu');
  const open   = document.getElementById('burger-open');
  const close  = document.getElementById('burger-close');
  if (!burger) return;

  burger.addEventListener('click', () => {
    const expanded = menu.classList.toggle('hidden') === false;
    open.classList.toggle('hidden',  expanded);
    close.classList.toggle('hidden', !expanded);
  });

  // Close on outside click
  document.addEventListener('click', (e) => {
    if (!burger.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.add('hidden');
      open.classList.remove('hidden');
      close.classList.add('hidden');
    }
  });
})();

// ── Toast system ────────────────────────────────────────────
window.Toast = (function () {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  function show(message, type = '', duration = 3500) {
    const el = document.createElement('div');
    el.className = 'toast ' + type;
    el.textContent = message;
    container.appendChild(el);
    setTimeout(() => {
      el.classList.add('out');
      el.addEventListener('animationend', () => el.remove());
    }, duration);
    return el;
  }

  return {
    success: (msg) => show(msg, 'success'),
    error:   (msg) => show(msg, 'error', 4500),
    info:    (msg) => show(msg, ''),
  };
})();

// ── Accordion ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.accordion-trigger').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.accordion-item');
      const isOpen = item.classList.contains('open');
      // Close all
      document.querySelectorAll('.accordion-item.open').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });
});

// ── Qty stepper ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-stepper]').forEach(wrapper => {
    const input = wrapper.querySelector('[data-stepper-value]');
    const minus = wrapper.querySelector('[data-stepper-minus]');
    const plus  = wrapper.querySelector('[data-stepper-plus]');
    if (!input || !minus || !plus) return;

    const min = parseInt(input.dataset.min ?? 0);
    const max = parseInt(input.dataset.max ?? 99);

    function update(val) {
      val = Math.max(min, Math.min(max, val));
      input.value = val;
      minus.disabled = val <= min;
      plus.disabled  = val >= max;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    update(parseInt(input.value) || min);
    minus.addEventListener('click', () => update(parseInt(input.value) - 1));
    plus.addEventListener('click',  () => update(parseInt(input.value) + 1));
  });
});

// ── Modal system ────────────────────────────────────────────
window.Modal = {
  open(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  },
  close(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  // Close modal on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) Modal.close(overlay.id);
    });
  });

  // Close buttons
  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => Modal.close(btn.dataset.modalClose));
  });

  // Open buttons
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', () => Modal.open(btn.dataset.modalOpen));
  });
});

// ── Date helpers ─────────────────────────────────────────────
window.DateHelper = {
  today()  { return new Date().toISOString().slice(0, 10); },
  addDays(dateStr, n) {
    const d = new Date(dateStr);
    d.setDate(d.getDate() + n);
    return d.toISOString().slice(0, 10);
  },
  format(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
  },
  diffDays(from, to) {
    const a = new Date(from), b = new Date(to);
    return Math.max(1, Math.round((b - a) / 86400000) + 1);
  }
};

// ── Sync min dates on date inputs ────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const start = document.querySelector('[name="data_inizio"]');
  const end   = document.querySelector('[name="data_fine"]');
  if (start && end) {
    start.addEventListener('change', () => {
      if (end.value < start.value) end.value = start.value;
      end.min = start.value;
    });
  }
});

// ── Fetch helper ─────────────────────────────────────────────
window.apiFetch = async function (url, options = {}) {
  const res = await fetch(url, {
    headers: { 'Content-Type': 'application/json', ...options.headers },
    ...options,
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json();
};

// ── Print receipt ────────────────────────────────────────────
window.printReceipt = function (content) {
  const w = window.open('', '_blank', 'width=400,height=600');
  w.document.write(`
    <!DOCTYPE html><html><head>
    <meta charset="UTF-8">
    <title>Ricevuta – Lido Torre Conca</title>
    <style>
      body { font-family: monospace; font-size: 13px; padding: 1rem; }
      h2   { text-align: center; font-size: 16px; }
      .line{ border-top: 1px dashed #000; margin: 6px 0; }
      .big { font-size: 22px; font-weight: bold; text-align: center; }
    </style></head><body>${content}</body></html>`);
  w.document.close();
  w.print();
};
