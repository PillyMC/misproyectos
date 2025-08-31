<?php
session_start();
include_once("conexion.php");

if (!isset($_POST['usuario']) || !isset($_POST['clave'])) {
    header("Location: login.php?error=Faltan+datos");
    exit();
}

$usuario = $_POST['usuario'];
$clave = $_POST['clave'];

// Buscar el usuario por nombre o correo
$sql = "SELECT * FROM usuarios WHERE (nombre = $1 OR correo = $1) LIMIT 1";
$resultado = pg_query_params($conexion, $sql, array($usuario));

if ($resultado && pg_num_rows($resultado) === 1) {
    $datosUsuario = pg_fetch_assoc($resultado);
    // Verificar la clave
    if (password_verify($clave, $datosUsuario['clave'])) {
        $_SESSION['usuario'] = $datosUsuario['nombre'];
        $_SESSION['rol'] = $datosUsuario['rol'];
        $_SESSION['usuario_id'] = $datosUsuario['id'];
        $_SESSION['correo'] = $datosUsuario['correo'];

        // Si es docente, obtener grado y sección asignados desde profesores
        if ($datosUsuario['rol'] === 'Docente') {
            // Busca grado_asignado y seccion_asignada en la tabla profesores
            $sql_prof = "SELECT grado_asignado, seccion_asignada FROM profesores WHERE correo = $1 LIMIT 1";
            $res_prof = pg_query_params($conexion, $sql_prof, array($datosUsuario['correo']));
            if ($res_prof && pg_num_rows($res_prof) === 1) {
                $datosProf = pg_fetch_assoc($res_prof);
                $_SESSION['grado'] = $datosProf['grado_asignado'];
                $_SESSION['seccion'] = $datosProf['seccion_asignada'];
            } else {
                $_SESSION['grado'] = '';
                $_SESSION['seccion'] = '';
            }
        }

        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=Datos+incorrectos");
        exit();
    }
} else {
    header("Location: login.php?error=Datos+incorrectos");
    exit();
}
?>