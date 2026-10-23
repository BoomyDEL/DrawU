<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elige a tu mascota</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/elegir_mascota.css">
</head>
<body>


    <main class="page">
    <header class="intro">
      <p class="eyebrow">Antes de iniciar...</p>
      <h1>Elige a tu<br>nuevo mejor amigo</h1>
    </header>

    <div class="wave" aria-hidden="true"></div>
    
    <section class="picker" aria-live="polite">
      <div class="pet-title">
        <div id="petName">Dylan</div>
        <div class="outline" id="petNameOutline">Dylan</div>
      </div>
      
      <!-- Contenedor con la imagen del personaje centrada -->
      <div class="pet-character-display" id="petCharacterDisplay">
        <img id="petImg" src="../assets/img/personajes/dylan.png" alt="Mascota" class="picker-character-img">
      </div>

      <div class="pet-placeholder" id="petDescription"><span>ESTILO MORADO</span></div>
      <button class="apply" id="applyButton">Aplicar</button>
    </section>

    <button class="arrow left" id="previous" aria-label="Mascota anterior">
      <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 4 7 12l8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="arrow right" id="next" aria-label="Siguiente mascota">
      <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 4 8 8-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div class="status" id="status" role="status"></div>
  </main>

  <script src="../assets/js/elegir_mascota.js"></script>
</body>
</html>
