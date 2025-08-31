<?php
include_once("conexion.php");

// Guardar nuevo grado
if (isset($_POST['guardar_grado'])) {
    $nombre = trim($_POST['nombre_grado']);
    if ($nombre != "") {
        pg_query_params($conexion, "INSERT INTO grados (nombre) VALUES ($1)", [$nombre]);
    }
}

// Guardar nueva sección
if (isset($_POST['guardar_seccion'])) {
    $nombre = trim($_POST['nombre_seccion']);
    $id_grado = $_POST['id_grado'];
    if ($nombre != "" && $id_grado != "") {
      pg_query_params($conexion, "INSERT INTO secciones (nombre, grado_id,id_grado) VALUES ($1, $2, $3)", [$nombre, $id_grado, $id_grado]);
    }
}

// Listar grados y secciones
$grados = pg_fetch_all(pg_query($conexion, "SELECT * FROM grados ORDER BY nombre")) ?: [];
$secciones = pg_fetch_all(pg_query($conexion, "SELECT s.*, g.nombre as grado FROM secciones s JOIN grados g ON s.grado_id=g.id ORDER BY g.nombre, s.nombre")) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Grados y Secciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts Montserrat -->
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
            position: fixed;
            left: 0; top: 0; width: 220px; height: 100vh;
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
        .container {
            max-width: 700px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: var(--sombra); padding: 32px;
        }
        h2 { color: #2980d9; }
        form { margin-bottom: 30px; }
        label { font-weight: 600; color: #333;}
        input, select { width: 100%; margin-top: 8px; margin-bottom: 10px; padding: 8px; border-radius: 5px; border: 1px solid #ccc; background: #f0f6ff;}
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
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <nav>
            <a href="dashboard.php">Gestionar Estudiantes</a>
            <a href="gestionar_profesores.php">Gestionar profesores</a>
            <a href="grados_secciones.php" class="active">Gestionar grados y secciones</a>
            <a href="asistencias.php">Asistencias</a>
          
            <a href="calificaciones.php">Calificaciones</a>
          <a href="asignar_grado.php">Asignar Grados</a>
            <a href="usuarios/usuarios.php">Usuarios</a>
        </nav>
        <div class="user">
            <strong>Admin</strong><br>
            admin@nclases.com
            <br><br>
            <button class="btn-salir" onclick="salir()">Salir</button>
        </div>
    </aside>
    <main class="main-content">
        <div class="container">
            <h2>Agregar Nuevo Grado</h2>
            <form method="POST">
                <label for="nombre_grado">Nombre del Grado:</label>
                <input type="text" name="nombre_grado" required>
                <button type="submit" name="guardar_grado">Guardar Grado</button>
            </form>
            
            <h2>Agregar Nueva Sección</h2>
            <form method="POST">
                <label for="id_grado">Grado:</label>
                <select name="id_grado" required>
                    <option value="">Seleccione un grado</option>
                    <?php foreach($grados as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="nombre_seccion">Nombre de la Sección:</label>
                <input type="text" name="nombre_seccion" required>
                <button type="submit" name="guardar_seccion">Guardar Sección</button>
            </form>

            <h2>Listado de Grados</h2>
            <ul>
                <?php foreach($grados as $g): ?>
                    <li><?= htmlspecialchars($g['nombre']) ?></li>
                <?php endforeach; ?>
            </ul>

            <h2>Listado de Secciones</h2>
            <table>
                <tr><th>Grado</th><th>Sección</th></tr>
                <?php foreach($secciones as $s): ?>
                    <tr><td><?= htmlspecialchars($s['grado']) ?></td><td><?= htmlspecialchars($s['nombre']) ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>
    <script>
        function salir() {
            window.location.href = "login.php";
        }
    </script>
</body>
</html>