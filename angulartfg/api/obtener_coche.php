<?php
require_once "../includes/conexion.php";

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'ID no proporcionado']));
}

$id = intval($_GET['id']);
$sql = "SELECT id, modelo, precio FROM coches WHERE id = $id";
$resultado = $conn->query($sql);

if ($resultado->num_rows === 0) {
    die(json_encode(['error' => 'Coche no encontrado']));
}

header('Content-Type: application/json');
echo json_encode($resultado->fetch_assoc());
?>