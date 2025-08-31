<?php
session_start();
include_once("conexion.php");

$rol = $_SESSION['rol'] ?? '';
$usuario = $_SESSION['usuario'] ?? '';
$correo = $_SESSION['correo'] ?? '';

// Filtro: grado, sección y materia
$id_grado = $_GET['id_grado'] ?? ($_POST['id_grado'] ?? '');
$id_seccion = $_GET['id_seccion'] ?? ($_POST['id_seccion'] ?? '');
$id_materia = $_GET['id_materia'] ?? ($_POST['id_materia'] ?? '');

// Cargar grados y secciones
$grados = pg_fetch_all(pg_query($conexion, "SELECT * FROM grados ORDER BY nombre")) ?: [];
$secciones = pg_fetch_all(pg_query($conexion, "SELECT * FROM secciones ORDER BY nombre")) ?: [];
$materias = pg_fetch_all(pg_query($conexion, "SELECT id, nombre FROM materias ORDER BY nombre")) ?: [];
$periodos = ['Primer Periodo', 'Segundo Periodo', 'Tercer Periodo', 'Cuarto Periodo'];

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

// Cargar estudiantes del grupo
$estudiantes = [];
if ($id_grado && $id_seccion) {
    $estudiantes = pg_fetch_all(pg_query_params(
        $conexion,
        "SELECT * FROM estudiantes WHERE id_grado=$1 AND id_seccion=$2 ORDER BY apellidos, nombres",
        [$id_grado, $id_seccion]
    )) ?: [];
}

// Guardar calificaciones
$mensaje = '';
if (isset($_POST['guardar_calificaciones'])) {
    $id_materia = $_POST['id_materia'] ?? '';
    $res_nombre = pg_query_params($conexion, "SELECT nombre FROM materias WHERE id = $1", [$id_materia]);
    $materia_nombre = $res_nombre && pg_num_rows($res_nombre) ? pg_fetch_result($res_nombre, 0, 0) : '';

    if (isset($_POST['nota']) && is_array($_POST['nota'])) {
        foreach ($_POST['nota'] as $cod_estudiante => $periodos_nota) {
            foreach ($periodos as $periodo) {
                $nota = $periodos_nota[$periodo] ?? '';
                if ($nota !== '') {
                    // Verifica si ya existe una calificación para ese estudiante, materia y periodo
                    $query_check = "SELECT 1 FROM calificaciones WHERE cod_estudiante=$1 AND id_materia=$2 AND periodo=$3";
                    $res_check = pg_query_params($conexion, $query_check, [$cod_estudiante, $id_materia, $periodo]);
                    if ($res_check && pg_fetch_row($res_check)) {
                        // Actualiza si existe
                        $query_update = "UPDATE calificaciones SET nota=$1 WHERE cod_estudiante=$2 AND id_materia=$3 AND periodo=$4";
                        pg_query_params($conexion, $query_update, [$nota, $cod_estudiante, $id_materia, $periodo]);
                    } else {
                        // Inserta si no existe
                        $query_insert = "INSERT INTO calificaciones (cod_estudiante, id_materia, nota, periodo, materia)
                                         VALUES ($1, $2, $3, $4, $5)";
                        pg_query_params($conexion, $query_insert, [$cod_estudiante, $id_materia, $nota, $periodo, $materia_nombre]);
                    }
                }
            }
        }
        $mensaje = '¡Calificaciones registradas!';
    } else {
        $mensaje = 'No se ingresaron calificaciones.';
    }
}

// Consultar calificaciones existentes para mostrar en los campos
$calificaciones_guardadas = [];
if ($id_grado && $id_seccion && $id_materia) {
    $res_notas = pg_query_params($conexion,
        "SELECT cod_estudiante, periodo, nota FROM calificaciones WHERE id_materia = $1
        AND cod_estudiante IN (SELECT cod_estudiante FROM estudiantes WHERE id_grado=$2 AND id_seccion=$3)",
        [$id_materia, $id_grado, $id_seccion]
    );
    if ($res_notas && pg_num_rows($res_notas)) {
        while ($row = pg_fetch_assoc($res_notas)) {
            $calificaciones_guardadas[$row['cod_estudiante']][$row['periodo']] = $row['nota'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Calificaciones por Periodo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7fafd; font-family: 'Montserrat', Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;}
        .sidebar {
            position: fixed; left: 0; top: 0; width: 220px; height: 100vh;
            background: linear-gradient(135deg, #12b0fa 60%, #2980d9 100%);
            color: #fff; box-shadow: 0 8px 32px 0 rgba(31,38,135,.13), 0 1.5px 6px 0 rgba(44,62,80,.10);
            display: flex; flex-direction: column; z-index: 2;
        }
        .sidebar .logo { text-align: center; padding: 32px 0 18px 0; }
        .sidebar .logo img { width: 70px; margin-bottom: 8px; }
        .sidebar nav { flex: 1; }
        .sidebar nav a {
            display: block; color: #fff; text-decoration: none;
            padding: 14px 32px; font-size: 16px; font-weight: 500;
            border-left: 4px solid transparent;
            transition: background 0.2s, border-color 0.2s;
        }
        .sidebar nav a.active, .sidebar nav a:hover {
            background: rgba(255,255,255,0.08); border-left: 4px solid #fff;
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
        .container-calificaciones {
            max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px 0 rgba(31,38,135,.13), 0 1.5px 6px 0 rgba(44,62,80,.10); padding: 32px;
        }
        h2 { color: #2980d9; }
        form { margin-bottom: 30px; }
        label { font-weight: 600; color: #333;}
        select, input[type="number"] {
            width: 100%; margin-top: 8px; margin-bottom: 10px; padding: 8px; border-radius: 5px; border: 1px solid #ccc; background: #f0f6ff;
        }
        button {
            background: #12b0fa; color: #fff; border: none;
            border-radius: 6px; padding: 10px 28px; font-size: 16px; font-weight: 700; cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #2980d9; }
        table { width: 100%; margin-top: 20px; background: #f0f6ff; border-radius: 8px; border-collapse: collapse;}
        th, td { padding: 8px 10px; border-bottom: 1px solid #e6e6e6;}
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
        .filtros {display: flex; gap: 18px; margin-bottom: 24px; flex-wrap: wrap;}
        .filtros > div { min-width: 180px; flex: 1;}
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
                <a href="asistencias.php">Asistencias</a>
                <a href="calificaciones.php" class="active">Calificaciones</a>
                <a href="asignar_grado.php">Asignar Grados</a>
                <a href="usuarios/usuarios.php">Usuarios</a>
            <?php else: ?>
                <a href="asistencias.php">Asistencias</a>
                <a href="calificaciones.php" class="active">Calificaciones</a>
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
        <div class="container-calificaciones">
            <h2>Registro de Calificaciones por Periodo</h2>
            <form method="GET" class="filtros">
                <div>
                    <label for="id_grado">Grado:</label>
                    <select name="id_grado" id="id_grado" required>
                        <option value="">Seleccione grado</option>
                        <?php foreach($grados as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= $id_grado == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
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
                    <label for="id_materia">Materia:</label>
                    <select name="id_materia" id="id_materia" required onchange="this.form.submit()">
                        <option value="">Seleccione la materia</option>
                        <?php foreach($materias as $mat): ?>
                            <option value="<?= $mat['id'] ?>" <?= $id_materia == $mat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit">Filtrar</button>
                </div>
            </form>
            <?php if ($id_grado && $id_seccion): ?>
            <div style="margin-bottom: 18px;">
                <strong>Grado:</strong> <?= htmlspecialchars($nombre_grado) ?> &nbsp;
                <strong>Sección:</strong> <?= htmlspecialchars($nombre_seccion) ?>
            </div>
            <?php endif; ?>
            <?php if ($mensaje): ?>
                <div class="alert-success"><?= $mensaje ?></div>
            <?php endif; ?>

            <?php if ($id_grado && $id_seccion && $id_materia && $estudiantes): ?>
            <form method="POST">
                <input type="hidden" name="id_grado" value="<?= htmlspecialchars($id_grado) ?>">
                <input type="hidden" name="id_seccion" value="<?= htmlspecialchars($id_seccion) ?>">
                <input type="hidden" name="id_materia" value="<?= htmlspecialchars($id_materia) ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <?php foreach($periodos as $p): ?>
                                <th><?= htmlspecialchars($p) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($estudiantes as $est): ?>
                        <tr>
                            <td><?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?></td>
                            <?php foreach($periodos as $p): ?>
                                <td>
                                    <input type="number" name="nota[<?= $est['cod_estudiante'] ?>][<?= $p ?>]" min="0" max="20" step="0.1"
                                    value="<?= isset($calificaciones_guardadas[$est['cod_estudiante']][$p]) ? htmlspecialchars($calificaciones_guardadas[$est['cod_estudiante']][$p]) : '' ?>">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="guardar_calificaciones" style="margin-top:18px;">Guardar Calificaciones</button>
            </form>
            <?php elseif (($id_grado && $id_seccion && $id_materia) && !$estudiantes): ?>
                <div class="alert-info">No hay estudiantes en este grado y sección.</div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function salir() { window.location.href = "login.php"; }
        // Filtrar secciones por grado
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
    </script>
</body>
</html>