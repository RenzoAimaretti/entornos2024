<?php
    session_start();
    require '../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));$dotenv->load();
        // Crear conexión
    $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);
    
    $query = "select id, nombre from especialidad";
    $result = $conn->query($query);
    $especialidades = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $especialidades[] = $row;
        }
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta Especialistas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>
<body>
<?php require_once '../shared/navbar.php'; ?> 
<!-- Título -->
    <div class="container my-4">
        <h2 class="text-center text-white py-2" style="background-color: #a8d08d;">Alta de Especialistas</h2>
    </div>
    <div class="d-flex justify-content-center">
        <div class="card " style="width:50rem;">
            <h3 class="card-title text-center">Formulario de alta</h3>
            <div class="card-body">
                <form action="../shared/alta-especialista.php" method="POST">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required >
                    </div>
                    <div class="form-group">
                        <label for="email">Email del especialista</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="text" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="repassword">Repita la contraseña</label>
                        <input type="text" class="form-control" id="repassword" name="repassword" required>
                    </div>
                    <div class="form-group">
                        <label for="tel">Teléfono de contacto</label>
                        <input type="text" class="form-control" id="tel" name="tel" required>
                    </div>
                    <div class="form-group">
                        <label for="esp">Especialidad</label>
                        <select class="form-control" id="esp" name="esp" required>
                            <option value="" disabled selected>Seleccione una especialidad</option>
                            <?php foreach ($especialidades as $especialidad): ?>
                                <option value="<?php echo htmlspecialchars($especialidad['id']); ?>">
                                    <?php echo htmlspecialchars($especialidad['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar Especialista</button>
                </form>
            </div>
        </div>
    </div>

<!-- Bootstrap Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>