<?php
include_once("../conexion.php");
$query = "SELECT * FROM usuarios ORDER BY id ASC";
$result = pg_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios</title>
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
        body {
            background: var(--gris-fondo);
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
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
        .usuarios-container {
            max-width: 950px;
            margin: 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--sombra);
            padding: 32px;
        }
        h1 {
            color: #2980d9;
            text-align: center;
            margin-bottom: 28px;
        }
        .btn-nuevo {
            background: linear-gradient(90deg, #12b0fa 60%, #2980d9 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 22px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 18px;
            float: right;
            margin-left: 10px;
            transition: background 0.2s;
        }
        .btn-nuevo:hover {
            background: linear-gradient(90deg, #2980d9 60%, #12b0fa 100%);
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
            background: #f0f6ff;
            color: #2980d9;
            font-weight: 700;
        }
        tr:nth-child(even) {
            background: #f7fafd;
        }
        tr:hover {
            background: #e6f0ff;
        }
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
            <img src="../logo.png" alt="Logo">
        </div>
        <nav>
            <a href="../dashboard.php">Gestionar Estudiantes</a>
            <a href="../gestionar_profesores.php">Gestionar profesores</a>
            <a href="../grados_secciones.php">Gestionar grados y secciones</a>
            <a href="../asistencias.php">Asistencias</a>
            <a href="../calificaciones.php">Calificaciones</a>
            <a href="../asignar_grado.php">Asignar Grados</a>
            <a href="usuarios.php" class="active">Usuarios</a>
        </nav>
        <div class="user">
            <strong>Admin</strong><br>
            admin@nclases.com
            <br><br>
            <button class="btn-salir" onclick="salir()">Salir</button>
        </div>
    </aside>
    <main class="main-content">
        <div class="usuarios-container">
            <h1>Lista de Usuarios</h1>
            <a href="crear_usuario.php" class="btn-nuevo">Nuevo Usuario</a>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Correo</th>
                        <th>DUI</th>
                        <th>Rol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = pg_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($row['correo']); ?></td>
                        <td><?php echo htmlspecialchars($row['dui']); ?></td>
                        <td><?php echo htmlspecialchars($row['rol']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        function salir() { window.location.href = "../login.php"; }
    </script>
</body>
</html>