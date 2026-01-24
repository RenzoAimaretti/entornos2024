<?php
session_start();
if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'especialista') {
  header("Location: vistaProfesional/dashboardProfesional.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <?php if (isset($_SESSION['usuario_nombre'])): ?>
    <script>
      localStorage.setItem('usuario_id', '<?php echo $_SESSION['usuario_id']; ?>');
      localStorage.setItem('usuario_nombre', '<?php echo $_SESSION['usuario_nombre']; ?>');
    </script>
  <?php endif; ?>

  <?php require_once 'shared/navbar.php'; ?>

  <header class="container-fluid text-center p-4 mb-4" style="background-color: #00897b;">
    <div class="row">
      <div class="col">
        <div class="header-images">
          <img src="https://www.lasalut.es/wp-content/uploads/2024/01/esterilizacion-perro-gato.jpg"
            alt="Grupo de Mascotas" class="img-fluid header-img">
          <img
            src="https://www.purina.com.ar/sites/default/files/2022-10/purina-consulta-veterinaria-para-mascotas-lo-que-debes-saber.jpg"
            alt="Mascota 2" class="img-fluid header-img">
          <img
            src="https://media.istockphoto.com/id/1353103116/es/foto/veterinario-examinando-lindo-perro-pug-y-gato-en-la-cl%C3%ADnica-primer-plano-d%C3%ADa-de-vacunaci%C3%B3n.jpg?s=612x612&w=0&k=20&c=y8RP8tBmuAApVU6Ga6OkizZoAnuHHjimBgtSRoAJBEI="
            alt="Mascota 3" class="img-fluid header-img">
        </div>
      </div>
    </div>
  </header>

  <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'cliente'): ?>
    <section class="container my-4">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card shadow-sm border-0 bg-green text-center">
            <div class="card-body">
              <h3 class="card-title text-white">Autogestión de Turnos</h3>
              <p class="card-text text-white">Solicita y administra tus turnos de forma rápida y sencilla.</p>
              <a href="vistaCliente/autogestion-turnos.php" class="btn btn-light font-weight-bold"
                style="color: #00897b;">
                Acceder
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="bg-green py-3 text-center my-4">
    <div class="container">
      <p class="mb-0" style="font-size: 1.1em; font-weight: bold;">
        Somos la mejor veterinaria de tu ciudad, conoce nuestros servicios y trae a tu mascota
      </p>
    </div>
  </section>

  <section class="container my-4">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <p style="font-size: 1.4em; text-align: center;">
          Desde su apertura en 1986 la veterinaria San Antón se dedicó a brindar servicios y
          productos de primerísima calidad para vos y tus mascotas, para ello contamos con los
          mejores profesionales y con equipamiento médico de última generación.
        </p>
      </div>
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <?php require_once 'shared/footer.php'; ?>
</body>

</html>