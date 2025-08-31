<?php
include_once("conexion.php");

// Obtener lista de profesores
$query_profesores = "SELECT id, nombre_completo FROM profesores ORDER BY nombre_completo ASC";
$result_profesores = pg_query($conexion, $query_profesores);
$profesores = pg_fetch_all($result_profesores) ?: [];

// Obtener grados y secciones
$query_grados = "SELECT id, nombre FROM grados ORDER BY nombre ASC";
$result_grados = pg_query($conexion, $query_grados);
$grados = pg_fetch_all($result_grados) ?: [];

$query_secciones = "SELECT id, nombre, grado_id FROM secciones ORDER BY grado_id, nombre ASC";
$result_secciones = pg_query($conexion, $query_secciones);
$secciones = pg_fetch_all($result_secciones) ?: [];

// Asignar grado y sección a profesor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asignar_grado'])) {
    $profesor_id = $_POST['profesor_id'];
    $grado_id = $_POST['grado_id'];
    $seccion_id = $_POST['seccion_id'];
    $query = "UPDATE profesores SET grado_asignado = $1, seccion_asignada = $2 WHERE id = $3";
    $result = pg_query_params($conexion, $query, array($grado_id, $seccion_id, $profesor_id));
    if ($result) {
        $mensaje = "Grado y sección asignados correctamente al profesor.";
    } else {
        $error = "Error al asignar grado y sección: " . pg_last_error($conexion);
    }
}

// Mostrar listado de profesores con su grado y sección asignados
$query_asignados = "SELECT p.id, p.nombre_completo, g.nombre AS grado, s.nombre AS seccion 
    FROM profesores p
    LEFT JOIN grados g ON p.grado_asignado = g.id
    LEFT JOIN secciones s ON p.seccion_asignada = s.id
    ORDER BY p.nombre_completo ASC";
$result_asignados = pg_query($conexion, $query_asignados);
$asignados = pg_fetch_all($result_asignados) ?: [];

// Mostrar estadística de grados
$total_grados = count($grados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar grado y sección a profesor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');
        :root {
            --azul-primario: #2980d9;
            --azul-secundario: #12b0fa;
            --azul-claro: #f0f6ff;
            --gris-fondo: #f7fafd;
            --blanco: #fff;
            --gris: #e6e6e6;
            --sombra: 0 8px 32px 0 rgba(31, 38, 135, 0.13), 0 1.5px 6px 0 rgba(44, 62, 80, 0.10);
        }
        body {
            background: var(--gris-fondo);
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100vh;
            background: linear-gradient(135deg, var(--azul-secundario) 60%, var(--azul-primario) 100%);
            color: var(--blanco);
            box-shadow: var(--sombra);
            display: flex;
            flex-direction: column;
            z-index: 2;
        }
        .sidebar .logo {
            text-align: center;
            padding: 32px 0 18px 0;
        }
        .sidebar .logo img {
            width: 70px;
            margin-bottom: 8px;
        }
        .sidebar nav {
            flex: 1;
        }
        .sidebar nav a {
            display: block;
            color: var(--blanco);
            text-decoration: none;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: background 0.2s, border-color 0.2s;
        }
        .sidebar nav a.active,
        .sidebar nav a:hover {
            background: rgba(255,255,255,0.08);
            border-left: 4px solid var(--blanco);
        }
        .sidebar .user {
            padding: 18px 32px;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 15px;
            color: #e0f2ff;
        }
        .sidebar .btn-salir {
            width: 100%;
            margin-top: 18px;
            background: #ff6b6b;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .sidebar .btn-salir:hover {
            background: #d63031;
        }
        .main-content {
            margin-left: 220px;
            padding: 36px 40px 24px 40px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .header h1 {
            color: var(--azul-primario);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .stats {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
        }
        .stat-card {
            flex: 1;
            background: var(--blanco);
            border-radius: 12px;
            box-shadow: var(--sombra);
            padding: 24px 18px;
            text-align: center;
        }
        .stat-card h2 {
            color: var(--azul-primario);
            font-size: 32px;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        .stat-card p {
            color: #555;
            font-size: 15px;
            margin: 0;
        }
        .form-modal {
            background: var(--blanco);
            border-radius: 14px;
            box-shadow: var(--sombra);
            padding: 32px 28px 24px 28px;
            min-width: 320px;
            max-width: 600px;
            width: 100%;
            position: relative;
            margin-bottom: 32px;
        }
        .form-modal label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-modal select, .form-modal input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 16px;
            border: 1px solid #e6e6e6;
            border-radius: 5px;
            background: #f0f6ff;
            font-size: 15px;
            color: #333;
            outline: none;
        }
        .form-modal .btn-guardar {
            background: var(--azul-secundario);
            color: var(--blanco);
            border: none;
            border-radius: 5px;
            padding: 10px 24px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .form-modal .btn-guardar:hover {
            background: var(--azul-primario);
        }
        .alert-success {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            animation: fadeInOut 3s ease-in-out;
        }
        .alert-error {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f44336;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            animation: fadeInOut 3s ease-in-out;
        }
        .table-container {
            background: var(--blanco);
            border-radius: 12px;
            box-shadow: var(--sombra);
            padding: 24px;
            margin-bottom: 32px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background: var(--azul-claro);
            color: var(--azul-primario);
            font-weight: 700;
        }
        tr:nth-child(even) {
            background: #f7fafd;
        }
        tr:hover {
            background: #e6f0ff;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        @media (max-width: 900px) {
            .main-content {
                padding: 24px 8vw 24px 8vw;
            }
            .stats {
                flex-direction: column;
                gap: 16px;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
                padding: 18px 2vw 18px 2vw;
            }
            .form-modal {
                min-width: unset;
                width: 95%;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($mensaje)): ?>
        <div class="alert-success"><?= $mensaje ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <aside class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <nav>
            <a href="dashboard.php">Gestionar Estudiantes</a>
            <a href="gestionar_profesores.php">Gestionar profesores</a>
            <a href="grados_secciones.php">Gestionar grados y secciones</a>
            <a href="asistencias.php">Asistencias</a>
            <a href="calificaciones.php">Calificaciones</a>
            <a href="asignar_grado.php" class="active">Asignar grado</a>
            <a href="usuarios/usuarios.php">Usuarios</a>
            
        </nav>
        <div class="user">
            <strong>Admin</strong><br>
            admin@nclases.com
            <br><br>
            <button class="btn-salir" onclick="window.location.href='login.php'">Salir</button>
        </div>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Asignar grado y sección a profesor</h1>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h2><?= $total_grados ?></h2>
                <p>Total de Grados</p>
            </div>
            <?php foreach ($grados as $grado): ?>
            <div class="stat-card">
                <h2><?= htmlspecialchars($grado['nombre']) ?></h2>
                <p>Grado</p>
            </div>
            <?php endforeach; ?>
        </div>

        <form class="form-modal" method="POST">
            <label for="profesor_id">Profesor</label>
            <select name="profesor_id" id="profesor_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($profesores as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['nombre_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="grado_id">Grado</label>
            <select name="grado_id" id="grado_id" required onchange="filtrarSecciones()">
                <option value="">Seleccione...</option>
                <?php foreach ($grados as $g): ?>
                    <option value="<?= $g['id'] ?>">
                        <?= htmlspecialchars($g['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="seccion_id">Sección</label>
            <select name="seccion_id" id="seccion_id" required>
                <option value="">Seleccione grado primero...</option>
                <?php
                foreach ($secciones as $s) {
                    echo '<option value="'.$s['id'].'" data-grado="'.$s['grado_id'].'" style="display:none;">'.htmlspecialchars($s['nombre']).'</option>';
                }
                ?>
            </select>

            <button type="submit" class="btn-guardar" name="asignar_grado">Asignar grado y sección</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Profesor</th>
                        <th>Grado asignado</th>
                        <th>Sección asignada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($asignados as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($row['grado']) ?: '<em>No asignado</em>' ?></td>
                            <td><?= htmlspecialchars($row['seccion']) ?: '<em>No asignada</em>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($asignados) === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align:center;">No hay profesores registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        function filtrarSecciones() {
            var grado = document.getElementById('grado_id').value;
            var seccionSelect = document.getElementById('seccion_id');
            var options = seccionSelect.options;
            for (var i = 0; i < options.length; i++) {
                var opt = options[i];
                if (opt.getAttribute('data-grado')) {
                    opt.style.display = (opt.getAttribute('data-grado') === grado) ? '' : 'none';
                }
            }
            seccionSelect.value = '';
        }
    </script>
</body>
</html>