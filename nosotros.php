<?php
session_start()
  ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Veterinaria San Antón - Nosotros</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="styles.css" rel="stylesheet">
  <style>
    p {
      text-align: justify;
    }
  </style>
</head>

<body>
  <?php require_once 'shared/navbar.php'; ?>

  <div class="container mt-5 mb-5">

    <div class="bg-green p-4 rounded text-white text-center shadow-sm mb-4">
      <h1 class="mb-0 font-weight-bold">Nosotros</h1>
    </div>

    <div class="card shadow-sm border-0 mb-5">
      <div class="card-body p-5">
        <div class="row">
          <div class="col-md-6 mb-4">
            <h3 class="mb-3" style="color: #00897b;">Comprometidos con el bienestar animal</h3>
            <p class="text-secondary">
              En la veterinaria <strong>San Antón</strong> nos dedicamos al cuidado integral de las mascotas,
              brindando servicios médicos, estéticos y de acompañamiento durante todas las etapas de su vida.
              Nuestro objetivo es ofrecer una atención profesional, cercana y confiable, tanto para las mascotas
              como para sus dueños.
            </p>
            <p class="text-secondary">
              Trabajamos con una visión preventiva y responsable, priorizando la salud, el bienestar y la calidad
              de vida de cada animal que atendemos.
            </p>
          </div>

          <div class="col-md-6 mb-4">
            <h3 class="mb-3" style="color: #00897b;">Quiénes somos</h3>
            <p class="text-secondary">
              San Antón es una clínica veterinaria ubicada en la ciudad de Rosario, Santa Fe, formada por un equipo
              de médicos veterinarios titulados y personal especializado. Contamos con profesionales capacitados
              para la atención de perros, gatos y mascotas exóticas, tanto en la clínica como en consultas a domicilio.
            </p>
            <p class="text-secondary">
              Nuestro equipo está integrado por veterinarios, personal administrativo y especialistas en peluquería
              animal, trabajando de manera coordinada para garantizar una atención ordenada, segura y de calidad.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card border-0 shadow-sm bg-green text-white">
          <div class="card-body p-4 text-center">
            <h3 class="card-title font-weight-bold">Una clínica moderna y organizada</h3>
            <p class="card-text" style="color: #e0f2f1;">
              Apostamos a la incorporación de herramientas digitales que nos permitan optimizar la gestión de la
              información, mejorar la organización interna y brindar un mejor servicio a nuestros clientes.
              A través de nuestro sistema web, centralizamos el registro de mascotas, dueños, profesionales,
              atenciones y servicios, asegurando trazabilidad, seguridad y acceso rápido a la información.
            </p>
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