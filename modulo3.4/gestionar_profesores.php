<?php
include_once("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_profesor'])) {
    // Recoger datos del formulario
    $nombre = $_POST['nombre'];
    $correo = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'] ?? null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'] ?? null;
    $numero_identificacion = $_POST['identificacion'];
    $materia = $_POST['materia'];
    $nivel_academico = $_POST['nivel_academico'];
    $anios_experiencia = (int) $_POST['experiencia'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $estado_laboral = $_POST['estado'];

    // Procesar foto (opcional)
    $foto_binaria = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto_binaria = file_get_contents($_FILES['foto']['tmp_name']);
    }

    // Preparar query con parámetros
    $query = "INSERT INTO profesores (
                nombre_completo, correo, telefono, direccion, fecha_nacimiento, genero,
                numero_identificacion, materia, nivel_academico, anios_experiencia,
                fecha_ingreso, estado_laboral, foto
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13
            )";

    $params = [
        $nombre,
        $correo,
        $telefono,
        $direccion,
        $fecha_nacimiento,
        $genero,
        $numero_identificacion,
        $materia,
        $nivel_academico,
        $anios_experiencia,
        $fecha_ingreso,
        $estado_laboral,
        $foto_binaria
    ];

    $result = pg_query_params($conexion, $query, $params);

    if ($result) {
        $mensaje = "Profesor guardado correctamente.";
    } else {
        $error = "Error al guardar profesor: " . pg_last_error($conexion);
    }
}

/// Obtener lista de profesores
$query_profesores = "SELECT * FROM profesores ORDER BY id ASC";
$result_profesores = pg_query($conexion, $query_profesores);
$profesores = pg_fetch_all($result_profesores) ?: [];

// Estadísticas
$total_profesores = count($profesores);
$total_mujeres = 0;
$total_hombres = 0;
$total_otros = 0;

// Contar correctamente según los valores del select
foreach ($profesores as $prof) {
    $g = strtolower(trim($prof['genero']));
    if ($g == 'femenino' || $g == 'f') $total_mujeres++;
    else if ($g == 'masculino' || $g == 'm') $total_hombres++;
    else $total_otros++;
}

$grupos = [];
$total_grupos = count($grupos);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Profesores</title>
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .header h1 {
            color: var(--azul-primario);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .header .btn {
            background: linear-gradient(90deg, var(--azul-secundario) 60%, var(--azul-primario) 100%);
            color: var(--blanco);
            border: none;
            border-radius: 6px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(18, 176, 250, 0.12);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .header .btn:hover {
            background: linear-gradient(90deg, var(--azul-primario) 60%, var(--azul-secundario) 100%);
            box-shadow: 0 4px 16px rgba(18, 176, 250, 0.18);
        }
        .stats {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
        }
        .stat-card {
            flex: 1;
            background: var(--blanco);
            border-radius: 12px;
            box-shadow: var(--sombra);
            padding: 24px 18px;
            text-align: center;
        }
        .stat-card h2 {
            color: var(--azul-primario);
            font-size: 32px;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        .stat-card p {
            color: #555;
            font-size: 15px;
            margin: 0;
        }
        .table-container {
    background: var(--blanco);
    border-radius: 12px;
    box-shadow: var(--sombra);
    padding: 24px;
    margin-bottom: 32px;
    overflow-x: auto;
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
    background: var(--azul-claro);
    color: var(--azul-primario);
    font-weight: 700;
}
tr:nth-child(even) {
    background: #f7fafd;
}
tr:hover {
    background: #e6f0ff;
}
        
        .acciones button {
            background: var(--azul-secundario);
            color: var(--blanco);
            border: none;
            border-radius: 4px;
            padding: 6px 14px;
            margin-right: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .acciones button.editar {
            background: #2980d9;
        }
        .acciones button.eliminar {
            background: #ff6b6b;
        }
        .acciones button:hover {
            opacity: 0.85;
        }
        .form-modal-bg {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(44,62,80,0.18);
            align-items: center;
            justify-content: center;
        }
        .form-modal-bg.active {
            display: flex;
        }
        .form-modal {
            background: var(--blanco);
            border-radius: 14px;
            box-shadow: var(--sombra);
            padding: 32px 28px 24px 28px;
            min-width: 320px;
            max-width: 95vw;
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
        .alert-success {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            animation: fadeInOut 3s ease-in-out;
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
            .stats {
                flex-direction: column;
                gap: 16px;
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
        }
        .form-modal select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 14px;
            border: 1px solid #e6e6e6;
            border-radius: 5px;
            background: #f0f6ff;
            font-size: 15px;
            color: #333;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }
        .form-modal textarea {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 14px;
            border: 1px solid #e6e6e6;
            border-radius: 5px;
            background: #f0f6ff;
            font-size: 15px;
            color: #333;
            outline: none;
            resize: vertical;
            min-height: 60px;
            font-family: 'Montserrat', Arial, sans-serif;
        }
    </style>
</head>
<body>
    <?php if (isset($mensaje)): ?>
        <div class="alert-success"><?= $mensaje ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <aside class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <nav>
            <a href="dashboard.php">Gestionar Estudiantes</a>
            <a href="gestionar_profesores.php" class="active">Gestionar profesores</a>
            <a href="grados_secciones.php">Gestionar grados y secciones</a>
            <a href="asistencias.php">Asistencias</a>
            <a href="calificaciones.php">Calificaciones</a>
                 <a href="asignar_grado.php">Asignar Grados</a>
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
            <h1>Gestión de profesores</h1>
            <button class="btn" onclick="document.getElementById('modalEstudiante').style.display='flex'">+ Nuevo Profesor</button>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h2><?= $total_profesores ?></h2>
                <p>Total de Profesores</p>
            </div>
            <div class="stat-card">
                <h2><?= $total_mujeres ?></h2>
                <p>Mujeres</p>
            </div>
            <div class="stat-card">
                <h2><?= $total_hombres ?></h2>
                <p>Hombres</p>
            </div>
           
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre completo</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Materia</th>
                        <th>Fecha ingreso</th>
                        <th>Estado laboral</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($profesores)): ?>
                    <?php foreach ($profesores as $prof): ?>
                        <tr>
                            <td><?= htmlspecialchars($prof['id']) ?></td>
                            <td><?= htmlspecialchars($prof['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($prof['correo']) ?></td>
                            <td><?= htmlspecialchars($prof['telefono']) ?></td>
                            <td><?= htmlspecialchars($prof['materia']) ?></td>
                            <td><?= htmlspecialchars($prof['fecha_ingreso']) ?></td>
                            <td><?= htmlspecialchars($prof['estado_laboral']) ?></td>
                            <td class="acciones">
                                <button class="editar" onclick="location.href='editar_profesor.php?id=<?= $prof['id'] ?>'">Editar</button>
                                <form method="POST" action="eliminar_profesor.php" style="display:inline;" onsubmit="return confirm('¿Eliminar este profesor?');">
                                    <input type="hidden" name="id" value="<?= $prof['id'] ?>">
                                    <button type="submit" class="eliminar">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No hay profesores registrados</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

<!-- Modal para agregar/editar profesor -->
<div class="form-modal-bg" id="modalEstudiante" style="display: none;">
    <form class="form-modal" id="formEstudiante" style="max-height: 80vh; overflow-y: auto;" method="POST" action="gestionar_profesores.php" enctype="multipart/form-data">
        <button type="button" class="btn-cerrar" onclick="cerrarModal()">&times;</button>
        <h2 id="modalTitulo">Nuevo Profesor</h2>
        <div style="margin-bottom: 15px;">
            <label for="nombre">Nombre completo*</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="email">Correo electrónico*</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label for="telefono">Número de teléfono*</label>
                <input type="tel" id="telefono" name="telefono" pattern="[0-9]{8,15}" title="Solo números (8 a 15 dígitos)" required>
            </div>
            <div>
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label for="fecha_nacimiento">Fecha de nacimiento*</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            <div>
                <label for="genero">Género</label>
                <select id="genero" name="genero">
                    <option value="">Seleccione...</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="identificacion">Número de identificación*</label>
            <input type="text" id="identificacion" name="identificacion" required>
        </div>
       <div style="margin-bottom: 15px;">
    <label for="materia">Materia o área que enseña*</label>
    <select id="materia" name="materia" required>
        <option value="">Seleccione...</option>
        <option value="Sociales">Sociales</option>
        <option value="Lenguaje">Lenguaje</option>
        <option value="Matematica">Matemática</option>
        <option value="Fisica">Física</option>
        <option value="Ciencias">Ciencias</option>
        <option value="Informatica">Informática</option>
        <option value="Modulo">Módulo</option>
    </select>
</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label for="nivel_academico">Nivel académico*</label>
                <select id="nivel_academico" name="nivel_academico" required>
                    <option value="">Seleccione...</option>
                    <option value="licenciatura">Licenciatura</option>
                    <option value="maestria">Maestría</option>
                    <option value="doctorado">Doctorado</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div>
                <label for="experiencia">Años de experiencia*</label>
                <input type="number" id="experiencia" name="experiencia" min="0" required>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <div>
                <label for="fecha_ingreso">Fecha de ingreso*</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
            </div>
            <div>
                <label for="estado">Estado laboral*</label>
                <select id="estado" name="estado" required>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
        </div>
        <div style="margin-bottom: 20px;">
            <label for="foto">Foto (opcional)</label>
            <input type="file" id="foto" name="foto" accept="image/*">
        </div>
        <div class="modal-actions" style="position: sticky; bottom: 0; background: white; padding: 15px 0; border-top: 1px solid var(--gris);">
            <button type="button" class="btn-cancelar" onclick="cerrarModal()" style="margin-right: 10px; background: #f0f0f0; color: #333;">Cancelar</button>
            <button type="submit" class="btn-guardar" name="guardar_profesor">Guardar Profesor</button>
        </div>
    </form>
</div>
<script>
    function cerrarModal() {
        document.getElementById("modalEstudiante").style.display = "none";
    }
    function abrirModal() {
        document.getElementById("modalEstudiante").style.display = "flex";
    }
    document.addEventListener("click", function(e) {
        let modalBg = document.getElementById("modalEstudiante");
        if (e.target === modalBg) {
            cerrarModal();
        }
    });
</script>
</body>
</html>