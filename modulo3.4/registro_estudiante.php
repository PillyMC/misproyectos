<?php
// Incluir conexión a la base de datos
include_once("conexion.php");

// Verificar que la conexión exista
if (!$conexion) {
    die("Error de conexión: " . pg_last_error());
}

// Procesar solo si el método es POST (viene del formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recoger y limpiar datos
    $nombre = trim($_POST['nombres'] ?? '');
    $apellido = trim($_POST['apellidos'] ?? '');
    $correo = filter_var($_POST['correo'] ?? '', FILTER_VALIDATE_EMAIL);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $sangre = trim($_POST['sangre'] ?? '');

    // Validar que los campos obligatorios estén completos
    if ($nombre && $apellido && $correo && $fecha_nacimiento && $telefono && $genero) {
        
        // Consulta SQL usando parámetros para prevenir inyecciones
        $query = "INSERT INTO estudiantes 
                  (nombres, apellidos, correo, fecha_nacimiento, telefono, genero, direccion, sangre) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";

        $result = pg_query_params($conexion, $query, array(
            $nombre, $apellido, $correo, $fecha_nacimiento, $telefono, $genero, $direccion, $sangre
        ));

        // Si se insertó correctamente
        if ($result) {
            echo "<script>
                alert(' Estudiante registrado exitosamente.');
                window.location.href = 'dashboard.php'; // Cambia a la página que desees
            </script>";
        } else {
            echo "<script>
                alert(' Error al registrar: " . pg_last_error($conexion) . "');
                window.history.back();
            </script>";
        }
    } else {
        // Si falta algún dato obligatorio
        echo "<script>
            alert('⚠ Por favor, completa todos los campos obligatorios.');
            window.history.back();
        </script>";
    }
}
?>
