async function loadPendingUIDs() {
  try {
    const res = await fetch('api/pending_uids.php');
    const data = await res.json();
    const uids = (data && Array.isArray(data.uids)) ? data.uids : [];
    document.querySelectorAll('select.uid-select').forEach(function(sel) {
      const current = sel.value;
      // Limpiar excepto placeholder
      sel.innerHTML = '<option value="">-- Selecciona UID leído --</option>';
      uids.forEach(function(u) {
        const opt = document.createElement('option');
        opt.value = u;
        opt.textContent = u;
        sel.appendChild(opt);
      });
      // Mantener selección si sigue existiendo
      if (current && uids.includes(current)) sel.value = current;
    });
  } catch(e) {
    console.error('Error cargando UIDs:', e);
  }
}

document.addEventListener('DOMContentLoaded', function() {
  // Cargar UIDs si estamos en la página de gestión de usuarios
  if (document.querySelector('select.uid-select')) {
    loadPendingUIDs();
    
    // Configurar botones de refresco por fila
    document.querySelectorAll('.refresh-uids').forEach(function(btn) {
      btn.addEventListener('click', function() {
        loadPendingUIDs();
        btn.textContent = 'Actualizado';
        setTimeout(() => { btn.textContent = 'Actualizar UIDs'; }, 1500);
      });
    });

    // Auto-actualización periódica solo si el elemento tiene el atributo data-auto-refresh
    const refreshInterval = document.querySelector('select.uid-select').getAttribute('data-auto-refresh');
    if (refreshInterval) {
      setInterval(loadPendingUIDs, parseInt(refreshInterval));
    }
  }
});
