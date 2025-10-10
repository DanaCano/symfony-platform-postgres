document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form && form.matches('form[data-confirm]')) {
      const msg = form.getAttribute('data-confirm') || 'Voulez-vous vraiment continuer ?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    }
  });
  
  const applyForm = document.getElementById('apply-form');
  if (applyForm) {
    applyForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const projectId = this.dataset.projectId;
      const messageField = document.getElementById('message');
      const message = (messageField.value || '').trim();
      if (!message) { alert('Veuillez saisir un message.'); return; }
      fetch(`/project/${projectId}/apply`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: `message=${encodeURIComponent(message)}`
      }).then(r => r.json()).then(data => {
        if (data.success) {
          alert('Candidature envoyée !');
          messageField.value='';
          messageField.disabled=true;
          this.querySelector('button[type="submit"]').disabled=true;
        } else {
          alert(data.error || 'Erreur lors de la candidature.');
        }
      }).catch(err => {
        console.error(err);
        alert('Erreur réseau.');
      });
    });
  }
});
