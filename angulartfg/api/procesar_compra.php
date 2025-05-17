<?php
session_start();
include("includes/conexion.php");

if (!isset($_SESSION['usuario'])) {
    die(json_encode(['error' => 'No autenticado']));
}

$carrito = json_decode($_POST['carrito'], true);
$usuarioId = $_SESSION['usuario']['id'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // Crear la compra
    $sqlCompra = "INSERT INTO compras (usuario_id, fecha) VALUES (?, NOW())";
    $stmt = $conn->prepare($sqlCompra);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $compraId = $conn->insert_id;
    
    // Insertar los items de la compra
    foreach ($carrito as $item) {
        $sqlItem = "INSERT INTO compra_items (compra_id, coche_id, cantidad, precio_unitario) 
                    VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlItem);
        $stmt->bind_param("iiid", $compraId, $item['id'], $item['cantidad'], $item['precio']);
        $stmt->execute();
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'compra_id' => $compraId]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar la compra: ' . $e->getMessage()]);
}
?>