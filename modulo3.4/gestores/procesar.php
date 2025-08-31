<?php
require_once __DIR__ . '/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_POST['accion'])) {
    $_SESSION['mensaje'] = "Acción no especificada";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: gestores.php");
    exit();
}

$accion = $_POST['accion'];



    if ($accion === 'crear') {
        $camposRequeridos = ['nombres', 'apellidos', 'correo', 'documento', 'distrito', 'departamento', 'clave', 'rol'];
        foreach ($camposRequeridos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo $campo es requerido");
            }
        }

        $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);

        $query = "INSERT INTO gestores (nombre, apellido, correo, dui, rol, clave) 
                 VALUES ($1, $2, $3, $4, $5, $6)";
        $params = [
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['correo'],
            $_POST['dui'],
            $_POST['rol'],
            $clave,
           
       
        ];

        ejecutarConsulta($query, $params);

        $_SESSION['mensaje'] = "Gestor creado exitosamente";
        $_SESSION['mensaje_tipo'] = "success";

    } elseif ($accion === 'editar') {
        if (empty($_POST['cod_gestor'])) {
            throw new Exception("ID de gestor no especificado");
        }

        $camposRequeridos = ['nombre', 'apellido', 'correo', 'dui', 'distrito', 'departamento', 'rol'];
        foreach ($camposRequeridos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo $campo es requerido");
            }
        }

        // Si hay nueva foto, la actualiza; si no, mantiene la anterior
        if ($foto_perfil) {
            if (!empty($_POST['clave'])) {
                $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
                $query = "UPDATE gestores SET 
                          nombres = $1, 
                          apellidos = $2, 
                          correo = $3, 
                          documento = $4, 
                          distrito_residencia = $5, 
                          departamento_trabajo = $6, 
                          rol = $7, 
                          clave = $8,
                          foto_perfil = $9
                          WHERE cod_gestor = $10";
                $params = [
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['correo'],
                    $_POST['documento'],
                    $_POST['distrito'],
                    $_POST['departamento'],
                    $_POST['rol'],
                    $clave,
                    $foto_perfil,
                    $_POST['cod_gestor']
                ];
            } else {
                $query = "UPDATE gestores SET 
                          nombres = $1, 
                          apellidos = $2, 
                          correo = $3, 
                          documento = $4, 
                          distrito_residencia = $5, 
                          departamento_trabajo = $6, 
                          rol = $7, 
                          foto_perfil = $8
                          WHERE cod_gestor = $9";
                $params = [
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['correo'],
                    $_POST['documento'],
                    $_POST['distrito'],
                    $_POST['departamento'],
                    $_POST['rol'],
                    $foto_perfil,
                    $_POST['cod_gestor']
                ];
            }
        } else {
            if (!empty($_POST['clave'])) {
                $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
                $query = "UPDATE gestores SET 
                          nombres = $1, 
                          apellidos = $2, 
                          correo = $3, 
                          documento = $4, 
                          distrito_residencia = $5, 
                          departamento_trabajo = $6, 
                          rol = $7, 
                          clave = $8
                          WHERE cod_gestor = $9";
                $params = [
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['correo'],
                    $_POST['documento'],
                    $_POST['distrito'],
                    $_POST['departamento'],
                    $_POST['rol'],
                    $clave,
                    $_POST['cod_gestor']
                ];
            } else {
                $query = "UPDATE gestores SET 
                          nombres = $1, 
                          apellidos = $2, 
                          correo = $3, 
                          documento = $4, 
                          distrito_residencia = $5, 
                          departamento_trabajo = $6, 
                          rol = $7
                          WHERE cod_gestor = $8";
                $params = [
                    $_POST['nombres'],
                    $_POST['apellidos'],
                    $_POST['correo'],
                    $_POST['documento'],
                    $_POST['distrito'],
                    $_POST['departamento'],
                    $_POST['rol'],
                    $_POST['cod_gestor']
                ];
            }
        }