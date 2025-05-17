<?php
session_start();
include("includes/conexion.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

// Obtener todos los usuarios
$sql = "SELECT id, nombre, email, password, rol FROM usuarios";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-between align-items-center mb-4">
        <div class="col">
            <h2>Panel de Administrador</h2>
        </div>
        <div class="col text-end">
            <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
        </div>
    </div>

    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Usuarios Registrados</h5>
        </div>
        <div class="card-body">
            <!-- Tabla de usuarios -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Contraseña</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($usuario = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= htmlspecialchars(substr($usuario['password'], 0, 2)) . str_repeat('*', 12) ?></td>
                            <td><?= htmlspecialchars($usuario['rol']) ?></td>
                            <td>
                                <a href="ver_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-info btn-sm">Ver</a>
                                <a href="modificar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-warning btn-sm">Modificar</a>
                                <a href="eliminar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
