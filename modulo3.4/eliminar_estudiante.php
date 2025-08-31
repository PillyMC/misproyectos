<?php
include_once("conexion.php");

// Obtener ID del estudiante a eliminar
$id = $_POST['id'] ?? null;

if ($id) {
    // Verificar si existe antes de eliminar
    $query_verificar = "SELECT * FROM estudiantes WHERE \"cod_estudiante\" = $1";
    $result_verificar = pg_query_params($conexion, $query_verificar, array($id));
    
    if (pg_num_rows($result_verificar) > 0) {
        // Eliminar estudiante
        $query_eliminar = "DELETE FROM estudiantes WHERE \"cod_estudiante\" = $1";
        $result_eliminar = pg_query_params($conexion, $query_eliminar, array($id));
        
        if ($result_eliminar) {
            header("Location: dashboard.php?success=1&mensaje=Estudiante eliminado correctamente");
        } else {
            header("Location: dashboard.php?error=1&mensaje=Error al eliminar estudiante");
        }
    } else {
        header("Location: dashboard.php?error=1&mensaje=El estudiante no existe");
    }
} else {
    header("Location: dashboard.php?error=1&mensaje=ID no proporcionado");
}
exit();
?>