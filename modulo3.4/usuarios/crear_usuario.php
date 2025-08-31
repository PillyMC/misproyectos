<?php
include_once("../conexion.php");

$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $dui = $_POST['dui'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // Verificar si el correo ya existe
    $query_check = "SELECT 1 FROM usuarios WHERE correo = $1";
    $result_check = pg_query_params($conexion, $query_check, array($correo));

    if ($result_check && pg_fetch_row($result_check)) {
        $mensaje_error = "Este correo ya está registrado. Por favor utiliza uno diferente.";
    } elseif (!$result_check) {
        $mensaje_error = "Error al consultar la base de datos.";
    } else {
        // Hashear la clave antes de guardar
        $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
        $query = "INSERT INTO usuarios (nombre, apellido, correo, dui, rol, clave) VALUES ($1, $2, $3, $4, $5, $6)";
        $result = pg_query_params($conexion, $query, array($nombre, $apellido, $correo, $dui, $rol, $clave_hash));

        if ($result) {
            header("Location: usuarios.php");
            exit();
        } else {
            $mensaje_error = "Error al crear el usuario.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7fafd; font-family: 'Montserrat', Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .form-container { background: #fff; border-radius: 18px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.13), 0 1.5px 6px 0 rgba(44, 62, 80, 0.10); padding: 38px 32px 32px 32px; min-width: 340px; max-width: 95vw; position: relative; }
        .form-container h1 { color: #2980d9; font-size: 26px; font-weight: 700; margin-bottom: 22px; text-align: center; }
        .form-group { margin-bottom: 18px; }
        .form-container label { display: block; margin-bottom: 6px; color: #333; font-weight: 600; }
        .form-container input, .form-container select { width: 100%; padding: 10px 12px; border: 1px solid #e6e6e6; border-radius: 5px; background: #f0f6ff; font-size: 15px; color: #333; outline: none; margin-bottom: 4px; }
        .form-container input:focus, .form-container select:focus { background: #e6f0ff; border-color: #2980d9; }
        .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 18px; }
        .btn-crear { background: linear-gradient(90deg, #12b0fa 60%, #2980d9 100%); color: #fff; border: none; border-radius: 6px; padding: 12px 28px; font-size: 16px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(18, 176, 250, 0.12); transition: background 0.2s, box-shadow 0.2s; }
        .btn-crear:hover { background: linear-gradient(90deg, #2980d9 60%, #12b0fa 100%); box-shadow: 0 4px 16px rgba(18, 176, 250, 0.18); }
        .btn-cancelar { color: #2980d9; background: none; border: none; font-size: 15px; font-weight: 600; text-decoration: underline; cursor: pointer; padding: 0; margin-left: 12px; }
        .error { background: #ffdddd; border-left: 6px solid #ff6b6b; color: #c0392b; padding: 10px 18px; margin-bottom: 18px; border-radius: 6px; font-size: 15px; }
        @media (max-width: 480px) { .form-container { min-width: 90vw; padding: 24px 8vw 24px 8vw; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Crear Usuario</h1>
        <?php if ($mensaje_error): ?>
            <div class="error"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" name="apellido" id="apellido" required>
            </div>
            <div class="form-group">
                <label for="correo">Correo:</label>
                <input type="email" name="correo" id="correo" required>
            </div>
            <div class="form-group">
                <label for="dui">DUI:</label>
                <input type="text" name="dui" id="dui" required>
            </div>
            <div class="form-group">
                <label for="rol">Rol:</label>
                <select name="rol" id="rol" required>
                    <option value="Administrador">Administrador</option>
                    <option value="Docente">Docente</option>
                    <option value="Estudiante">Estudiante</option>
                </select>
            </div>
            <div class="form-group">
                <label for="clave">Clave:</label>
                <input type="password" name="clave" id="clave" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-crear">Crear Usuario</button>
                <a href="usuarios.php" class="btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>