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

  <!-- Navegación -->
  <?php require_once 'shared/navbar.php'; ?>

  <!-- Encabezado -->
  <?php if (isset($_SESSION['usuario_nombre'])): ?>
    <h2 class="text-center my-4" style="background-color: #a8d08d; width:100%">Vista de tipo:
      <?php echo $_SESSION['usuario_tipo']; ?>
    </h2>
  <?php endif; ?>

  <header class="container text-center my-4">
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
        <p class="lead">Somos la mejor veterinaria de tu ciudad, conoce nuestros servicios y trae a tu mascota</p>
      </div>
    </div>
  </header>

  <!-- Sección de Autogestión de Turnos -->
  <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'cliente'): ?>
    <section class="container text-center my-4">
      <h3>Autogestión de Turnos</h3>
      <p>Solicita y administra tus turnos de forma rápida y sencilla.</p>
      <a href="vistaCliente/autogestion-turnos.php" class="btn btn-primary">Acceder</a>
    </section>
  <?php endif; ?>

  <!-- Franja Verde -->
  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Somos la mejor veterinaria de tu ciudad, conoce nuestros servicios y trae a tu mascota</p>
    </div>
  </section>

  <!-- Sección de Bienvenida -->
  <section class="container my-4">
    <div class="row">
      <div class="col-md-8">
        <p>Desde su apertura en 1986 la veterinaria San Antón se dedicó a brindar servicios y productos de primerísima
          calidad para vos y tus mascotas, para ello contamos con los mejores profesionales y con equipamiento médico de
          última generación.</p>
      </div>
      <div class="col-md-4">
        <p>Encontranos en:</p>
        <img
          src="https://st2.depositphotos.com/4242631/6430/v/450/depositphotos_64302369-stock-illustration-map-icon-with-pin-pointer.jpg"
          alt="Mapa" class="img-fluid">
      </div>
    </div>
  </section>

  <!-- Franja Verde -->
  <section class="bg-green text-white py-2 text-center">
    <div class="container">
      <p class="mb-0">Teléfono de contacto: 115673346 | Mail: sananton24@gmail.com</p>
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <?php require_once 'shared/footer.php'; ?>
</body>

</html>