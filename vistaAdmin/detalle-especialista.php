<!-- quizas dejar este para admin y hacer otro aparte para el especialista -->
 <?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    if ($id === null) {
        echo "ID no proporcionado.";
    } else {
        require '../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));$dotenv->load();
        // Crear conexión
        $conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password'], $_ENV['dbname']);

        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        if ($_SESSION['usuario_tipo'] !== 'admin') {
            die("Acceso denegado");
        }

        $query = "SELECT u.id, u.nombre, u.email, p.telefono, e.nombre as especialidad 
                  FROM usuarios u 
                  inner JOIN profesionales p ON u.id = p.id
                  inner JOIN especialidad e on p.id_esp = e.id
                  WHERE u.id = $id";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
            $email = $row['email'];
            $telefono = $row['telefono'];
            $especialidad = $row['especialidad'];
        }

        
                              
    }
}
 ?>

 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de especialista</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
 </head>
 <body>
    <?php require_once '../shared/navbar.php'; ?>
    <div class="d-flex justify-content-center">
    <div class="card text-center" style="width:50rem;">
        <div class="card-header">
            <h2>Detalles de <?php echo $nombre?> </h2>
        </div>
    <div class="card-body">
        <div>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Telefono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
            <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($especialidad); ?></p>
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editarModal">Editar</button>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarModalLabel">Editar Mascota</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="../shared/editar-especialista.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>
                            </div>
                            <script>
                                // Pre-cargar los días ya registrados desde PHP a JS
                                <?php
                                $diasCargados = [];
                                $horariosQuery = "SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = $id";
                                $horariosResult = $conn->query($horariosQuery);
                                if ($horariosResult && $horariosResult->num_rows > 0) {
                                    while ($horarioRow = $horariosResult->fetch_assoc()) {
                                        $diasCargados[] = [
                                            'dia' => $horarioRow['diaSem'],
                                            'horaInicio' => $horarioRow['horaIni'],
                                            'horaFin' => $horarioRow['horaFin']
                                        ];
                                    }
                                }
                                ?>
                                const diasCargados = <?php echo json_encode($diasCargados); ?>;
                                window.addEventListener('DOMContentLoaded', function() {
                                    const container = document.getElementById('dias-container');
                                    const dias = ['Lun','Mar','Mie','Jue','Vie'];
                                    diasCargados.forEach(function(item, index) {
                                        const div = document.createElement('div');
                                        div.className = 'form-row align-items-end mb-2';
                                        div.innerHTML = `
                                            <div class="col">
                                                <select name="dias[${index}][dia]" class="form-control" required>
                                                    <option value="" disabled>Día</option>
                                                    ${dias.map(d => `<option value="${d}" ${item.dia === d ? 'selected' : ''}>${d}</option>`).join('')}
                                                </select>
                                            </div>
                                            <div class="col">
                                                <select name="dias[${index}][horaInicio]" class="form-control hora-inicio" required>
                                                    <option value="" disabled>Hora inicio</option>
                                                    ${['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00'].map(h => `<option value="${h}" ${item.horaInicio === h ? 'selected' : ''}>${h}</option>`).join('')}
                                                </select>
                                            </div>
                                            <div class="col">
                                                <select name="dias[${index}][horaFin]" class="form-control hora-fin" required>
                                                    <option value="" disabled>Hora fin</option>
                                                    ${['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00'].map(h => `<option value="${h}" ${item.horaFin === h ? 'selected' : ''}>${h}</option>`).join('')}
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-danger btn-sm remove-dia-btn">&times;</button>
                                            </div>
                                        `;
                                        container.appendChild(div);

                                        div.querySelector('.remove-dia-btn').onclick = function() {
                                            div.remove();
                                        };

                                        // Validar hora fin > hora inicio
                                        const horaInicio = div.querySelector('.hora-inicio');
                                        const horaFin = div.querySelector('.hora-fin');
                                        function validarHoras() {
                                            if (horaInicio.value && horaFin.value && horaFin.value <= horaInicio.value) {
                                                horaFin.setCustomValidity('La hora de fin debe ser mayor que la hora de inicio');
                                            } else {
                                                horaFin.setCustomValidity('');
                                            }
                                        }
                                        horaInicio.addEventListener('change', validarHoras);
                                        horaFin.addEventListener('change', validarHoras);
                                    });
                                });
                            </script>
                            <div class="form-group">
                                <label for="especialidad">Especialidad</label>
                                <select class="form-control" id="especialidad" name="especialidad" required>
                                    <?php
                                    $especialidadesQuery = "SELECT id, nombre FROM especialidad";
                                    $especialidadesResult = $conn->query($especialidadesQuery);
                                    if ($especialidadesResult->num_rows > 0) {
                                        while ($especialidadRow = $especialidadesResult->fetch_assoc()) {
                                            $selected = ($especialidadRow['nombre'] === $especialidad) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($especialidadRow['id']) . "' $selected>" . htmlspecialchars($especialidadRow['nombre']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No hay especialidades disponibles</option>";
                                    }
                                    ?>
                                </select>
                            </div>
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
                                    <select name="dias[${index}][horaInicio]" class="form-control hora-inicio" required>
                                        <option value="" disabled selected>Hora inicio</option>
                                        <option value="08:00">08:00</option>
                                        <option value="09:00">09:00</option>
                                        <option value="10:00">10:00</option>
                                        <option value="11:00">11:00</option>
                                        <option value="12:00">12:00</option>
                                        <option value="13:00">13:00</option>
                                        <option value="14:00">14:00</option>
                                        <option value="15:00">15:00</option>
                                        <option value="16:00">16:00</option>
                                        <option value="17:00">17:00</option>
                                        <option value="18:00">18:00</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <select name="dias[${index}][horaFin]" class="form-control hora-fin" required>
                                        <option value="" disabled selected>Hora fin</option>
                                        <option value="09:00">09:00</option>
                                        <option value="10:00">10:00</option>
                                        <option value="11:00">11:00</option>
                                        <option value="12:00">12:00</option>
                                        <option value="13:00">13:00</option>
                                        <option value="14:00">14:00</option>
                                        <option value="15:00">15:00</option>
                                        <option value="16:00">16:00</option>
                                        <option value="17:00">17:00</option>
                                        <option value="18:00">18:00</option>
                                        <option value="19:00">19:00</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-danger btn-sm remove-dia-btn">&times;</button>
                                </div>
                            `;
                            container.appendChild(div);

                            div.querySelector('.remove-dia-btn').onclick = function() {
                                div.remove();
                            };

                            // Validar hora fin > hora inicio
                            const horaInicio = div.querySelector('.hora-inicio');
                            const horaFin = div.querySelector('.hora-fin');

                            function validarHoras() {
                                if (horaInicio.value && horaFin.value && horaFin.value <= horaInicio.value) {
                                    horaFin.setCustomValidity('La hora de fin debe ser mayor que la hora de inicio');
                                } else {
                                    horaFin.setCustomValidity('');
                                }
                            }

                            horaInicio.addEventListener('change', validarHoras);
                            horaFin.addEventListener('change', validarHoras);
                        });
                    </script>
                    </script>
                            <div class="modal-footer d-flex justify-content-center">
                                <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top:1rem;" class="tabla-dias-atencion">
            <h3>Días de atención</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Hora inicio</th>
                        <th>Hora fin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $horariosQuery = "SELECT diaSem, horaIni, horaFin FROM profesionales_horarios WHERE idPro = $id";
                    $horariosResult = $conn->query($horariosQuery);
                    if ($horariosResult->num_rows > 0) {
                        while ($horarioRow = $horariosResult->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($horarioRow['diaSem']) . "</td>";
                            echo "<td>" . htmlspecialchars($horarioRow['horaIni']) . "</td>";
                            echo "<td>" . htmlspecialchars($horarioRow['horaFin']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td class='text-center' colspan='3'>No hay días de atención registrados</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;" class="tabla-historia-clinica">
            <h3>Atenciones</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Servicio</th>
                        <th>Profesional a cargo</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $proximosTurnos = "SELECT a.fecha, s.nombre as nombreServicio, u.nombre as nombrePro from atenciones a
                    inner join usuarios u on a.id_pro = u.id
                    inner join servicios s on a.id_serv = s.id
                    where a.fecha >= now() and a.id_pro = $id";
                    $result = $conn->query($proximosTurnos);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars(date("d-m-Y H:i:s", strtotime($row['fecha']))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nombreServicio']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nombrePro']) . "</td>";
                            echo "<td><a class=\"btn btn-info\" href='detalle-atencion.php'>Ver detalles</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td class='text-center' colspan='4'>No hay registros</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>