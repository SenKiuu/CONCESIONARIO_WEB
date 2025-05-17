<?php
session_start();
include("includes/conexion.php");

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'ID no proporcionado']));
}

$cocheId = intval($_GET['id']);

$sql = "SELECT c.id, c.modelo, c.precio, cd.marca, cd.ano 
        FROM coches c
        LEFT JOIN coche_detalles cd ON c.id = cd.coche_id
        WHERE c.id = $cocheId";

$resultado = $conn->query($sql);

if ($resultado->num_rows === 0) {
    die(json_encode(['error' => 'Coche no encontrado']));
}

$coche = $resultado->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($coche);
?>