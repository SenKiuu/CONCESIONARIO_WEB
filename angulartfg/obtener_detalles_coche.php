<?php
session_start();
include("includes/conexion.php");

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'ID no proporcionado']));
}

$cocheId = intval($_GET['id']);

// Obtener información básica del coche
$sql = "SELECT c.*, cd.* 
        FROM coches c
        LEFT JOIN coche_detalles cd ON c.id = cd.coche_id
        WHERE c.id = $cocheId";

$resultado = $conn->query($sql);

if ($resultado->num_rows === 0) {
    die(json_encode(['error' => 'Coche no encontrado']));
}

$coche = $resultado->fetch_assoc();

// Obtener imágenes adicionales (asumiendo que tienes una tabla coche_imagenes)
$imagenes_adicionales = [];
$sqlImagenes = "SELECT imagen FROM coche_imagenes WHERE coche_id = $cocheId";
$resultadoImagenes = $conn->query($sqlImagenes);

while ($imagen = $resultadoImagenes->fetch_assoc()) {
    $imagenes_adicionales[] = $imagen['imagen'];
}

// Preparar la respuesta
$respuesta = [
    'id' => $coche['id'],
    'modelo' => $coche['modelo'],
    'marca' => $coche['marca'],
    'ano' => $coche['ano'],
    'kilometros' => $coche['kilometros'],
    'combustible' => $coche['combustible'],
    'potencia' => $coche['potencia'],
    'color' => $coche['color'],
    'precio' => $coche['precio'],
    'descripcion' => $coche['descripcion'],
    'imagen' => $coche['imagen'],
    'imagenes_adicionales' => $imagenes_adicionales
];

header('Content-Type: application/json');
echo json_encode($respuesta);
?>