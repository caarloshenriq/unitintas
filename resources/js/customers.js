function digits(v) { return (v || '').replace(/\D/g, ''); }

function maskCPF(d) {
  return d
    .replace(/^(\d{3})(\d)/, "$1.$2")
    .replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3")
    .replace(/\.(\d{3})(\d{1,2})$/, ".$1-$2")
    .slice(0, 14);
}
function maskCNPJ(d) {
  return d
    .replace(/^(\d{2})(\d)/, "$1.$2")
    .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
    .replace(/\.(\d{3})(\d)/, ".$1/$2")
    .replace(/(\d{4})(\d)/, "$1-$2")
    .slice(0, 18);
}
function validCPF(c) {
  if (!c || c.length !== 11 || /^(\d)\1+$/.test(c)) return false;
  let s = 0; for (let i=0;i<9;i++) s += parseInt(c[i])*(10-i);
  let r = (s*10)%11; if (r===10) r=0; if (r !== parseInt(c[9])) return false;
  s = 0; for (let i=0;i<10;i++) s += parseInt(c[i])*(11-i);
  r = (s*10)%11; if (r===10) r=0; return r === parseInt(c[10]);
}
function validCNPJ(c) {
  if (!c || c.length !== 14 || /^(\d)\1+$/.test(c)) return false;
  const calc = (x) => {
    let n=0,p=x-7;
    for (let i=0;i<x;i++){ n += parseInt(c[i])*p--; if(p<2) p=9; }
    const r=n%11; return r<2?0:11-r;
  }
  const t=c.length-2, d=c.substring(t);
  return calc(t)===parseInt(d[0]) && calc(t+1)===parseInt(d[1]);
}

function bindCustomerUI(root=document) {
  root.querySelectorAll('[data-doc-toggle]').forEach(toggle => {
    const form = toggle.closest('form') || root;
    const hiddenType = form.querySelector('input[name="document_type"]');
    const docInput   = form.querySelector('[data-doc-input]');
    const errorEl    = form.querySelector('[data-doc-error]');
    const labelEl    = form.querySelector('[data-doc-label]');

    const setType = (isCnpj) => {
      if (!hiddenType) return;
      hiddenType.value = isCnpj ? 'cnpj' : 'cpf';
      if (labelEl) labelEl.textContent = `Documento (${isCnpj ? 'CNPJ' : 'CPF'})`;
      if (docInput) {
        const d = digits(docInput.value);
        docInput.value = isCnpj ? maskCNPJ(d) : maskCPF(d);
        if (errorEl) errorEl.textContent = '';
      }
    };

    setType(toggle.checked);

    toggle.addEventListener('change', () => setType(toggle.checked));
  });

  root.querySelectorAll('[data-doc-input]').forEach(input => {
    input.addEventListener('input', () => {
      const form = input.closest('form') || root;
      const hiddenType = form.querySelector('input[name="document_type"]');
      const errorEl = form.querySelector('[data-doc-error]');
      const isCnpj = (hiddenType?.value === 'cnpj');
      const d = digits(input.value);
      input.value = isCnpj ? maskCNPJ(d) : maskCPF(d);

      if (errorEl) {
        errorEl.textContent = '';
        if (!isCnpj && d.length === 11 && !validCPF(d)) errorEl.textContent = 'CPF inválido';
        if ( isCnpj && d.length === 14 && !validCNPJ(d)) errorEl.textContent = 'CNPJ inválido';
      }
    });
  });

  root.querySelectorAll('[data-phone-input]').forEach(input => {
    input.addEventListener('input', () => {
      const d = digits(input.value);
      input.value = (d.length <= 10)
        ? d.replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, (m,a,b,c)=>(a?`(${a}`:'')+(a&&a.length===2?') ':'')+(b||'')+(b&&c?`-${c}`:''))
        : d.replace(/^(\d{0,2})(\d{0,5})(\d{0,4}).*/, (m,a,b,c)=>(a?`(${a}`:'')+(a&&a.length===2?') ':'')+(b||'')+(b&&c?`-${c}`:''));
    });
  });

  root.querySelectorAll('form[data-strip-masks]').forEach(form => {
    form.addEventListener('submit', () => {
      const doc = form.querySelector('[data-doc-input]');
      const phone = form.querySelector('[data-phone-input]');
      if (doc)   doc.value   = digits(doc.value);
      if (phone) phone.value = digits(phone.value);
    });
  });
}

window.initCustomers = function(root=document){ bindCustomerUI(root); };

document.addEventListener('DOMContentLoaded', () => { window.initCustomers(); });

if (window.Livewire) {
  window.Livewire.hook('message.processed', (message, component) => {
    window.initCustomers(component.el);
  });
}
