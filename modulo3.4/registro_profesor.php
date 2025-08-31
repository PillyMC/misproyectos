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
    $nombre_completo = trim($_POST['nombre'] ?? '');
    $correo = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $numero_identificacion = trim($_POST['identificacion'] ?? '');
    $materia = trim($_POST['materia'] ?? '');
    $nivel_academico = trim($_POST['nivel_academico'] ?? '');
    $anios_experiencia = isset($_POST['experiencia']) ? (int)$_POST['experiencia'] : null;
    $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');
    $estado_laboral = trim($_POST['estado'] ?? '');

    // Validar que los campos obligatorios estén completos
    if (
        !empty($nombre_completo) && $correo && !empty($telefono) && !empty($fecha_nacimiento) &&
        !empty($numero_identificacion) && !empty($materia) && !empty($nivel_academico) &&
        $anios_experiencia !== null && !empty($fecha_ingreso) && !empty($estado_laboral)
    ) {
        // Consulta SQL usando parámetros para prevenir inyecciones
        $query = "INSERT INTO profesores 
                  (nombre_completo, correo, telefono, direccion, fecha_nacimiento, genero, numero_identificacion, materia, nivel_academico, anios_experiencia, fecha_ingreso, estado_laboral) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)";

        $result = pg_query_params($conexion, $query, array(
            $nombre_completo, $correo, $telefono, $direccion, $fecha_nacimiento, $genero,
            $numero_identificacion, $materia, $nivel_academico, $anios_experiencia,
            $fecha_ingreso, $estado_laboral
        ));

        if ($result) {
            echo "<script>
                alert('Profesor registrado exitosamente.');
                window.location.href = 'dashboard.php';
            </script>";
        } else {
            echo "<script>
                alert('Error al registrar: " . pg_last_error($conexion) . "');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('Por favor, completa todos los campos obligatorios con un formato válido.');
            window.history.back();
        </script>";
    }
}
?>
