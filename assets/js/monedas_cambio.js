/**
 * monedas_cambio.js
 * Lógica para la compra y equipamiento de estilos de mascotas con Draws.
 * Sincroniza las monedas del header, anima los pinceles y los colores del tema de todo el sitio.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Configuración de las mascotas
    const PET_CONFIG = {
        1: { name: 'Mascota 1 (Dylan)', price: 0, themeClass: '', globalClass: '', colorName: 'morado' },
        2: { name: 'Mascota 2 (Tommy)', price: 50, themeClass: 'tema-aqua', globalClass: 'tema-pet-aqua', colorName: 'azul/aqua' },
        3: { name: 'Mascota 3 (Boomy)', price: 100, themeClass: 'tema-verde', globalClass: 'tema-pet-verde', colorName: 'verde' }
    };

    // Almacenamiento local persistente
    const STORAGE_KEY_COINS = 'drawu_user_coins';
    const STORAGE_KEY_PET = 'drawu_active_pet';
    const STORAGE_KEY_UNLOCKED = 'drawu_unlocked_pets';

    // Inicializar valores por defecto si no existen
    if (localStorage.getItem(STORAGE_KEY_COINS) === null) {
        localStorage.setItem(STORAGE_KEY_COINS, '100'); // Saldo inicial de prueba
    }

    if (!localStorage.getItem(STORAGE_KEY_PET)) {
        localStorage.setItem(STORAGE_KEY_PET, '1'); // Mascota 1 (Dylan) por defecto
    }

    if (!localStorage.getItem(STORAGE_KEY_UNLOCKED)) {
        localStorage.setItem(STORAGE_KEY_UNLOCKED, JSON.stringify([1]));
    }

    // Elementos del DOM
    const pageEl = document.getElementById('monedasPage');
    const headerCoinsCountEl = document.querySelector('.header-coins-count');
    const drawingsBadgeCountEl = document.getElementById('drawingsBadgeCount');
    const toastEl = document.getElementById('notificationToast');
    const btnConseguirDrawings = document.getElementById('btnConseguirDrawings');

    let toastTimer = null;

    // Notificaciones tipo toast
    function showToast(message, type = 'info') {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = 'notification-toast show toast-' + type;

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toastEl.classList.remove('show');
        }, 3200);
    }

    function getCoins() {
        return parseInt(localStorage.getItem(STORAGE_KEY_COINS) || '0', 10);
    }

    function setCoins(amount) {
        const safeAmount = Math.max(0, amount);
        localStorage.setItem(STORAGE_KEY_COINS, safeAmount.toString());
        updateCoinDisplays(safeAmount);
    }

    function getActivePet() {
        return parseInt(localStorage.getItem(STORAGE_KEY_PET) || '1', 10);
    }

    function setActivePet(petId) {
        localStorage.setItem(STORAGE_KEY_PET, petId.toString());
        applyTheme(petId);
    }

    function getUnlockedPets() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY_UNLOCKED)) || [1];
        } catch (e) {
            return [1];
        }
    }

    function unlockPet(petId) {
        const list = getUnlockedPets();
        if (!list.includes(petId)) {
            list.push(petId);
            localStorage.setItem(STORAGE_KEY_UNLOCKED, JSON.stringify(list));
        }
    }

    function updateCoinDisplays(coins) {
        if (headerCoinsCountEl) {
            headerCoinsCountEl.textContent = coins;
        }
        if (drawingsBadgeCountEl) {
            drawingsBadgeCountEl.textContent = coins;
        }
    }

    // Aplica los colores de la mascota tanto en esta ventana como globalmente en el body
    function applyTheme(petId) {
        const pet = PET_CONFIG[petId];

        // 1. En la vista de monedas_cambio
        if (pageEl) {
            pageEl.classList.remove('tema-aqua', 'tema-verde');
            if (pet && pet.themeClass) {
                pageEl.classList.add(pet.themeClass);
            }
        }

        // 2. Globalmente en el body y html para todos los componentes (navbar, sidebar, header, etc.)
        document.body.classList.remove('tema-pet-aqua', 'tema-pet-verde');
        document.documentElement.classList.remove('tema-pet-aqua', 'tema-pet-verde');
        if (pet && pet.globalClass) {
            document.body.classList.add(pet.globalClass);
            document.documentElement.classList.add(pet.globalClass);
        }
    }

    // Animación del pincel moviéndose desde el extremo hasta su posición
    function triggerBrushAnimation(petId) {
        const row = document.querySelector(`.pet-card-row[data-pet-id="${petId}"]`);
        if (!row) return;

        const brush = row.querySelector('.brush-img');
        if (!brush) return;

        brush.classList.remove('brush-animating');
        void brush.offsetWidth; // Reflow para reiniciar la animación CSS
        brush.classList.add('brush-animating');

        setTimeout(() => {
            brush.classList.remove('brush-animating');
        }, 900);
    }

    function renderButtons() {
        const coins = getCoins();
        const activePet = getActivePet();
        const unlocked = getUnlockedPets();

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

    function handleActionClick(petId) {
        const pet = PET_CONFIG[petId];
        if (!pet) return;

        const coins = getCoins();
        const activePet = getActivePet();
        const unlocked = getUnlockedPets();

        // 1. Si ya está equipado
        if (petId === activePet) {
            triggerBrushAnimation(petId);
            showToast(`¡El estilo de ${pet.name} ya está activo!`, 'info');
            return;
        }

        // 2. Si ya está comprado / desbloqueado: aplicar
        if (unlocked.includes(petId)) {
            setActivePet(petId);
            triggerBrushAnimation(petId);
            renderButtons();
            showToast(`¡Estilo de ${pet.name} aplicado con éxito!`, 'success');
            return;
        }

        // 3. Si necesita comprarse: verificar saldo
        if (coins >= pet.price) {
            const newCoins = coins - pet.price;
            setCoins(newCoins);
            unlockPet(petId);
            setActivePet(petId);
            triggerBrushAnimation(petId);
            renderButtons();
            showToast(`¡Felicidades! Has desbloqueado y aplicado ${pet.name} por ${pet.price} Draws.`, 'success');
        } else {
            const missing = pet.price - coins;
            showToast(`No tienes suficientes Draws. Te faltan ${missing} Draws para obtener ${pet.name}.`, 'error');
        }
    }

    // Event listeners en botones de aplicar/obtener
    document.querySelectorAll('.btn-pet-action').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const petId = parseInt(e.currentTarget.dataset.petId, 10);
            handleActionClick(petId);
        });
    });

    // Permitir clic en el espacio de color / barra para probar animación directamente
    document.querySelectorAll('.pet-card-bar').forEach(bar => {
        bar.addEventListener('click', (e) => {
            const row = e.currentTarget.closest('.pet-card-row');
            if (row) {
                const petId = parseInt(row.dataset.petId, 10);
                triggerBrushAnimation(petId);
            }
        });
    });

    // Botón de recarga de demostración (+50 Draws)
    if (btnConseguirDrawings) {
        btnConseguirDrawings.addEventListener('click', (e) => {
            e.preventDefault();
            const current = getCoins();
            const bonus = 50;
            setCoins(current + bonus);
            renderButtons();
            showToast(`¡Has recibido +${bonus} Draws de prueba!`, 'success');
        });
    }

    // Inicializar estado
    const initialCoins = getCoins();
    updateCoinDisplays(initialCoins);
    applyTheme(getActivePet());
    renderButtons();
});
