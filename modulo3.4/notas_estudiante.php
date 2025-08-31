<?php
session_start();
include_once("conexion.php");

// Solo permitir acceso si el rol es Estudiante
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Estudiante') {
    header("Location: login.php");
    exit;
}

$cod_estudiante = $_SESSION['cod_estudiante'] ?? '';
$usuario = $_SESSION['usuario'] ?? '';
$correo = $_SESSION['correo'] ?? '';

// Consultar datos básicos del estudiante
$estudiante = [];
if ($cod_estudiante) {
    $res = pg_query_params($conexion, "SELECT nombres, apellidos, id_grado, id_seccion FROM estudiantes WHERE cod_estudiante=$1", [$cod_estudiante]);
    if (pg_num_rows($res)) {
        $estudiante = pg_fetch_assoc($res);
    }
}

// Consultar nombre de grado y sección
$nombre_grado = '';
$nombre_seccion = '';
if (!empty($estudiante)) {
    $res_grado = pg_query_params($conexion, "SELECT nombre FROM grados WHERE id=$1", [$estudiante['id_grado']]);
    $nombre_grado = $res_grado && pg_num_rows($res_grado) ? pg_fetch_result($res_grado, 0, 0) : '';
    $res_seccion = pg_query_params($conexion, "SELECT nombre FROM secciones WHERE id=$1", [$estudiante['id_seccion']]);
    $nombre_seccion = $res_seccion && pg_num_rows($res_seccion) ? pg_fetch_result($res_seccion, 0, 0) : '';
}

// Consultar las calificaciones del estudiante
$calificaciones = [];
if ($cod_estudiante) {
    $res_cal = pg_query_params($conexion,
        "SELECT id, materia, nota, periodo FROM calificaciones WHERE cod_estudiante = $1 ORDER BY periodo DESC, materia ASC",
        [$cod_estudiante]
    );
    if ($res_cal && pg_num_rows($res_cal)) {
        $calificaciones = pg_fetch_all($res_cal);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Calificaciones</title>
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
        .container-notas {
            max-width: 700px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: var(--sombra); padding: 32px;
        }
        h2 { color: #2980d9; }
        .info-estudiante { margin-bottom: 18px; font-size: 16px; background: #f0f6ff; border-radius: 8px; padding: 12px; }
        table { width: 100%; margin-top: 20px; background: #f0f6ff; border-radius: 8px; border-collapse: collapse;}
        th, td { padding: 8px 10px; border-bottom: 1px solid #e6e6e6;}
        th { color: #2097e4; text-align: left;}
        .sin-notas { background: #2980d9; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;}
        @media (max-width: 900px) {
            .main-content { padding: 24px 8vw 24px 8vw; }
        }
        @media (max-width: 600px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 18px 2vw 18px 2vw; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <nav>
            <a href="notas_estudiante.php" class="active">Mis Calificaciones</a>
        </nav>
        <div class="user">
            <strong><?= htmlspecialchars($usuario) ?></strong><br>
            <?= htmlspecialchars($correo) ?>
            <br><br>
            <button class="btn-salir" onclick="salir()">Salir</button>
        </div>
    </aside>
    <main class="main-content">
        <div class="container-notas">
            <h2>Mis Calificaciones</h2>
            <?php if (!empty($estudiante)): ?>
            <div class="info-estudiante">
                <strong>Estudiante:</strong> <?= htmlspecialchars($estudiante['apellidos'] . ', ' . $estudiante['nombres']) ?> <br>
                <strong>Grado:</strong> <?= htmlspecialchars($nombre_grado) ?> &nbsp; 
                <strong>Sección:</strong> <?= htmlspecialchars($nombre_seccion) ?>
            </div>
            <?php endif; ?>
            <?php if ($calificaciones): ?>
            <table>
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Nota</th>
                        <th>Periodo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($calificaciones as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['materia']) ?></td>
                        <td><?= htmlspecialchars($n['nota']) ?></td>
                        <td><?= htmlspecialchars($n['periodo']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="sin-notas">No hay calificaciones registradas.</div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function salir() { window.location.href = "login.php"; }
    </script>
</body>
</html>