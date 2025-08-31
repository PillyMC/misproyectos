<!-- sidebar.php -->
<aside class="sidebar">
    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>
    <nav>
        <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>Estudiantes</a>
        <a href="grupos.php" <?= basename($_SERVER['PHP_SELF']) == 'grupos.php' ? 'class="active"' : '' ?>>Grupos</a>
        <!-- Más enlaces -->
    </nav>
    <div class="user">
        <strong>Admin</strong><br>
        admin@nclases.com
        <button class="btn-salir" onclick="salir()">Salir</button>
    </div>
</aside>