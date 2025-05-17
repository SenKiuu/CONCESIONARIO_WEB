<?php
session_start();
include("includes/conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener parámetros de búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$precio_min = isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : '';
$precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : '';

// Configuración de paginación
$por_pagina = 12;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Consulta base
$sql = "SELECT c.*, cd.marca, cd.ano, cd.combustible, cd.potencia 
        FROM coches c
        LEFT JOIN coche_detalles cd ON c.id = cd.coche_id
        WHERE 1";

// Aplicar filtros
if (!empty($busqueda)) {
    $sql .= " AND c.modelo LIKE '%" . $conn->real_escape_string($busqueda) . "%'";
}

if (!empty($precio_min) && !empty($precio_max)) {
    $sql .= " AND c.precio BETWEEN $precio_min AND $precio_max";
} elseif (!empty($precio_min)) {
    $sql .= " AND c.precio >= $precio_min";
} elseif (!empty($precio_max)) {
    $sql .= " AND c.precio <= $precio_max";
}

// Consulta para el total
$resultado_total = $conn->query(str_replace('c.*, cd.marca, cd.ano, cd.combustible, cd.potencia', 'COUNT(*) as total', $sql));
$total_coches = $resultado_total->fetch_assoc()['total'];
$total_paginas = ceil($total_coches / $por_pagina);

// Consulta paginada
$sql .= " LIMIT $por_pagina OFFSET $offset";
$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 180px;
            object-fit: cover;
            cursor: pointer;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-4" style="width: 250px; min-height: 100vh;">
        <div class="text-center mb-4">
            <img src="./img/logo.jpg" class="rounded-circle" width="100" alt="Logo">
            <h5 class="mt-2">Concesionario</h5>
        </div>
        <hr>
        <nav class="nav flex-column">
            <a class="nav-link text-white active" href="coches.php"><i class="bi bi-house-door"></i> Inicio</a>
            <a class="nav-link text-white" href="perfil.php"><i class="bi bi-person"></i> Perfil</a>
            <a class="nav-link text-white" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
        </nav>
    </div>

    <!-- Contenido principal -->
    <div class="flex-fill">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></span>
                <div class="d-flex">
                    <form class="d-flex me-2" method="GET" action="coches.php">
                        <input type="hidden" name="pagina" value="1">
                        <input class="form-control me-2" type="search" placeholder="Buscar" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    </form>
                    <button class="btn btn-primary position-relative" id="btnCarrito">
                        <i class="bi bi-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="contadorCarrito">0</span>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Filtros -->
        <div class="container mt-3">
            <form method="GET" action="coches.php">
                <input type="hidden" name="pagina" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Precio mínimo</label>
                        <input type="number" class="form-control" name="precio_min" value="<?= htmlspecialchars($precio_min) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Precio máximo</label>
                        <input type="number" class="form-control" name="precio_max" value="<?= htmlspecialchars($precio_max) ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Listado de coches -->
        <div class="container py-4">
            <h2 class="mb-4">Nuestros Coches</h2>
            
            <?php if ($resultado->num_rows > 0): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while($coche = $resultado->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <img src="img/<?= htmlspecialchars($coche['imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($coche['modelo']) ?>" onclick="verDetalle(<?= $coche['id'] ?>)">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($coche['modelo']) ?></h5>
                                    <p class="card-text">
                                        <span class="badge bg-primary"><?= htmlspecialchars($coche['marca']) ?></span>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($coche['ano']) ?></span>
                                        <span class="badge bg-success"><?= number_format($coche['precio'], 2) ?> €</span>
                                    </p>
                                </div>
                                <div class="card-footer bg-white">
                                    <button class="btn btn-sm btn-success" onclick="agregarAlCarrito(event, <?= $coche['id'] ?>)">
                                        <i class="bi bi-cart-plus"></i> Añadir
                                    </button>
                                    <button class="btn btn-sm btn-primary float-end" onclick="verDetalle(<?= $coche['id'] ?>)">
                                        <i class="bi bi-info-circle"></i> Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No se encontraron coches con los filtros aplicados.</div>
            <?php endif; ?>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina_actual > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>">Siguiente</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de detalles -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Coche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalleCoche">
                <!-- Contenido cargado por AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnAddFromModal">Añadir al carrito</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal del carrito -->
<div class="modal fade" id="modalCarrito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tu Carrito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="itemsCarrito">
                    <!-- Items del carrito -->
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <h5>Total:</h5>
                    <h5 id="totalCarrito">0.00 €</h5>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Seguir comprando</button>
                <button type="button" class="btn btn-success" id="btnFinalizarCompra">Finalizar compra</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Carrito en memoria
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
let currentCarId = null;

// Actualizar contador del carrito
function actualizarCarrito() {
    const total = carrito.reduce((sum, item) => sum + item.cantidad, 0);
    $('#contadorCarrito').text(total);
    localStorage.setItem('carrito', JSON.stringify(carrito));
}

// Agregar al carrito
function agregarAlCarrito(event, cocheId) {
    event.stopPropagation();
    
    $.ajax({
        url: 'api/obtener_coche.php',
        method: 'GET',
        data: { id: cocheId },
        dataType: 'json',
        success: function(coche) {
            const itemExistente = carrito.find(item => item.id === coche.id);
            
            if (itemExistente) {
                itemExistente.cantidad += 1;
            } else {
                carrito.push({
                    id: coche.id,
                    modelo: coche.modelo,
                    precio: coche.precio,
                    cantidad: 1
                });
            }
            
            actualizarCarrito();
            mostrarAlerta(`${coche.modelo} añadido al carrito`, 'success');
        },
        error: function() {
            mostrarAlerta('Error al agregar al carrito', 'danger');
        }
    });
}

// Ver detalles del coche
function verDetalle(cocheId) {
    currentCarId = cocheId;
    
    $.ajax({
        url: 'api/detalle_coche.php',
        method: 'GET',
        data: { id: cocheId },
        success: function(data) {
            $('#detalleCoche').html(data);
            const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
            modal.show();
        },
        error: function() {
            mostrarAlerta('Error al cargar detalles', 'danger');
        }
    });
}

// Mostrar alerta
function mostrarAlerta(mensaje, tipo) {
    const alerta = $(`
        <div class="alert alert-${tipo} alert-dismissible fade show position-fixed bottom-0 end-0 m-3" role="alert">
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
    
    $('body').append(alerta);
    setTimeout(() => alerta.alert('close'), 3000);
}

// Cargar carrito en el modal
function cargarCarrito() {
    if (carrito.length === 0) {
        $('#itemsCarrito').html('<p>El carrito está vacío</p>');
        $('#totalCarrito').text('0.00 €');
        return;
    }
    
    let html = '';
    let total = 0;
    
    carrito.forEach(item => {
        total += item.precio * item.cantidad;
        html += `
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6>${item.modelo}</h6>
                        <small>${item.precio.toFixed(2)} € x ${item.cantidad}</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(${item.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#itemsCarrito').html(html);
    $('#totalCarrito').text(total.toFixed(2) + ' €');
}

// Eliminar del carrito
function eliminarDelCarrito(cocheId) {
    carrito = carrito.filter(item => item.id !== cocheId);
    actualizarCarrito();
    cargarCarrito();
    mostrarAlerta('Producto eliminado', 'warning');
}

// Eventos al cargar la página
$(document).ready(function() {
    actualizarCarrito();
    
    // Abrir modal del carrito
    $('#btnCarrito').click(function() {
        cargarCarrito();
        const modal = new bootstrap.Modal(document.getElementById('modalCarrito'));
        modal.show();
    });
    
    // Añadir desde modal de detalles
    $('#btnAddFromModal').click(function() {
        if (currentCarId) {
            agregarAlCarrito({ stopPropagation: () => {} }, currentCarId);
            bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();
        }
    });
    
    // Finalizar compra
    $('#btnFinalizarCompra').click(function() {
        if (carrito.length === 0) {
            mostrarAlerta('El carrito está vacío', 'warning');
            return;
        }
        
        $.ajax({
            url: 'api/procesar_compra.php',
            method: 'POST',
            data: { carrito: JSON.stringify(carrito) },
            success: function() {
                carrito = [];
                actualizarCarrito();
                bootstrap.Modal.getInstance(document.getElementById('modalCarrito')).hide();
                mostrarAlerta('Compra realizada con éxito', 'success');
            },
            error: function() {
                mostrarAlerta('Error al procesar la compra', 'danger');
            }
        });
    });
});
</script>
</body>
</html>