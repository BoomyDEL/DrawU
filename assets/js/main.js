/**
 * main.js - Sincronización global de monedas y temas en todas las vistas de DrawU
 */
(() => {
    // Sincronizar estilo/tema de mascota activo en todo el sitio
    function syncGlobalPetTheme() {
        const activePet = localStorage.getItem('drawu_active_pet') || '1';
        document.body.classList.remove('tema-pet-aqua', 'tema-pet-verde');
        document.documentElement.classList.remove('tema-pet-aqua', 'tema-pet-verde');

        if (activePet === '2') {
            document.body.classList.add('tema-pet-aqua');
            document.documentElement.classList.add('tema-pet-aqua');
        } else if (activePet === '3') {
            document.body.classList.add('tema-pet-verde');
            document.documentElement.classList.add('tema-pet-verde');
        }
    }

    // Sincronizar contador de monedas en el header
    function syncGlobalCoins() {
        const coinsCountEl = document.querySelector('.header-coins-count');
        if (coinsCountEl) {
            const storedCoins = localStorage.getItem('drawu_user_coins');
            if (storedCoins !== null) {
                coinsCountEl.textContent = storedCoins;
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            syncGlobalPetTheme();
            syncGlobalCoins();
        });
    } else {
        syncGlobalPetTheme();
        syncGlobalCoins();
    }

    // Escuchar cambios de storage si hay varias pestañas o vistas abiertas
    window.addEventListener('storage', (e) => {
        if (e.key === 'drawu_active_pet') {
            syncGlobalPetTheme();
        }
        if (e.key === 'drawu_user_coins') {
            syncGlobalCoins();
        }
    });
})();
