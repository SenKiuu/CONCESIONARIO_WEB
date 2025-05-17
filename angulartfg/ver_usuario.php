<?php
session_start();
include("includes/conexion.php");

if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["rol"] !== "admin") {
    header("Location: index.php");
    exit();
}

// Procesar eliminación de compra
if (isset($_POST['eliminar_compra'])) {
    $compra_id = $_POST['compra_id'];
    $sql_eliminar = "DELETE FROM compras WHERE id = ?";
    $stmt = $conn->prepare($sql_eliminar);
    $stmt->bind_param("i", $compra_id);
    $stmt->execute();
    
    // Refrescar la página después de eliminar
    header("Location: ver_usuario.php?id=".$_GET['id']);
    exit();
}

if (isset($_GET['id'])) {
    $usuario_id = $_GET['id'];

    // Obtener información del usuario
    $sql_usuario = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql_usuario);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    // Obtener compras del usuario con más detalles
    $sql_compras = "SELECT compras.id as compra_id, coches.* FROM compras
                    JOIN coches ON compras.coche_id = coches.id
                    WHERE compras.usuario_id = ?";
    $stmt = $conn->prepare($sql_compras);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $compras = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #4cc9f0;
            --dark: #14213d;
            --light: #f8f9fa;
            --light-gray: #e9ecef;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(248, 249, 250, 0.9), rgba(233, 236, 239, 0.9));
            min-height: 100vh;
            padding-top: 30px;
            padding-bottom: 50px;
        }
        
        .user-profile {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 30px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            color: var(--primary);
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .user-stats {
            display: flex;
            justify-content: space-around;
            padding: 15px;
            background: white;
            border-radius: 10px;
            margin-top: -20px;
            position: relative;
            z-index: 1;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: var(--dark);
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--primary);
        }
        
        .profile-body {
            padding: 25px;
        }
        
        .section-title {
            color: var(--dark);
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .car-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            background: white;
        }
        
        .car-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .car-img-container {
            height: 160px;
            overflow: hidden;
            position: relative;
            background-color: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .car-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .car-card:hover .car-img {
            transform: scale(1.03);
        }
        
        .car-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent);
            color: var(--dark);
            padding: 3px 8px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .car-body {
            padding: 15px;
        }
        
        .car-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .car-price {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 12px;
        }
        
        .btn-delete {
            background: linear-gradient(to right, #dc3545, #c82333);
            border: none;
            border-radius: 25px;
            padding: 6px 15px;
            color: white;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(220, 53, 69, 0.3);
            color: white;
        }
        
        .btn-back {
            background: var(--dark);
            color: white;
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            font-size: 14px;
        }
        
        .btn-back:hover {
            background: var(--secondary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            border: 1px dashed var(--light-gray);
        }
        
        .empty-icon {
            font-size: 50px;
            color: var(--accent);
            margin-bottom: 15px;
            opacity: 0.7;
        }
        
        .empty-text {
            color: var(--dark);
            opacity: 0.7;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .user-info-section {
            background: var(--light-gray);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-title {
            font-size: 14px;
            color: var(--primary);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="user-profile">
        <div class="profile-header">
            <div class="profile-pic">
                <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
            </div>
            <h3 class="mb-1"><?= htmlspecialchars($usuario['nombre']) ?></h3>
            <p class="mb-0"><span class="badge bg-light text-dark"><?= htmlspecialchars($usuario['rol']) ?></span></p>
        </div>
        
        <div class="user-stats">
            <div class="stat-item">
                <div class="stat-value"><?= $compras->num_rows ?></div>
                <div class="stat-label">Compras</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">#<?= htmlspecialchars($usuario['id']) ?></div>
                <div class="stat-label">ID Usuario</div>
            </div>
        </div>
        
        <div class="profile-body">
            <div class="user-info-section">
                <div class="info-title"><i class="fas fa-user-circle me-2"></i> Información Personal</div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-envelope me-2 text-muted"></i>
                    <span><?= htmlspecialchars($usuario['email']) ?></span>
                </div>
                <?php if(isset($usuario['telefono'])): ?>
                <div class="d-flex align-items-center">
                    <i class="fas fa-phone me-2 text-muted"></i>
                    <span><?= htmlspecialchars($usuario['telefono']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <h4 class="section-title"><i class="fas fa-car me-2"></i> Vehículos Comprados</h4>
            
            <?php if($compras->num_rows > 0): ?>
                <div class="row">
                    <?php while ($compra = $compras->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="car-card h-100">
                                <div class="car-img-container">
                                    <?php 
                                    $imagen_path = "images/coches/" . htmlspecialchars($compra['imagen']);
                                    if(file_exists($imagen_path)): ?>
                                        <img src="<?= $imagen_path ?>" class="car-img" alt="<?= htmlspecialchars($compra['modelo']) ?>">
                                    <?php else: ?>
                                        <div class="text-center p-4">
                                            <i class="fas fa-car fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="car-badge">Comprado</span>
                                </div>
                                <div class="car-body d-flex flex-column">
                                    <h5 class="car-title"><?= htmlspecialchars($compra['modelo']) ?></h5>
                                    <div class="car-price mt-auto"><?= number_format($compra['precio'], 0, ',', '.') ?> €</div>
                                    
                                    <form method="POST" class="mt-2">
                                        <input type="hidden" name="compra_id" value="<?= $compra['compra_id'] ?>">
                                        <button type="submit" name="eliminar_compra" class="btn btn-delete">
                                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-car-crash"></i>
                    </div>
                    <h5>Garaje vacío</h5>
                    <p class="empty-text">No se encontraron vehículos comprados</p>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="panel_admin.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Volver al Panel
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Confirmación con SweetAlert
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Eliminar compra?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
</body>
</html>