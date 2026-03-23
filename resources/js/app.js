import './bootstrap';

// ── SweetAlert2 (cargado vía CDN antes de este módulo) ───────────────────────
// Guard defensivo: si el CDN falla, se usan alerts nativos para no romper nada.
const Swal = window.Swal ?? null;

const Toast = Swal ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    },
}) : null;

// ── Diálogo de confirmación reutilizable ─────────────────────────────────────
window.confirmAction = function (message, callback, options = {}) {
    if (Swal) {
        Swal.fire({
            title: options.title || '¿Estás seguro?',
            text: message,
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: options.danger ? '#dc2626' : '#10472a',
            cancelButtonColor: '#64748b',
            confirmButtonText: options.confirmText || 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) callback();
        });
    } else if (window.confirm(message)) {
        callback();
    }
};

// ── Escucha eventos de Livewire ───────────────────────────────────────────────
document.addEventListener('livewire:init', () => {
    Livewire.on('notify', ({ type, message }) => {
        if (Toast) {
            Toast.fire({ icon: type, title: message });
        }
    });
});
