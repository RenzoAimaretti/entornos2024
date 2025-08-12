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
                        <input type="text" class="form-control" id="nombre" name="nombre" required placeholder='Nombre Apellido'>
                    </div>
                    <div class="form-group">
                        <label for="email">Email del especialista</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder='esp@gmail.com'>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="text" class="form-control" id="password" name="password" required placeholder='Esp123'>
                    </div>
                    <div class="form-group">
                        <label for="repassword">Repita la contraseña</label>
                        <input type="text" class="form-control" id="repassword" name="repassword" required placeholder='Esp123'>
                    </div>
                    <div class="form-group">
                        <label for="tel">Teléfono de contacto</label>
                        <input type="text" class="form-control" id="tel" name="tel" required placeholder='1234567890'>
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
                    <!-- Dias de atencion -->
                    <div class="form-group">
                        <label>Días de atención y horarios</label>
                        <div id="dias-container"></div>
                        <button type="button" class="btn btn-info mt-2" id="add-dia-btn">Agregar día</button>
                    </div>
                    <script>
                        document.getElementById('add-dia-btn').addEventListener('click', function() {
                            const container = document.getElementById('dias-container');
                            const index = container.children.length;
                            const dias = ['Lun','Mar','Mie','Jue','Vie'];
                            const div = document.createElement('div');
                            div.className = 'form-row align-items-end mb-2';
                            div.innerHTML = `
                                <div class="col">
                                    <select name="dias[${index}][dia]" class="form-control" required>
                                        <option value="" disabled selected>Día</option>
                                        ${dias.map(d => `<option value="${d}">${d}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col">
                                    <input type="time" name="dias[${index}][horaInicio]" class="form-control" required>
                                </div>
                                <div class="col">
                                    <input type="time" name="dias[${index}][horaFin]" class="form-control" required>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-danger btn-sm remove-dia-btn">&times;</button>
                                </div>
                            `;
                            container.appendChild(div);

                            div.querySelector('.remove-dia-btn').onclick = function() {
                                div.remove();
                            };
                        });
                    </script>
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