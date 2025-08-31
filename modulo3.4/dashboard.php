<?php
session_start();
include_once("conexion.php");

$rol = $_SESSION['rol'] ?? '';
$nombre_usuario = $_SESSION['usuario'] ?? '';
$correo_usuario = $_SESSION['correo'] ?? '';

$user_sidebar_name = htmlspecialchars($nombre_usuario ?: 'Usuario');
$user_sidebar_mail = htmlspecialchars($correo_usuario ?: '');

if ($rol === 'Administrador') {
    $grados = pg_fetch_all(pg_query($conexion, "SELECT * FROM grados ORDER BY nombre")) ?: [];
    $secciones = pg_fetch_all(pg_query($conexion, "SELECT * FROM secciones ORDER BY nombre")) ?: [];

    // Guardar estudiante y usuario automáticamente
    if (isset($_POST['guardar_estudiante'])) {
        $nombres = $_POST['nombres'];
        $apellidos = $_POST['apellidos'];
        $genero = $_POST['genero'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $sangre = $_POST['sangre'];
        $direccion = $_POST['direccion'];
        $id_grado = $_POST['id_grado'];
        $id_seccion = $_POST['id_seccion'];

        // Insertar estudiante y obtener el nuevo cod_estudiante
        $query = "INSERT INTO estudiantes (nombres, apellidos, genero, fecha_nacimiento, correo, telefono, sangre, direccion, id_grado, id_seccion)
                  VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10) RETURNING cod_estudiante";
        $result = pg_query_params($conexion, $query, [$nombres, $apellidos, $genero, $fecha_nacimiento, $correo, $telefono, $sangre, $direccion, $id_grado, $id_seccion]);

        if ($result && pg_num_rows($result) === 1) {
            $row = pg_fetch_assoc($result);
            $cod_estudiante = $row['cod_estudiante'];

            // Crear usuario automáticamente si no existe ese correo aún
            $nombre = $nombres;
            $apellido = $apellidos;
            $dui = ""; 
            $rol_usuario = 'Estudiante';
            $clave_hash = password_hash('123456', PASSWORD_DEFAULT);
            $query_check = "SELECT 1 FROM usuarios WHERE correo = $1";
            $result_check = pg_query_params($conexion, $query_check, array($correo));

            if ($result_check && pg_fetch_row($result_check)) {
                $mensaje = "El estudiante fue creado, pero el correo ya existe como usuario.";
            } else {
                $query_user = "INSERT INTO usuarios (nombre, apellido, correo, dui, rol, clave, cod_estudiante) VALUES ($1, $2, $3, $4, $5, $6, $7)";
                $result_user = pg_query_params($conexion, $query_user, [$nombre, $apellido, $correo, $dui, $rol_usuario, $clave_hash, $cod_estudiante]);
                if ($result_user) {
                    $mensaje = "Estudiante y usuario creados correctamente.";
                } else {
                    $mensaje = "Estudiante creado, pero error al crear usuario: " . pg_last_error($conexion);
                }
            }
        } else {
            $mensaje = "Error al guardar estudiante: " . pg_last_error($conexion);
        }
    }

    if (isset($_GET['eliminar'])) {
        $id = $_GET['eliminar'];
        pg_query_params($conexion, "DELETE FROM asistencias WHERE cod_estudiante=$1", [$id]);
        pg_query_params($conexion, "DELETE FROM calificaciones WHERE cod_estudiante=$1", [$id]);
        pg_query_params($conexion, "DELETE FROM usuarios WHERE cod_estudiante=$1", [$id]);
        $result = pg_query_params($conexion, "DELETE FROM estudiantes WHERE cod_estudiante=$1", [$id]);

        if ($result) {
            $mensaje = "Estudiante y sus datos eliminados correctamente.";
        } else {
            $mensaje = "Error al eliminar estudiante: " . pg_last_error($conexion);
        }
    }

    // Listar estudiantes (con nombre de grado y sección)
    $query_estudiantes = "
        SELECT e.*, g.nombre AS grado, s.nombre AS seccion
        FROM estudiantes e
        LEFT JOIN grados g ON e.id_grado = g.id
        LEFT JOIN secciones s ON e.id_seccion = s.id
        ORDER BY e.cod_estudiante
    ";
    $estudiantes = pg_fetch_all(pg_query($conexion, $query_estudiantes)) ?: [];

    $total = count($estudiantes);
    $total_hombres = 0;
    $total_mujeres = 0;
    foreach ($estudiantes as $est) {
        if ($est['genero'] == 'M') $total_hombres++;
        if ($est['genero'] == 'F') $total_mujeres++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul-primario: #2980d9;
            --azul-secundario: #12b0fa;
            --azul-claro: #f0f6ff;
            --gris-fondo: #f7fafd;
            --blanco: #fff;
            --gris: #e6e6e6;
            --sombra: 0 8px 32px 0 rgba(31, 38, 135, 0.13), 0 1.5px 6px 0 rgba(44, 62, 80, 0.10);
        }
        body { background: var(--gris-fondo); font-family: 'Montserrat', Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;}
        .sidebar {
            position: fixed; left: 0; top: 0; width: 220px; height: 100vh;
            background: linear-gradient(135deg, var(--azul-secundario) 60%, var(--azul-primario) 100%);
            color: var(--blanco); box-shadow: var(--sombra);
            display: flex; flex-direction: column; z-index: 2;
        }
        .sidebar .logo { text-align: center; padding: 32px 0 18px 0; }
        .sidebar .logo img { width: 70px; margin-bottom: 8px; }
        .sidebar nav { flex: 1; }
        .sidebar nav a {
            display: block; color: var(--blanco); text-decoration: none;
            padding: 14px 32px; font-size: 16px; font-weight: 500;
            border-left: 4px solid transparent;
            transition: background 0.2s, border-color 0.2s;
        }
        .sidebar nav a.active, .sidebar nav a:hover {
            background: rgba(255,255,255,0.08); border-left: 4px solid var(--blanco);
        }
        .sidebar .user {
            padding: 18px 32px; border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 15px; color: #e0f2ff;
        }
        .sidebar .btn-salir {
            width: 100%; margin-top: 18px; background: #ff6b6b; color: #fff; border: none;
            border-radius: 6px; padding: 10px 0; font-size: 16px; font-weight: 700; cursor: pointer;
            transition: background 0.2s;
        }
        .sidebar .btn-salir:hover { background: #d63031; }
        .main-content { margin-left: 220px; padding: 36px 40px 24px 40px; }
        .dashboard-cards {
            display: flex; gap: 18px; margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .dashboard-card {
            background: var(--blanco);
            border-radius: 10px;
            box-shadow: var(--sombra);
            padding: 26px 32px;
            text-align: center;
            min-width: 200px;
            flex: 1;
        }
        .dashboard-card h3 {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--azul-primario);
        }
        .dashboard-card span {
            font-size: 1.1rem;
            color: #555;
        }
        .table th, .table td { vertical-align: middle !important; }
        .modal-header { border-bottom: 1px solid #e5e5e5; }
        .modal-footer { border-top: 1px solid #e5e5e5; }
        .btn-info, .btn-info:active, .btn-info:focus { background: var(--azul-secundario) !important; border: none;}
        .btn-info:hover { background: var(--azul-primario) !important; }
        .btn-outline-secondary { border-color: var(--azul-secundario); color: var(--azul-secundario);}
        .btn-outline-secondary:hover { background: var(--azul-secundario); color: var(--blanco);}
        .table thead { background: var(--azul-claro);}
        .table th { color: var(--azul-primario); font-weight: 700;}
        @media (max-width: 900px) {
            .main-content { padding: 24px 8vw 24px 8vw; }
            .dashboard-cards { flex-direction: column; gap: 12px;}
        }
        @media (max-width: 600px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 18px 2vw 18px 2vw; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <nav>
            <?php if ($rol === 'Administrador'): ?>
                <a href="dashboard.php" class="active">Gestionar Estudiantes</a>
                <a href="gestionar_profesores.php">Gestionar profesores</a>
                <a href="grados_secciones.php">Gestionar grados y secciones</a>
                <a href="asistencias.php">Asistencias</a>
                <a href="calificaciones.php">Calificaciones</a>
                <a href="asignar_grado.php">Asignar grados</a>
                <a href="usuarios/usuarios.php">Usuarios</a>
            <?php elseif ($rol === 'Docente'): ?>
                <a href="asistencias.php">Asistencias</a>
                <a href="calificaciones.php">Calificaciones</a>
            <?php elseif ($rol === 'Estudiante'): ?>  
                <a href="ver_calificaciones.php">Mis calificaciones</a>
            <?php endif; ?>
        </nav>
        <div class="user">
            <strong><?= $user_sidebar_name ?></strong><br>
            <?= $user_sidebar_mail ?>
            <br><br>
            <button class="btn-salir" onclick="salir()">Salir</button>
        </div>
    </aside>
    <main class="main-content">
        <?php if ($rol === 'Administrador'): ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0" style="color:#2097e4"><b>Registro de Estudiantes</b></h2>
                <button class="btn btn-info" data-toggle="modal" data-target="#modalNuevoEstudiante">+ Nuevo Estudiante</button>
            </div>
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3><?= $total ?></h3>
                    <span>Total de Estudiantes</span>
                </div>
                <div class="dashboard-card">
                    <h3><?= $total_mujeres ?></h3>
                    <span>Total de Mujeres</span>
                </div>
                <div class="dashboard-card">
                    <h3><?= $total_hombres ?></h3>
                    <span>Total de Hombres</span>
                </div>
            </div>
            <!-- Tabla de estudiantes -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th>Fecha Nacimiento</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $est): ?>
                        <tr>
                            <td><?= $est['cod_estudiante'] ?></td>
                            <td><?= htmlspecialchars($est['nombres']) ?></td>
                            <td><?= htmlspecialchars($est['apellidos']) ?></td>
                            <td><?= htmlspecialchars($est['grado']) ?></td>
                            <td><?= htmlspecialchars($est['seccion']) ?></td>
                            <td><?= htmlspecialchars($est['fecha_nacimiento']) ?></td>
                            <td><?= htmlspecialchars($est['telefono']) ?></td>
                            <td>
                                <a href="editar_estudiante.php?id=<?= $est['cod_estudiante'] ?>" class="btn btn-primary btn-sm">Editar</a>
                                <a href="?eliminar=<?= $est['cod_estudiante'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro de eliminar?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal Nuevo Estudiante -->
        <div class="modal fade" id="modalNuevoEstudiante" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form method="POST" action="">
                <div class="modal-header">
                  <h5 class="modal-title" style="color:#2097e4"><b>Nuevo Estudiante</b></h5>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="nombres">Nombres*</label>
                      <input type="text" class="form-control" name="nombres" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="apellidos">Apellidos*</label>
                      <input type="text" class="form-control" name="apellidos" required>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="genero">Género*</label>
                      <select class="form-control" name="genero" required>
                        <option value="">Seleccione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                        <option value="O">Otro</option>
                      </select>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="fecha_nacimiento">Fecha Nacimiento*</label>
                      <input type="date" class="form-control" name="fecha_nacimiento" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="correo">Correo Electrónico*</label>
                    <input type="email" class="form-control" name="correo" required>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="telefono">Teléfono*</label>
                      <input type="tel" class="form-control" name="telefono" pattern="[0-9]{8,15}" title="Solo números (8 a 15 dígitos)" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="sangre">Tipo de Sangre</label>
                      <select class="form-control" name="sangre">
                        <option value="">No especificado</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                      </select>
                    </div>
                  </div>
                  <!-- Grado y Sección -->
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="id_grado">Grado*</label>
                      <select class="form-control" name="id_grado" id="id_grado" required>
                        <option value="">Seleccione un grado</option>
                        <?php foreach ($grados as $grado): ?>
                          <option value="<?= $grado['id'] ?>"><?= htmlspecialchars($grado['nombre']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="id_seccion">Sección*</label>
                      <select class="form-control" name="id_seccion" id="id_seccion" required>
                        <option value="">Seleccione una sección</option>
                        <?php foreach ($secciones as $seccion): ?>
                          <option value="<?= $seccion['id'] ?>" data-grado="<?= $seccion['id_grado'] ?>">
                            <?= htmlspecialchars($seccion['nombre']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea class="form-control" name="direccion" rows="2"></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-info" name="guardar_estudiante">Guardar Estudiante</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php elseif ($rol === 'Docente'): ?>
            <h2 style="color:#2097e4"><b>Panel Docente</b></h2>
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <span>Solo tienes acceso a:</span>
                    <h3>Asistencias y Calificaciones</h3>
                    <span>del grado y sección que te corresponde.</span>
                </div>
            </div>
        <?php elseif ($rol === 'Estudiante'): ?>
            <h2 style="color:#2097e4"><b>Panel Estudiante</b></h2>
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <span>Solo tienes acceso a ver tus asistencias y calificaciones.</span>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function salir() { window.location.href = "login.php"; }
    document.addEventListener("DOMContentLoaded", function() {
      var gradoSelect = document.getElementById('id_grado');
      var seccionSelect = document.getElementById('id_seccion');
      if (gradoSelect && seccionSelect) {
        var seccionOptions = Array.from(seccionSelect.options);

        function filtrarSecciones() {
          var idGrado = gradoSelect.value;
          seccionSelect.innerHTML = '';
          seccionSelect.appendChild(new Option('Seleccione una sección', ''));
          seccionOptions.forEach(function(opt) {
            if (opt.value === '') return;
            if (opt.getAttribute('data-grado') === idGrado) {
              seccionSelect.appendChild(opt.cloneNode(true));
            }
          });
        }
        gradoSelect.addEventListener('change', filtrarSecciones);
        filtrarSecciones();
      }
    });
    </script>
</body>
</html>