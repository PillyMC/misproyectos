<?php
include_once("conexion.php");
if (!$conexion) {
    die("Error de conexión: " . pg_last_error());
}


// Verifica conexión
if (!$conexion) {
    die("Error de conexión: " . pg_last_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoge y limpia los datos del formulario
    $nombre = $_POST['nombres'] ?? '';
    $apellido = $_POST['apellidos'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $direccion = $_POST['direccion'] ?? null;
    $sangre = $_POST['sangre'] ?? null;

    // Validación básica
    if ($nombre && $apellido && $correo && $fecha_nacimiento && $telefono && $genero) {
        $query = "INSERT INTO estudiantes 
                  (nombres, apellidos, correo, fecha_nacimiento, telefono, genero, direccion, sangre) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
                  
        $result = pg_query_params($conexion, $query, array(
            $nombre, $apellido, $correo, $fecha_nacimiento, $telefono, $genero, $direccion, $sangre
        ));

        if ($result) {
            echo "<p style='color: green;'>Estudiante registrado exitosamente.</p>";
        } else {
            echo "<p style='color: red;'>Error al registrar: " . pg_last_error($conexion) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>Por favor, completa todos los campos obligatorios.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Estudiante</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        form { max-width: 500px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input, select, button { width: 100%; padding: 8px; margin-top: 5px; }
        button { background-color: #4CAF50; color: white; border: none; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Formulario de Registro de Estudiante</h2>
    <form method="POST" action="registro_estudiante.php">
        <label for="nombres">Nombres *</label>
        <input type="text" name="nombres" required>

        <label for="apellidos">Apellidos *</label>
        <input type="text" name="apellidos" required>

        <label for="correo">Correo electrónico *</label>
        <input type="email" name="correo" required>

        <label for="fecha_nacimiento">Fecha de nacimiento *</label>
        <input type="date" name="fecha_nacimiento" required>

        <label for="telefono">Teléfono *</label>
        <input type="text" name="telefono" required>

        <label for="genero">Género *</label>
        <select name="genero" required>
            <option value="">Seleccione</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
            <option value="O">Otro</option>
        </select>

        <label for="direccion">Dirección</label>
        <input type="text" name="direccion">

        <label for="sangre">Tipo de sangre</label>
        <input type="text" name="sangre">

        <button type="submit">Registrar</button>
    </form>
</body>
</html>
