<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Contáctanos</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container mt-5 mb-5">

    <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
      <h1 class="mb-0 font-weight-bold">Contáctanos</h1>
      <p class="mb-0 mt-2" style="font-size: 1.1rem; opacity: 0.9;">Estamos aquí para asesorarte y cuidar a tu mascota
      </p>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-5">

        <div class="row align-items-center">

          <div class="col-md-6 mb-4 mb-md-0 border-right">
            <h3 class="text-center mb-4" style="color: #00897b;">Canales de Atención</h3>
            <p class="text-center text-muted mb-4">
              ¿Tenés dudas sobre nuestros planes o necesitas un turno urgente?<br>
              Elegí tu medio de comunicación preferido.
            </p>

            <a href="https://wa.me/5493412754750" target="_blank"
              class="btn btn-success btn-lg btn-block shadow-sm mb-3 d-flex align-items-center justify-content-center">
              <i class="fab fa-whatsapp fa-2x mr-3"></i>
              <span class="text-left">
                <small class="d-block">Escribinos por WhatsApp</small>
                <strong>+54 9 341 275-4750</strong>
              </span>
            </a>

            <a href="tel:+54115673346"
              class="btn btn-outline-dark btn-lg btn-block shadow-sm d-flex align-items-center justify-content-center">
              <i class="fas fa-phone-alt fa-2x mr-3" style="color: #00897b;"></i>
              <span class="text-left">
                <small class="d-block">Llamanos por Teléfono</small>
                <strong>11 5673 346</strong>
              </span>
            </a>
          </div>

          <div class="col-md-6 pl-md-5">
            <h4 class="mb-3" style="color: #333;">Visitanos en la clínica</h4>

            <ul class="list-unstyled">
              <li class="mb-3">
                <i class="fas fa-map-marker-alt mr-2" style="color: #00897b; font-size: 1.2rem;"></i>
                <strong>Dirección:</strong><br>
                <span class="text-muted ml-4">Av. Siempre Viva 123, Rosario, Santa Fe</span>
              </li>
              <li class="mb-3">
                <i class="fas fa-clock mr-2" style="color: #00897b; font-size: 1.2rem;"></i>
                <strong>Horarios de Atención:</strong><br>
                <span class="text-muted ml-4">Lunes a Viernes: 9:00 - 20:00 hs</span><br>
                <span class="text-muted ml-4">Sábados: 9:00 - 13:00 hs</span>
              </li>
              <li class="mb-3">
                <i class="fas fa-envelope mr-2" style="color: #00897b; font-size: 1.2rem;"></i>
                <strong>Email:</strong><br>
                <span class="text-muted ml-4">sanantonn24@gmail.com</span>
              </li>
            </ul>

            <div class="mt-4 text-center">
              <img
                src="https://st2.depositphotos.com/4242631/6430/v/450/depositphotos_64302369-stock-illustration-map-icon-with-pin-pointer.jpg"
                alt="Ubicación" class="img-fluid rounded shadow-sm" style="max-height: 150px;">
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <?php require_once 'shared/footer.php'; ?>
</body>

</html>