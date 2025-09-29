document.addEventListener('DOMContentLoaded', function () {
  const applyForm = document.getElementById('apply-form');
  if (applyForm) {
    applyForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const projectId = this.dataset.projectId;
      const messageField = document.getElementById('message');
      const message = (messageField.value || '').trim();
      if (!message) { alert('Por favor ingresa un mensaje.'); return; }
      fetch(`/project/${projectId}/apply`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: `message=${encodeURIComponent(message)}`
      }).then(r => r.json()).then(data => {
        if (data.success) {
          alert('¡Postulación enviada!');
          messageField.value='';
          messageField.disabled=true;
          this.querySelector('button[type="submit"]').disabled=true;
        } else {
          alert(data.error || 'Error al postular.');
        }
      }).catch(err => {
        console.error(err);
        alert('Error de red.');
      });
    });
  }
});
