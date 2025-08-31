<?php
session_start();
// Configuración de rutas
$base_dir = realpath(__DIR__ . '/../') . '/';
$conexion_file = $base_dir . 'includes/database.php';

if (!file_exists($conexion_file)) {
    die("Error crítico: Archivo de conexión no encontrado en: " . $conexion_file);
}

require_once $conexion_file;
require_once $base_dir . 'includes/header.php';

if (!isset($conexion)) {
    die("Error: No se pudo establecer conexión con la base de datos");
}

if (!isset($_GET['id'])) {
    die("Error: No se especificó el ID del gestor a editar");
}

$id = $_GET['id'];

$query = "SELECT * FROM gestores WHERE cod_gestor = $1";
$result = pg_query_params($conexion, $query, [$id]);

if (!$result) {
    die("Error en la consulta: " . pg_last_error($conexion));
}

$gestor = pg_fetch_assoc($result);

if (!$gestor) {
    die("Error: Gestor no encontrado");
}

// Control de permisos
$soloLectura = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Gestor') ? 'readonly' : '';
$soloDeshabilitado = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Gestor') ? 'disabled' : '';
$esAdmin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador');
?>
<style>
.container h1 {
    margin-top: 20px;
    font-weight: bold;
    color: #1d3557;
}
.btn-primary, .btn-secondary {
    border: none;
    border-radius: 10px;
    padding: 8px 14px;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}
.btn-primary { background-color: #2056a7; }
.btn-primary:hover { background-color: #003d80; transform: translateY(-1px); }
.btn-secondary { background-color: #6c757d; }
.btn-secondary:hover { background-color: #5a6268; }
.row { margin-bottom: 15px; }
.form-label { font-weight: 600; }
.form-control, .form-select {
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #ced4da;
}
</style>

<div class="container py-4">
    <h2 class="text-center mb-4">Gestor</h2>

    <form action="procesar.php" method="POST">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="cod_gestor" value="<?= htmlspecialchars($gestor['cod_gestor']) ?>">

        <?php if (!empty($gestor['foto_perfil']) && file_exists($base_dir . $gestor['foto_perfil'])): ?>
            <div class="mb-3 text-center">
                <img src="../<?= htmlspecialchars($gestor['foto_perfil']) ?>"
                     alt="Foto de perfil"
                     style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">
                <p class="mt-2 mb-0 text-muted" style="font-size: 0.95em;">Foto de perfil actual</p>
            </div>
        <?php else: ?>
            <div class="mb-3 text-center">
                <img src="../uploads/perfiles/default.png"
                     alt="Sin foto"
                     style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; opacity:0.5;">
                <p class="mt-2 mb-0 text-muted" style="font-size: 0.95em;">Sin foto de perfil</p>
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombres" class="form-label">Nombres:</label>
                <input type="text" id="nombres" name="nombres" class="form-control" required value="<?= htmlspecialchars($gestor['nombres']) ?>" <?= $soloLectura ?>>
            </div>

            <div class="col-md-6">
                <label for="apellidos" class="form-label">Apellidos:</label>
                <input type="text" id="apellidos" name="apellidos" class="form-control" required value="<?= htmlspecialchars($gestor['apellidos']) ?>" <?= $soloLectura ?>>
            </div>

            <div class="col-md-6">
                <label for="correo" class="form-label">Correo:</label>
                <input type="email" id="correo" name="correo" class="form-control" required value="<?= htmlspecialchars($gestor['correo']) ?>" <?= $soloLectura ?>>
            </div>

            <div class="col-md-6">
                <label for="documento" class="form-label">Documento:</label>
                <input type="text" id="documento" name="documento" class="form-control" required value="<?= htmlspecialchars($gestor['documento']) ?>" <?= $soloLectura ?>>
            </div>

            <div class="col-md-6">
                <label for="distrito" class="form-label">Distrito Residencia:</label>
                <input type="text" id="distrito" name="distrito" class="form-control" required value="<?= htmlspecialchars($gestor['distrito_residencia']) ?>" <?= $soloLectura ?>>
            </div>

            <div class="col-md-6">
                <label for="departamento" class="form-label">Departamento Trabajo:</label>
                <input type="text" id="departamento" name="departamento" class="form-control" required value="<?= htmlspecialchars($gestor['departamento_trabajo']) ?>" <?= $soloLectura ?>>
            </div>

            <div class="col-md-6">
                <label for="rol" class="form-label">Rol:</label>
                <select id="rol" name="rol" class="form-select" required <?= $soloDeshabilitado ?>>
                    <option value="Gestor" <?= $gestor['rol'] === 'Gestor' ? 'selected' : '' ?>>Gestor</option>
                    <option value="Administrador" <?= $gestor['rol'] === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

           <?php if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Gestor'): ?>
<div class="col-md-6">
    <label for="clave" class="form-label">Nueva Contraseña:</label>
    <input type="password" id="clave" name="clave" class="form-control" placeholder="Dejar en blanco para no cambiar" <?= $soloLectura ?>>
    <small class="text-muted">Mínimo 6 caracteres</small>
</div>
<?php endif; ?>
        </div>

        <?php if ($esAdmin): ?>
        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="gestores.php" class="btn btn-secondary">Cancelar</a>
        </div>
        <?php endif; ?>
    </form>
</div>

<script>
// Validación opcional del lado del cliente
document.querySelector('form').addEventListener('submit', function(e) {
    const clave = document.getElementById('clave').value;
    if (clave && clave.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres');
        e.preventDefault();
    }
});
</script>

<?php
require_once $base_dir . 'includes/footer.php';
?>