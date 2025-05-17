<?php
session_start();
include("includes/conexion.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $usuario_id = $_GET['id'];

    // Verificar si el usuario existe
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Eliminar el usuario
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);

        if ($stmt->execute()) {
            // Redirigir al panel de administrador
            header("Location: panel_admin.php?mensaje=Usuario eliminado exitosamente");
            exit();
        } else {
            // Mostrar mensaje de error
            echo "Error al eliminar el usuario.";
        }
    } else {
        // Si no existe el usuario
        echo "Usuario no encontrado.";
    }
} else {
    // Si no se pasa un id en la URL
    echo "No se ha proporcionado un ID válido.";
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuario</title>
</head>
<body>
    <h1>Eliminar Usuario</h1>
    <p>El usuario ha sido eliminado correctamente. Serás redirigido al panel de administrador.</p>
</body>
</html>
