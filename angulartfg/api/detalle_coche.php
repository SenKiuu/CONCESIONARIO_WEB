<?php
require_once "../includes/conexion.php";

if (!isset($_GET['id'])) {
    die("ID no proporcionado");
}

$id = intval($_GET['id']);
$sql = "SELECT c.*, cd.* FROM coches c 
        LEFT JOIN coche_detalles cd ON c.id = cd.coche_id
        WHERE c.id = $id";
$resultado = $conn->query($sql);

if ($resultado->num_rows === 0) {
    die("Coche no encontrado");
}

$coche = $resultado->fetch_assoc();

// Obtener imágenes adicionales (si tienes la tabla coche_imagenes)
$imagenes = [$coche['imagen']]; // Imagen principal
$sqlImagenes = "SELECT imagen FROM coche_imagenes WHERE coche_id = $id";
$resultImagenes = $conn->query($sqlImagenes);

while ($img = $resultImagenes->fetch_assoc()) {
    $imagenes[] = $img['imagen'];
}

// Generar HTML del detalle
ob_start();
?>
<div class="row">
    <div class="col-md-6">
        <div id="carouselCoche" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($imagenes as $i => $imagen): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <img src="../img/<?= htmlspecialchars($imagen) ?>" class="d-block w-100" style="height: 300px; object-fit: cover;">
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselCoche" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselCoche" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </div>
    <div class="col-md-6">
        <h3><?= htmlspecialchars($coche['modelo']) ?></h3>
        <h4 class="text-primary"><?= number_format($coche['precio'], 2) ?> €</h4>
        <hr>
        <p><strong>Marca:</strong> <?= htmlspecialchars($coche['marca']) ?></p>
        <p><strong>Año:</strong> <?= htmlspecialchars($coche['ano']) ?></p>
        <p><strong>Combustible:</strong> <?= htmlspecialchars($coche['combustible']) ?></p>
        <p><strong>Potencia:</strong> <?= htmlspecialchars($coche['potencia']) ?> CV</p>
        <p><strong>Color:</strong> <?= htmlspecialchars($coche['color']) ?></p>
        <p><strong>Kilómetros:</strong> <?= isset($coche['kilometros']) ? number_format($coche['kilometros'], 0) : 'N/A' ?> km</p>
    </div>
</div>
<div class="mt-4">
    <h5>Descripción</h5>
    <p><?= !empty($coche['descripcion']) ? nl2br(htmlspecialchars($coche['descripcion'])) : 'No hay descripción disponible.' ?></p>
</div>
<?php
echo ob_get_clean();
?>