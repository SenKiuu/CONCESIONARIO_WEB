<?php
session_start();
include("includes/conexion.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $usuario_id = $_GET['id'];

    // Obtener información actual del usuario
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST["nombre"];
        $email = $_POST["email"];
        $rol = $_POST["rol"];

        // Actualizar la información del usuario
        $sql_update = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssi", $nombre, $email, $rol, $usuario_id);

        if ($stmt->execute()) {
            $mensaje = "Usuario actualizado correctamente.";
        } else {
            $mensaje = "Error al actualizar el usuario.";
        }
    }
} else {
    header("Location: panel_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-5">
    <h2>Modificar Usuario</h2>
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-info">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>Editar Datos del Usuario</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="rol" class="form-label">Rol:</label>
                    <select name="rol" id="rol" class="form-select" required>
                        <option value="usuario" <?= $usuario['rol'] == 'usuario' ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <a href="panel_admin.php" class="btn btn-primary mt-3">Volver al Panel</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
