<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['rol'] !== 'Administrador') {
    header("Location: formulario_datos.php");
    exit();
}

$page_title = "Usuarios";
$base_dir = realpath(__DIR__ . '/../') . '/';
$required_files = [
    'includes/database.php',
    'includes/header.php',
    'includes/footer.php'
];

foreach ($required_files as $file) {
    if (!file_exists($base_dir . $file)) {
        die("Error: Archivo requerido no encontrado - " . $file);
    }
}

require_once $base_dir . 'includes/database.php';
require_once $base_dir . 'includes/header.php';

if (!isset($conexion) || !$conexion) {
    die("Error de conexión: " . (isset($conexion) ? pg_last_error($conexion) : "Conexión no establecida"));
}

$filtro = '';
$params = [];
if (isset($_GET['buscar']) && $_GET['buscar'] !== '') {
    $buscar = trim($_GET['buscar']);
    $filtro = "WHERE 
        LOWER(nombres) LIKE $1 
        OR LOWER(apellidos) LIKE $1 
        OR LOWER(departamento_trabajo) LIKE $1 
        OR LOWER(nombres || ' ' || apellidos) LIKE $1";
    $params[] = '%' . strtolower($buscar) . '%';
}

$query = "SELECT * FROM gestores $filtro ORDER BY cod_gestor";
$result = $params ? pg_query_params($conexion, $query, $params) : pg_query($conexion, $query);
if (!$result) {
    die("Error en la consulta SQL: " . pg_last_error($conexion));
}
$gestores = pg_fetch_all($result) ?: [];
?>

<style>
.container h1 {
    margin-top: 20px;
    font-weight: bold;
    color: #1d3557;
}
.btn-primary,
.btn-secondary,
.btn-warning,
.btn-danger {
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
.btn-warning { background-color: #ffb703; color: #000; }
.btn-warning:hover { background-color: #e6a800; }
.btn-danger { background-color: #e63946; }
.btn-danger:hover { background-color: #c82333; }

form.mb-3 {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
form.mb-3 input[type="text"] {
    flex-grow: 1;
    min-width: 240px;
}
form.mb-3 a {
    margin-left: auto;
}

/* Tarjeta estilo móvil */
.card-user {
    display: none;
}

/* Estilo normal (pantallas grandes) */
@media (min-width: 769px) {
    .table-responsive {
        display: block;
        border-radius: 12px;
        
    }
    .card-user {
        display: none;
    }
}

/* Estilo responsive (pantallas pequeñas) */
@media screen and (max-width: 768px) {
    .table-responsive {
        display: none;
    }

    .card-user {
        display: block;
        border: 1px solid #ccc;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
    }

    .card-user h5 {
        color: #1d3557;
        margin-bottom: 5px;
    }

    .card-user p {
        margin: 4px 0;
        font-size: 0.95rem;
    }

   .card-user .acciones {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: center; /* centra horizontalmente */
}

.card-user .acciones .btn-sm {
    width: auto;
    font-size: 0.85rem;
    padding: 6px 10px;
}
}
</style>

<div class="container">
    <h1>Gestión de Usuarios</h1>

    <form method="get" class="mb-3">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o departamento de trabajo"
            value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Buscar
        </button>
        <a href="gestores.php" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Limpiar
        </a>
    </form>

    <a href="crear.php" class="btn btn-primary mb-3"><i class="bi bi-person-plus"></i> Nuevo Usuario</a>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?>">
            <?= $_SESSION['mensaje'] ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Tabla (solo en escritorio) -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark text-center">
                <tr>
                    <th>Código</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>Documento</th>
                    <th>Departamento</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gestores as $gestor): ?>
                <tr>
                    <td><?= htmlspecialchars($gestor['cod_gestor']) ?></td>
                    <td><?= htmlspecialchars($gestor['nombres']) ?></td>
                    <td><?= htmlspecialchars($gestor['apellidos']) ?></td>
                    <td><?= htmlspecialchars($gestor['correo']) ?></td>
                    <td><?= htmlspecialchars($gestor['documento']) ?></td>
                    <td><?= htmlspecialchars($gestor['departamento_trabajo']) ?></td>
                    <td><?= htmlspecialchars($gestor['rol']) ?></td>
                    <td class="text-center">
                        <a href="editar.php?id=<?= $gestor['cod_gestor'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i> Editar</a>
                        <form action="eliminar.php" method="POST" style="display: inline;">
                            <input type="hidden" name="cod_gestor" value="<?= $gestor['cod_gestor'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este gestor?')"><i class="bi bi-trash"></i> Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($gestores)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">No se encontraron resultados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Vista móvil como tarjetas -->
    <?php foreach ($gestores as $gestor): ?>
        <div class="card-user">
            <h5><?= htmlspecialchars($gestor['nombres'] . ' ' . $gestor['apellidos']) ?></h5>
            <p><strong>Código:</strong> <?= $gestor['cod_gestor'] ?></p>
            <p><strong>Correo:</strong> <?= $gestor['correo'] ?></p>
            <p><strong>Documento:</strong> <?= $gestor['documento'] ?></p>
            <p><strong>Departamento:</strong> <?= $gestor['departamento_trabajo'] ?></p>
            <p><strong>Rol:</strong> <?= $gestor['rol'] ?></p>
            <div class="acciones">
                <a href="editar.php?id=<?= $gestor['cod_gestor'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i> Editar</a>
                <form action="eliminar.php" method="POST">
                    <input type="hidden" name="cod_gestor" value="<?= $gestor['cod_gestor'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este gestor?')"><i class="bi bi-trash"></i> Eliminar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once $base_dir . 'includes/footer.php'; ?>
