/**
 * monedas_cambio.js
 * Lógica para la compra y equipamiento de estilos de mascotas con Draws conectada a la BD.
 */

document.addEventListener('DOMContentLoaded', () => {
    const estadoInicial = window.DRAWU_TIENDA || {
        drawings: window.DRAWU?.drawings || 0,
        activa: 1,
        desbloqueadas: [1],
        precios: { 1: 0, 2: 50, 3: 100 }
    };

    const PET_CONFIG = {
        1: { name: 'Mascota 1 (Dylan)', price: estadoInicial.precios?.[1] ?? 0, themeClass: '', globalClass: '', colorName: 'morado' },
        2: { name: 'Mascota 2 (Tommy)', price: estadoInicial.precios?.[2] ?? 50, themeClass: 'tema-aqua', globalClass: 'tema-pet-aqua', colorName: 'azul/aqua' },
        3: { name: 'Mascota 3 (Boomy)', price: estadoInicial.precios?.[3] ?? 100, themeClass: 'tema-verde', globalClass: 'tema-pet-verde', colorName: 'verde' }
    };

    let coins = Number(estadoInicial.drawings) || 0;
    let activePet = Number(estadoInicial.activa) || 1;
    let unlocked = Array.isArray(estadoInicial.desbloqueadas) ? estadoInicial.desbloqueadas.map(Number) : [1];

    const pageEl = document.getElementById('monedasPage');
    const headerCoinsCountEl = document.querySelector('.header-coins-count');
    const drawingsBadgeCountEl = document.getElementById('drawingsBadgeCount');
    const toastEl = document.getElementById('notificationToast');

    let toastTimer = null;

    function showToast(message, type = 'info') {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = 'notification-toast show toast-' + type;

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toastEl.classList.remove('show');
        }, 3200);
    }

    function updateCoinDisplays(amount) {
        coins = Math.max(0, Number(amount) || 0);
        if (window.DRAWU) window.DRAWU.drawings = coins;
        if (headerCoinsCountEl) headerCoinsCountEl.textContent = String(coins);
        if (drawingsBadgeCountEl) drawingsBadgeCountEl.textContent = String(coins);
    }

    function applyTheme(petId) {
        const pet = PET_CONFIG[petId];

        if (pageEl) {
            pageEl.classList.remove('tema-aqua', 'tema-verde');
            if (pet && pet.themeClass) {
                pageEl.classList.add(pet.themeClass);
            }
        }

        document.body.classList.remove('tema-pet-aqua', 'tema-pet-verde');
        document.documentElement.classList.remove('tema-pet-aqua', 'tema-pet-verde');
        if (pet && pet.globalClass) {
            document.body.classList.add(pet.globalClass);
            document.documentElement.classList.add(pet.globalClass);
        }
        if (window.DRAWU) {
            window.DRAWU.temaMascota = pet?.globalClass || '';
        }
        try {
            localStorage.setItem('drawu_active_pet', String(petId));
        } catch {}
    }

    function triggerBrushAnimation(petId) {
        const row = document.querySelector(`.pet-card-row[data-pet-id="${petId}"]`);
        if (!row) return;

        const brush = row.querySelector('.brush-img');
        if (!brush) return;

        brush.classList.remove('brush-animating');
        void brush.offsetWidth;
        brush.classList.add('brush-animating');

        setTimeout(() => {
            brush.classList.remove('brush-animating');
        }, 900);
    }

    function renderButtons() {
        document.querySelectorAll('.pet-card-row').forEach(row => {
            const petId = parseInt(row.dataset.petId, 10);
            const price = parseInt(row.dataset.price, 10);
            const btn = row.querySelector('.btn-pet-action');
            if (!btn) return;

            btn.className = 'btn-pet-action';

            if (petId === activePet) {
                btn.textContent = 'Equipado';
                btn.classList.add('btn-equipado');
                btn.disabled = false;
            } else if (unlocked.includes(petId)) {
                btn.textContent = 'Aplicar';
                btn.classList.add('btn-desbloqueado');
                btn.disabled = false;
            } else {
                btn.textContent = 'Obtener';
                if (coins < price) {
                    btn.title = `Te faltan ${price - coins} Draws`;
                } else {
                    btn.title = `Obtén este estilo por ${price} Draws`;
                }
            }
        });
    }

    async function handleActionClick(petId) {
        const pet = PET_CONFIG[petId];
        if (!pet) return;

        if (petId === activePet) {
            triggerBrushAnimation(petId);
            showToast(`¡El estilo de ${pet.name} ya está activo!`, 'info');
            return;
        }

        try {
            const res = await window.drawuApi('mascota.php', {
                accion: 'obtener',
                id_mascota: petId
            });

            activePet = Number(res.activa) || petId;
            unlocked = Array.isArray(res.desbloqueadas) ? res.desbloqueadas.map(Number) : unlocked;
            updateCoinDisplays(res.drawings);
            applyTheme(activePet);
            triggerBrushAnimation(petId);
            renderButtons();

            if (res.comprada) {
                showToast(`¡Felicidades! Has desbloqueado y aplicado ${pet.name} por ${pet.price} Draws.`, 'success');
            } else {
                showToast(`¡Estilo de ${pet.name} aplicado con éxito!`, 'success');
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    document.querySelectorAll('.btn-pet-action').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const petId = parseInt(e.currentTarget.dataset.petId, 10);
            handleActionClick(petId);
        });
    });

    document.querySelectorAll('.pet-card-bar').forEach(bar => {
        bar.addEventListener('click', (e) => {
            const row = e.currentTarget.closest('.pet-card-row');
            if (row) {
                const petId = parseInt(row.dataset.petId, 10);
                triggerBrushAnimation(petId);
            }
        });
    });

    updateCoinDisplays(coins);
    applyTheme(activePet);
    renderButtons();
});
