<?php
include_once("conexion.php");
if (!$conexion) {
    die("Error de conexión: " . pg_last_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $nombre = $_POST['nombre_completo'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $genero = $_POST['genero'] ?? null;
    $numero_identificacion = $_POST['numero_identificacion'] ?? '';
    $materia = $_POST['materia'] ?? '';
    $nivel_academico = $_POST['nivel_academico'] ?? '';
    $anios_experiencia = isset($_POST['anios_experiencia']) ? (int)$_POST['anios_experiencia'] : 0;
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    $estado_laboral = $_POST['estado_laboral'] ?? '';

    // Validación básica
    if ($nombre && $correo && $telefono && $fecha_nacimiento && $numero_identificacion && $materia && $nivel_academico && $fecha_ingreso && $estado_laboral) {
        $query = "INSERT INTO profesores (
                    nombre_completo, correo, telefono, direccion, fecha_nacimiento, genero,
                    numero_identificacion, materia, nivel_academico, anios_experiencia,
                    fecha_ingreso, estado_laboral
                  ) VALUES (
                    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12
                  )";

        $result = pg_query_params($conexion, $query, array(
            $nombre, $correo, $telefono, $direccion, $fecha_nacimiento, $genero,
            $numero_identificacion, $materia, $nivel_academico, $anios_experiencia,
            $fecha_ingreso, $estado_laboral
        ));

        if ($result) {
            echo "<p style='color: green;'>Profesor registrado exitosamente.</p>";
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
    <title>Registro de Profesor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        form { max-width: 600px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input, select, button { width: 100%; padding: 8px; margin-top: 5px; }
        button { background-color: #2980d9; color: white; border: none; margin-top: 20px; cursor: pointer; }
        button:hover { background-color: #1c6395; }
    </style>
</head>
<body>
    <h2>Formulario de Registro de Profesor</h2>
    <form method="POST" action="registro_profesor.php">
        <label for="nombre_completo">Nombre completo *</label>
        <input type="text" name="nombre_completo" required>

        <label for="correo">Correo electrónico *</label>
        <input type="email" name="correo" required>

        <label for="telefono">Teléfono *</label>
        <input type="text" name="telefono" required>

        <label for="direccion">Dirección</label>
        <input type="text" name="direccion">

        <label for="fecha_nacimiento">Fecha de nacimiento *</label>
        <input type="date" name="fecha_nacimiento" required>

        <label for="genero">Género</label>
        <select name="genero">
            <option value="">Seleccione</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
        </select>

        <label for="numero_identificacion">Número de identificación *</label>
        <input type="text" name="numero_identificacion" required>

        <label for="materia">Materia *</label>
        <input type="text" name="materia" required>

        <label for="nivel_academico">Nivel académico *</label>
        <input type="text" name="nivel_academico" required>

        <label for="anios_experiencia">Años de experiencia *</label>
        <input type="number" name="anios_experiencia" min="0" value="0" required>

        <label for="fecha_ingreso">Fecha de ingreso *</label>
        <input type="date" name="fecha_ingreso" required>

        <label for="estado_laboral">Estado laboral *</label>
        <select name="estado_laboral" required>
            <option value="">Seleccione</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>

        <button type="submit" name="guardar_profesor">Registrar Profesor</button>
    </form>
</body>
</html>
