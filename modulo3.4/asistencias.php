<?php
session_start();
include_once("conexion.php");

$rol = $_SESSION['rol'] ?? '';
$usuario = $_SESSION['usuario'] ?? '';
$correo = $_SESSION['correo'] ?? '';

// DOCENTE: solo ve su grado y sección (guardados en la sesión)
if ($rol === 'Docente') {
    $id_grado = $_SESSION['grado'] ?? '';
    $id_seccion = $_SESSION['seccion'] ?? '';
} else {
    // ADMIN: puede filtrar
    $id_grado = $_GET['id_grado'] ?? ($_POST['id_grado'] ?? '');
    $id_seccion = $_GET['id_seccion'] ?? ($_POST['id_seccion'] ?? '');
}

// Obtener nombre del grado y sección
$nombre_grado = '';
$nombre_seccion = '';
if ($id_grado) {
    $res_grado = pg_query_params($conexion, "SELECT nombre FROM grados WHERE id = $1", [$id_grado]);
    $nombre_grado = $res_grado && pg_num_rows($res_grado) ? pg_fetch_result($res_grado, 0, 0) : '';
}
if ($id_seccion) {
    $res_seccion = pg_query_params($conexion, "SELECT nombre FROM secciones WHERE id = $1", [$id_seccion]);
    $nombre_seccion = $res_seccion && pg_num_rows($res_seccion) ? pg_fetch_result($res_seccion, 0, 0) : '';
}

// ADMIN: cargar todos los grados y todas las secciones
$grados = pg_fetch_all(pg_query($conexion, "SELECT * FROM grados ORDER BY nombre")) ?: [];
$secciones = pg_fetch_all(pg_query($conexion, "SELECT * FROM secciones ORDER BY nombre")) ?: [];

// Cargar estudiantes del grupo
$estudiantes = [];
if ($id_grado && $id_seccion) {
    $estudiantes = pg_fetch_all(pg_query_params(
        $conexion,
        "SELECT * FROM estudiantes WHERE id_grado=$1 AND id_seccion=$2 ORDER BY apellidos, nombres",
        [$id_grado, $id_seccion]
    )) ?: [];
}

// Guardar asistencia
$mensaje = '';
if (isset($_POST['guardar_asistencia'])) {
    $fecha = $_POST['fecha'];
    foreach ($_POST['asistencia'] as $cod_estudiante => $estado) {
        $query = "INSERT INTO asistencias (cod_estudiante, fecha, estado) VALUES ($1, $2, $3)";
        pg_query_params($conexion, $query, [$cod_estudiante, $fecha, $estado]);
    }
    $mensaje = '¡Asistencia registrada!';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Toma de Asistencia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        .container-asistencia {
            max-width: 700px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: var(--sombra); padding: 32px;
        }
        h2 { color: #2980d9; }
        form { margin-bottom: 30px; }
        label { font-weight: 600; color: #333;}
        select, input[type="date"] {
            width: 100%; margin-top: 8px; margin-bottom: 10px; padding: 8px; border-radius: 5px; border: 1px solid #ccc; background: #f0f6ff;
        }
        button {
            background: #12b0fa; color: #fff; border: none;
            border-radius: 6px; padding: 10px 28px; font-size: 16px; font-weight: 700; cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #2980d9; }
        table { width: 100%; margin-top: 20px; background: #f0f6ff; border-radius: 8px;}
        th, td { padding: 8px 10px; }
        th { color: #2097e4; text-align: left;}
        @media (max-width: 900px) {
            .main-content { padding: 24px 8vw 24px 8vw; }
        }
        @media (max-width: 600px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 18px 2vw 18px 2vw; }
        }
        .alert-success {
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-info {
            background: #2980d9;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <nav>
            <?php if ($rol === 'Administrador'): ?>
                <a href="dashboard.php">Gestionar Estudiantes</a>
                <a href="gestionar_profesores.php">Gestionar profesores</a>
                <a href="grados_secciones.php">Gestionar grados y secciones</a>
                <a href="asistencias.php" class="active">Asistencias</a>
                <a href="calificaciones.php">Calificaciones</a>
                <a href="asignar_grado.php">Asignar Grados</a>
                <a href="usuarios/usuarios.php">Usuarios</a>
            <?php elseif ($rol === 'Docente'): ?>
                <a href="asistencias.php" class="active">Asistencias</a>
                <a href="calificaciones.php">Calificaciones</a>
            <?php endif; ?>
        </nav>
        <div class="user">
            <strong><?= htmlspecialchars($usuario) ?></strong><br>
            <?= htmlspecialchars($correo) ?>
            <br><br>
            <button class="btn-salir" onclick="salir()">Salir</button>
        </div>
    </aside>
    <main class="main-content">
        <div class="container-asistencia">
            <h2>Toma de Asistencia</h2>
            <?php if ($rol === 'Administrador'): ?>
            <!-- Filtro para admin -->
            <form method="GET" style="display: flex; flex-wrap: wrap; gap: 18px; align-items: flex-end;">
                <div style="flex:1; min-width: 180px;">
                    <label for="id_grado">Grado:</label>
                    <select name="id_grado" id="id_grado" required>
                        <option value="">Seleccione grado</option>
                        <?php foreach($grados as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= $id_grado == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1; min-width: 180px;">
                    <label for="id_seccion">Sección:</label>
                    <select name="id_seccion" id="id_seccion" required>
                        <option value="">Seleccione sección</option>
                        <?php foreach($secciones as $s): ?>
                            <option value="<?= $s['id'] ?>" data-grado="<?= $s['id_grado'] ?>" <?= ($id_seccion == $s['id'] && $id_grado == $s['id_grado']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit">Filtrar</button>
                </div>
            </form>
            <?php else: ?>
            <!-- Docente ve solo su grado y sección asignados -->
            <div style="margin-bottom: 18px;">
                <strong>Grado:</strong> <?= htmlspecialchars($nombre_grado) ?> &nbsp;
                <strong>Sección:</strong> <?= htmlspecialchars($nombre_seccion) ?>
            </div>
            <?php endif; ?>
            <?php if ($mensaje): ?>
                <div class="alert-success"><?= $mensaje ?></div>
            <?php endif; ?>

            <?php if ($id_grado && $id_seccion && $estudiantes): ?>
            <form method="POST">
                <input type="hidden" name="fecha" value="<?= date('Y-m-d') ?>">
                <input type="hidden" name="id_grado" value="<?= htmlspecialchars($id_grado) ?>">
                <input type="hidden" name="id_seccion" value="<?= htmlspecialchars($id_seccion) ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($estudiantes as $est): ?>
                        <tr>
                            <td><?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?></td>
                            <td>
                                <select name="asistencia[<?= $est['cod_estudiante'] ?>]">
                                    <option value="Presente">Presente</option>
                                    <option value="Ausente">Ausente</option>
                                    <option value="Justificado">Justificado</option>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="guardar_asistencia" style="margin-top:18px;">Guardar Asistencia</button>
            </form>
            <?php elseif ($id_grado && $id_seccion): ?>
                <div class="alert-info">No hay estudiantes en este grado y sección.</div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function salir() { window.location.href = "login.php"; }
        <?php if ($rol === 'Administrador'): ?>
        document.addEventListener("DOMContentLoaded", function() {
            const gradoSelect = document.getElementById('id_grado');
            const seccionSelect = document.getElementById('id_seccion');
            const seccionOptions = Array.from(seccionSelect.options);

            function actualizarSecciones() {
                const idGrado = gradoSelect.value;
                seccionSelect.innerHTML = '<option value="">Seleccione sección</option>';
                seccionOptions.forEach(opt => {
                    if(opt.value === "") return;
                    if(opt.getAttribute('data-grado') === idGrado) {
                        seccionSelect.appendChild(opt.cloneNode(true));
                    }
                });
                seccionSelect.value = "<?= htmlspecialchars($id_seccion) ?>";
            }

            gradoSelect.addEventListener('change', actualizarSecciones);
            if(gradoSelect.value) {
                actualizarSecciones();
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>