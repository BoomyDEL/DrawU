        // Carrusel de mascotas
        const mascotas = [
            { nombre: 'Mascota 1', estilo: 'ESTILO MORADO',   tema: '' },
            { nombre: 'Mascota 2', estilo: 'ESTILO AQUA',     tema: 'tema-aqua' },
            { nombre: 'Mascota 3', estilo: 'ESTILO VERDE',    tema: 'tema-verde' }
        ];
        let actual = 0;
        const page = document.querySelector('.page');

        function mostrarMascota(i) {
            actual = (i + mascotas.length) % mascotas.length;
            const m = mascotas[actual];
            document.getElementById('petName').textContent = m.nombre;
            document.getElementById('petNameOutline').textContent = m.nombre;
            document.getElementById('petDescription').innerHTML = 'Imagen mascota ' + (actual + 1) + '<span>' + m.estilo + '</span>';
            page.classList.remove('tema-aqua', 'tema-verde');
            if (m.tema) page.classList.add(m.tema);
            document.getElementById('status').textContent = '';
        }

        document.getElementById('previous').addEventListener('click', () => mostrarMascota(actual - 1));
        document.getElementById('next').addEventListener('click', () => mostrarMascota(actual + 1));
        document.getElementById('applyButton').addEventListener('click', () => {
            // Ir a la página de inicio
            window.location.href = 'inicio.php';
        });