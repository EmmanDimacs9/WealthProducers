(function () {
  const form = document.getElementById('infoForm');
  const panels = [...form.querySelectorAll('.panel')];
  const stepItems = [...document.querySelectorAll('#stepList li')];
  const bar = document.getElementById('bar');
  const line = document.getElementById('stepLine');
  const titles = ['Personal Information', 'Emergency Contact', 'Specific Skills', 'Document Upload'];
  let current = 0;

  function show(i) {
    current = i;
    panels.forEach((p, idx) => p.classList.toggle('active', idx === i));
    stepItems.forEach((s, idx) => {
      s.classList.toggle('active', idx === i);
      s.classList.toggle('done', idx < i);
    });
    bar.style.width = ((i + 1) / panels.length * 100) + '%';
    line.innerHTML = 'Step <b>' + (i + 1) + '</b> of ' + panels.length + ' &middot; ' + titles[i];
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Each document: needs its files OR "To follow" ticked
  function checkDocs(panel) {
    let ok = true;
    panel.querySelectorAll('.file').forEach(box => {
      const input = box.querySelector('input[type=file]');
      const tf = box.querySelector('[data-tf]');
      const msg = box.querySelector('.file-msg');
      if (!input || !tf) return;
      const need = parseInt(input.dataset.need || '1', 10);
      const n = input.files.length;
      let m = '';
      if (n > need) {
        m = 'Please select no more than ' + need + ' file(s).';
      } else if (n < need && !tf.checked) {
        m = need > 1
          ? 'Upload all ' + need + ' files or tick "To follow".'
          : 'Upload the file or tick "To follow".';
      }
      if (msg) msg.textContent = m;
      box.classList.toggle('invalid', !!m);
      if (m) ok = false;
    });
    return ok;
  }

  function validate(panel) {
    let ok = true, first = null;
    panel.querySelectorAll('input[required], select[required]').forEach(el => {
      el.classList.remove('invalid');
      el.closest('.phone')?.classList.remove('invalid');
      if (el.hasAttribute('data-gmail')) {
        el.setCustomValidity(/^[A-Za-z0-9._%+\-]+@gmail\.com$/i.test(el.value.trim())
          ? '' : 'Please enter your complete email ending with @gmail.com');
      }
      if (el.hasAttribute('data-phone')) {
        el.setCustomValidity(/^09\d{9}$/.test(el.value)
          ? '' : 'Contact number must be exactly 11 digits (09XXXXXXXXX)');
      }
      if (!el.checkValidity()) {
        el.closest('.phone')?.classList.add('invalid');
        el.classList.add('invalid');
        ok = false;
        if (!first) first = el;
      }
    });
    if (first) { first.focus(); first.reportValidity(); }
    if (!checkDocs(panel)) ok = false;
    return ok;
  }

  form.addEventListener('input', e => {
    e.target.classList.remove('invalid');
    e.target.closest('.phone')?.classList.remove('invalid');
    // Phone fields: digits only, max 11
    if (e.target.hasAttribute('data-phone')) {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 11);
    }
    if (e.target.hasAttribute('data-gmail')) e.target.value = e.target.value.replace(/\s/g, '');
    if (e.target.setCustomValidity && (e.target.hasAttribute('data-phone') || e.target.hasAttribute('data-gmail'))) {
      e.target.setCustomValidity('');
    }
  });

  form.querySelectorAll('[data-next]').forEach(b =>
    b.addEventListener('click', () => {
      if (validate(panels[current]) && current < panels.length - 1) show(current + 1);
    })
  );
  form.querySelectorAll('[data-back]').forEach(b =>
    b.addEventListener('click', () => current > 0 && show(current - 1))
  );

  // Enter key advances instead of submitting early
  form.addEventListener('keydown', e => {
    if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'file' && current < panels.length - 1) {
      e.preventDefault();
      if (validate(panels[current])) show(current + 1);
    }
  });

  // ---- File boxes: size check, counter, "To follow" behaviour ----
  form.querySelectorAll('.file').forEach(box => {
    const input = box.querySelector('input[type=file]');
    const tf = box.querySelector('[data-tf]');
    const msg = box.querySelector('.file-msg');
    const counter = box.querySelector('.file-count');
    if (!input || !tf) return;
    const need = parseInt(input.dataset.need || '1', 10);

    function refreshCounter() {
      if (!counter) return;
      const n = input.files.length;
      counter.textContent = n ? n + ' of ' + need + ' file(s) selected' : '';
    }

    input.addEventListener('change', () => {
      const files = [...input.files];
      if (files.some(f => f.size > 5 * 1024 * 1024)) {
        alert('Each file must be 5 MB or smaller.');
        input.value = '';
      } else if (files.length > need) {
        alert('Please select no more than ' + need + ' file(s).');
        input.value = '';
      } else if (need === 1 && files.length) {
        tf.checked = false; // file provided, no longer "to follow"
      } else if (need > 1 && files.length === need) {
        tf.checked = false; // all files provided
      }
      refreshCounter();
      box.classList.remove('invalid');
      if (msg) msg.textContent = '';
    });

    tf.addEventListener('change', () => {
      // Single-file docs: ticking "To follow" clears and locks the file input
      if (need === 1) {
        if (tf.checked) input.value = '';
        input.disabled = tf.checked;
      }
      refreshCounter();
      box.classList.remove('invalid');
      if (msg) msg.textContent = '';
    });

    // Restore state after a server-side error reload
    if (need === 1 && tf.checked) input.disabled = true;
  });

  form.addEventListener('submit', e => {
    for (let i = 0; i < panels.length; i++) {
      if (!validate(panels[i])) { e.preventDefault(); show(i); validate(panels[i]); return; }
    }
    form.querySelector('button[type=submit]').disabled = true;
  });

  show(0);
})();