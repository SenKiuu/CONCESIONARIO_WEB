<?php
session_start();
include("includes/conexion.php");

// Variables de búsqueda y filtro
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$precio_min = isset($_GET['precio_min']) ? $_GET['precio_min'] : '';
$precio_max = isset($_GET['precio_max']) ? $_GET['precio_max'] : '';

// Construcción de la consulta SQL con filtro de búsqueda y precio
$sql = "SELECT * FROM coches WHERE 1";

// Filtro de búsqueda por modelo
if ($busqueda != '') {
    $sql .= " AND modelo LIKE '%$busqueda%'";
}

// Filtro de precio
if ($precio_min != '' && $precio_max != '') {
    $sql .= " AND precio BETWEEN $precio_min AND $precio_max";
} elseif ($precio_min != '') {
    $sql .= " AND precio >= $precio_min";
} elseif ($precio_max != '') {
    $sql .= " AND precio <= $precio_max";
}

$sql .= " LIMIT 12 OFFSET 0"; // Cambiar el OFFSET según la página

$coches = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Coches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Sidebar izquierdo con logo -->
    <div class="bg-dark text-white p-4" style="width: 250px;">
        <div class="d-flex justify-content-center mb-4">
            <img src="./img/logo.jpg" class="img-fluid" alt="Logo empresa" style="max-width: 100px;">
        </div>
        <h5 class="text-center">Pagina de <span class="navbar-brand"> <?= $_SESSION['usuario']['nombre'] ?></span></h5>
        <hr>
        <nav class="nav flex-column">
            <a class="nav-link text-white" href="./index.php">Inicio</a>
            <a class="nav-link text-white" href="perfil.php">Perfil</a>
            <a class="nav-link text-white" href="logout.php">Cerrar sesión</a>
        </nav>
    </div>

    <!-- Contenido principal -->
    <div class="flex-fill">
        <!-- Navbar superior con icono de carrito -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand">Bienvenido, <?= $_SESSION['usuario']['nombre'] ?></span>
                <div class="ms-auto d-flex">
                    <form class="d-flex" method="GET" action="coches.php">
                        <input class="form-control me-2" type="search" placeholder="Buscar coche" aria-label="Buscar" name="busqueda" value="<?= $busqueda ?>">
                        <input class="form-control me-2" type="number" placeholder="Precio mínimo" aria-label="Precio mínimo" name="precio_min" value="<?= $precio_min ?>">
                        <input class="form-control me-2" type="number" placeholder="Precio máximo" aria-label="Precio máximo" name="precio_max" value="<?= $precio_max ?>">
                        <button class="btn btn-outline-primary" type="submit">Buscar</button>
                    </form>
                    <button class="btn btn-outline-primary position-relative ms-2" id="carritoBtn" data-bs-toggle="modal" data-bs-target="#carritoModal">
                        <i class="bi bi-cart"></i> Carrito
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="carritoCantidad">0</span>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Listado de coches -->
        <div class="container py-4">
            <h3 class="mb-4">Nuestros Coches</h3>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while($coche = $coches->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card shadow-sm">
                            <img src="img/<?= $coche['imagen'] ?>" class="card-img-top" alt="<?= $coche['modelo'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $coche['modelo'] ?></h5>
                                <p class="card-text">Precio: <?= $coche['precio'] ?> €</p>
                                <button class="btn btn-success w-100" onclick="agregarCarrito(<?= $coche['id'] ?>)">Añadir al carrito</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <hr>
            <!-- Paginación -->
            <div class="mt-4 text-center">
                <a href="coches2.php" class="btn btn-primary">Página Siguiente</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal del carrito -->
<div class="modal fade" id="carritoModal" tabindex="-1" aria-labelledby="carritoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carritoModalLabel">Detalles del Carrito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <ul id="modalCarrito" class="list-group">
                    <!-- Los productos del carrito se añadirán aquí dinámicamente -->
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Finalizar Compra</button>
            </div>
        </div>
    </div>
</div>

<script src="js/carrito.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
