<?php
session_start();
include("includes/conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION["usuario"]["id"];
$carrito = json_decode($_POST["carrito"], true);

if (!is_array($carrito)) {
    die("Error con el carrito.");
}

foreach ($carrito as $coche_id) {
    $stmt = $conn->prepare("INSERT INTO compras (usuario_id, coche_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $usuario_id, $coche_id);
    $stmt->execute();
}

echo "<script>alert('Compra realizada con éxito'); window.location='tienda.php';</script>";
?>
