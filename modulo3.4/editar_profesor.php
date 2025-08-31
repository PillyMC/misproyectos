<?php
include_once("conexion.php");

$id = $_GET['id'] ?? null;
$profesor = null;

// Si recibimos un ID por la URL, cargamos los datos del profesor
if ($id) {
    $query = "SELECT * FROM profesores WHERE \"id\" = $1";
    $result = pg_query_params($conexion, $query, array($id));
    $profesor = pg_fetch_assoc($result);
}

// Si se envía el formulario para actualizar
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_profesor'])) {
    $id = $_POST['id'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $genero = trim($_POST['genero']);
    $numero_identificacion = trim($_POST['identificacion']);
    $materia = trim($_POST['materia']);
    $nivel_academico = trim($_POST['nivel_academico']);
    $anios_experiencia = (int)$_POST['experiencia'];
    $fecha_ingreso = trim($_POST['fecha_ingreso']);
    $estado_laboral = trim($_POST['estado']);

    $query = "UPDATE profesores SET 
                nombre_completo = $1,
                correo = $2,
                telefono = $3,
                direccion = $4,
                fecha_nacimiento = $5,
                genero = $6,
                numero_identificacion = $7,
                materia = $8,
                nivel_academico = $9,
                anios_experiencia = $10,
                fecha_ingreso = $11,
                estado_laboral = $12
              WHERE \"id\" = $13";

    $result = pg_query_params($conexion, $query, array(
        $nombre_completo, $correo, $telefono, $direccion, $fecha_nacimiento, $genero,
        $numero_identificacion, $materia, $nivel_academico, $anios_experiencia,
        $fecha_ingreso, $estado_laboral, $id
    ));

    if ($result) {
        header("Location: gestionar_profesores.php?success=1&mensaje=Profesor actualizado correctamente");
        exit();
    } else {
        $error = "Error al actualizar: " . pg_last_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Profesor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');
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
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100vh;
            background: linear-gradient(135deg, var(--azul-secundario) 60%, var(--azul-primario) 100%);
            color: var(--blanco);
            box-shadow: var(--sombra);
            display: flex;
            flex-direction: column;
            z-index: 2;
        }
        .sidebar .logo {
            text-align: center;
            padding: 32px 0 18px 0;
        }
        .sidebar .logo img {
            width: 70px;
            margin-bottom: 8px;
        }
        .sidebar nav {
            flex: 1;
        }
        .sidebar nav a {
            display: block;
            color: var(--blanco);
            text-decoration: none;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: background 0.2s, border-color 0.2s;
        }
        .sidebar nav a.active,
        .sidebar nav a:hover {
            background: rgba(255,255,255,0.08);
            border-left: 4px solid var(--blanco);
        }
        .sidebar .user {
            padding: 18px 32px;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 15px;
            color: #e0f2ff;
        }
        .sidebar .btn-salir {
            width: 100%;
            margin-top: 18px;
            background: #ff6b6b;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .sidebar .btn-salir:hover {
            background: #d63031;
        }
        .main-content {
            margin-left: 220px;
            padding: 36px 40px 24px 40px;
        }
        .header {
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 {
            color: var(--azul-primario);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .form-modal-bg {
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            position: static;
        }
        .form-modal {
            background: var(--blanco);
            border-radius: 14px;
            box-shadow: var(--sombra);
            padding: 32px 28px 24px 28px;
            min-width: 320px;
            max-width: 600px;
            width: 100%;
            position: relative;
        }
        .form-modal h2 {
            color: var(--azul-primario);
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 22px;
            font-weight: 700;
        }
        .form-modal label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }
        .form-modal input, .form-modal select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 14px;
            border: 1px solid #e6e6e6;
            border-radius: 5px;
            background: #f0f6ff;
            font-size: 15px;
            color: #333;
            outline: none;
        }
        .form-modal .modal-actions {
            text-align: right;
        }
        .form-modal .btn-cerrar {
            position: absolute;
            top: 12px;
            right: 18px;
            background: none;
            border: none;
            font-size: 22px;
            color: #aaa;
            cursor: pointer;
        }
        .form-modal .btn-cerrar:hover {
            color: #ff6b6b;
        }
        .form-modal .btn-guardar {
            background: var(--azul-secundario);
            color: var(--blanco);
            border: none;
            border-radius: 5px;
            padding: 10px 24px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .form-modal .btn-guardar:hover {
            background: var(--azul-primario);
        }
        .alert-error {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f44336;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            animation: fadeInOut 3s ease-in-out;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        @media (max-width: 900px) {
            .main-content {
                padding: 24px 8vw 24px 8vw;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
                padding: 18px 2vw 18px 2vw;
            }
            .form-modal {
                min-width: unset;
                width: 95%;
                padding: 15px;
            }
            .form-modal > div {
                grid-template-columns: 1fr !important;
            }
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
            <a href="gestionar_profesores.php" class="active">Gestionar profesores</a>
            <a href="grados_secciones.php">Gestionar grados y secciones</a>
            <a href="asistencias.php">Asistencias</a>
           <a href="asignar_grado.php">Asignar grados</a>
            <a href="calificaciones.php">Calificaciones</a>
         
            <a href="usuarios/usuarios.php">Usuarios</a>
        </nav>
        <div class="user">
            <strong>Admin</strong><br>
            admin@nclases.com
            <br><br>
            <button class="btn-salir" onclick="window.location.href='login.php'">Salir</button>
        </div>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Editar Profesor</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-modal-bg">
        <form class="form-modal" method="POST" style="box-shadow: none;">
            <h2>Editar Profesor</h2>
            <input type="hidden" name="id" value="<?= $profesor['id'] ?>">
            <input type="hidden" name="actualizar_profesor" value="1">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="nombre_completo">Nombre completo*</label>
                    <input type="text" name="nombre_completo" id="nombre_completo" value="<?= htmlspecialchars($profesor['nombre_completo'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="correo">Correo*</label>
                    <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($profesor['correo'] ?? '') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="telefono">Teléfono*</label>
                    <input type="tel" name="telefono" id="telefono" value="<?= htmlspecialchars($profesor['telefono'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="direccion">Dirección</label>
                    <input type="text" name="direccion" id="direccion" value="<?= htmlspecialchars($profesor['direccion'] ?? '') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="fecha_nacimiento">Fecha de nacimiento*</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= htmlspecialchars($profesor['fecha_nacimiento'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="genero">Género*</label>
                    <select name="genero" id="genero" required>
                        <option value="">Seleccione...</option>
                        <option value="masculino" <?= (strtolower($profesor['genero'] ?? '') == 'masculino') ? 'selected' : '' ?>>Masculino</option>
                        <option value="femenino" <?= (strtolower($profesor['genero'] ?? '') == 'femenino') ? 'selected' : '' ?>>Femenino</option>
                        <option value="otro" <?= (strtolower($profesor['genero'] ?? '') == 'otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
            </div>

            <label for="identificacion">Número de identificación*</label>
            <input type="text" name="identificacion" id="identificacion" value="<?= htmlspecialchars($profesor['numero_identificacion'] ?? '') ?>" required>

            <label for="materia">Materia*</label>
            <input type="text" name="materia" id="materia" value="<?= htmlspecialchars($profesor['materia'] ?? '') ?>" required>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="nivel_academico">Nivel académico*</label>
                    <select name="nivel_academico" id="nivel_academico" required>
                        <option value="">Seleccione...</option>
                        <option value="licenciatura" <?= (strtolower($profesor['nivel_academico'] ?? '') == 'licenciatura') ? 'selected' : '' ?>>Licenciatura</option>
                        <option value="maestria" <?= (strtolower($profesor['nivel_academico'] ?? '') == 'maestria') ? 'selected' : '' ?>>Maestría</option>
                        <option value="doctorado" <?= (strtolower($profesor['nivel_academico'] ?? '') == 'doctorado') ? 'selected' : '' ?>>Doctorado</option>
                        <option value="otro" <?= (strtolower($profesor['nivel_academico'] ?? '') == 'otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div>
                    <label for="experiencia">Años de experiencia*</label>
                    <input type="number" name="experiencia" id="experiencia" min="0" value="<?= htmlspecialchars($profesor['anios_experiencia'] ?? '') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="fecha_ingreso">Fecha de ingreso*</label>
                    <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="<?= htmlspecialchars($profesor['fecha_ingreso'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="estado">Estado laboral*</label>
                    <select name="estado" id="estado" required>
                        <option value="activo" <?= (strtolower($profesor['estado_laboral'] ?? '') == 'activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= (strtolower($profesor['estado_laboral'] ?? '') == 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions" style="position: sticky; bottom: 0; background: white; padding: 15px 0; border-top: 1px solid var(--gris);">
                <a href="gestionar_profesores.php" style="margin-right: 10px; background: #f0f0f0; color: #333; padding: 10px 24px; border-radius: 5px; text-decoration: none;">Cancelar</a>
                <button type="submit" class="btn-guardar">Guardar Cambios</button>
            </div>
        </form>
        </div>
    </main>
</body>
</html>