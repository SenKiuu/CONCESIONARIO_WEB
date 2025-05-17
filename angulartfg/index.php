<?php
session_start();
include("includes/conexion.php");

$mensaje = "";
$showLogin = false;
$showRegister = false;
$showCarDetails = false;
$selectedCar = null;

// Mostrar mensajes de sesión si existen
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Mostrar formularios según parámetro
if (isset($_GET['form'])) {
    if ($_GET['form'] == 'login') {
        $showLogin = true;
    } elseif ($_GET['form'] == 'register') {
        $showRegister = true;
    }
}

// Mostrar detalles del coche
if (isset($_GET['car_id'])) {
    $carId = intval($_GET['car_id']);
    $sql = "SELECT c.*, cd.* FROM coches c 
            JOIN coche_detalles cd ON c.id = cd.coche_id 
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $carId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $selectedCar = $result->fetch_assoc();
        $showCarDetails = true;
    }
}

// Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION["usuario"] = $usuario;

        if ($usuario["rol"] == "admin") {
            header("Location: admin.php");
        } else {
            header("Location: tienda.php");
        }
        exit();
    } else {
        $mensaje = "Usuario o contraseña incorrectos.";
        $showLogin = true;
    }
}

// Registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $rol = $_POST["rol"];

    // Verificar si el correo ya está registrado
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $mensaje = "Este correo electrónico ya está registrado.";
        $showRegister = true;
    } else {
        // Insertar el nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Registro exitoso. Ahora puedes iniciar sesión.";
            header("Location: ?form=login");
            exit();
        } else {
            $mensaje = "Error al registrar el usuario.";
            $showRegister = true;
        }
    }
}

// Contacto/Incidencias
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["contact"])) {
    $email = isset($_SESSION["usuario"]) ? $_SESSION["usuario"]["email"] : $_POST["email"];
    $mensaje_contacto = $_POST["mensaje"];
    
    $sql = "INSERT INTO incidencias (usuario_email, mensaje) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $mensaje_contacto);
    
    if ($stmt->execute()) {
        $mensaje = "Mensaje enviado con éxito. Nos pondremos en contacto contigo pronto.";
    } else {
        $mensaje = "Error al enviar el mensaje. Por favor, inténtalo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionario Premium</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a2a4a;
            --secondary-color: #0d6efd;
            --accent-color: #0b5ed7;
            --dark-blue: #052c65;
            --light-blue: #d1e3ff;
        }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), 
                      url('./img/fondo8.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            color: white;
        }
        
        .navbar {
            background: rgba(10, 25, 50, 0.9) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(13, 110, 253, 0.3);
        }
        
        .hero-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            padding-top: 100px;
            padding-bottom: 100px;
        }
        
        .hero-card {
            background: rgba(13, 110, 253, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(13, 110, 253, 0.3);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .hero-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }
        
        .btn-premium {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        
        .btn-premium:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-3px);
        }
        
        .btn-outline-premium {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
        }
        
        .btn-outline-premium:hover {
            background: var(--secondary-color);
            color: white;
        }
        
        .form-container {
            background: rgba(10, 25, 50, 0.9);
            border-radius: 10px;
            padding: 30px;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--secondary-color);
        }
        
        .form-control {
            border-radius: 50px;
            padding: 12px 20px;
            border: 2px solid #444;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
        }
        
        .logo span {
            color: var(--secondary-color);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 50px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background: var(--secondary-color);
            bottom: -15px;
            left: 25%;
        }
        
        .car-card {
            background: rgba(10, 25, 50, 0.7);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            border: 1px solid rgba(13, 110, 253, 0.3);
        }
        
        .car-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--secondary-color);
        }
        
        .car-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .testimonial-card {
            background: rgba(10, 25, 50, 0.7);
            border-radius: 10px;
            padding: 30px;
            border: 1px solid rgba(13, 110, 253, 0.3);
        }
        
        .testimonial-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary-color);
        }
        
        .blue-text {
            color: var(--secondary-color);
        }
        
        .nav-link {
            color: white !important;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        .active-link {
            color: var(--secondary-color) !important;
            font-weight: bold;
        }
        
        .car-detail-container {
            background: rgba(10, 25, 50, 0.9);
            border-radius: 15px;
            border: 1px solid var(--secondary-color);
        }
        
        .specs-list {
            list-style: none;
            padding: 0;
        }
        
        .specs-list li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .specs-list li:last-child {
            border-bottom: none;
        }
        
        .spec-icon {
            color: var(--secondary-color);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body class="d-flex flex-column">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="?">
            <h1 class="logo mb-0">
                <i class="fas fa-car me-2"></i>Concesionario <span>Xuri</span>
            </h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active-link" href="?">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#vehiculos">Vehículos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#servicios">Servicios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#testimonios">Testimonios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contacto">Contacto</a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a href="?form=login" class="btn btn-outline-premium btn-sm me-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?form=register" class="btn btn-premium btn-sm">
                        <i class="fas fa-user-plus me-1"></i>Registro
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<?php if (!$showLogin && !$showRegister && !$showCarDetails): ?>
<section class="hero-section" id="inicio">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInDown">
                    Excelencia Automotriz <span class="blue-text">Sin Límites</span>
                </h1>
                <p class="lead mb-5 animate__animated animate__fadeIn animate__delay-1s">
                    En Concesionario Xuri, redefinimos el lujo automotriz. Cada vehículo en nuestra colección ha sido meticulosamente seleccionado para ofrecerte una experiencia de conducción incomparable.
                </p>
                <div class="d-flex gap-3 animate__animated animate__fadeInUp animate__delay-1s">
                    <a href="#vehiculos" class="btn btn-premium btn-lg">
                        <i class="fas fa-car me-2"></i>Descubrir
                    </a>
                    <a href="#contacto" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Contactar
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block animate__animated animate__fadeInRight animate__delay-1s">
                <div class="hero-card p-4">
                    <img src="./img/acura_nsx.jpg" 
                         alt="Luxury Car" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Formularios (Login/Register) -->
<?php if ($showLogin || $showRegister): ?>
    <section class="py-5" style="min-height: 80vh; display: flex; align-items: center;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <!-- Formulario de Login -->
                    <?php if ($showLogin): ?>
                        <div class="form-container animate__animated animate__fadeIn mx-auto">
                            <h3 class="text-center mb-4 blue-text"><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</h3>
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" name="login" class="btn btn-premium">
                                        <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                                    </button>
                                </div>
                                <div class="text-center">
                                    <a href="?" class="text-muted"><i class="fas fa-arrow-left me-2"></i>Volver al inicio</a>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulario de Registro -->
                    <?php if ($showRegister): ?>
                        <div class="form-container animate__animated animate__fadeIn mx-auto">
                            <h3 class="text-center mb-4 blue-text"><i class="fas fa-user-plus me-2"></i>Crear Cuenta</h3>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                                <div class="mb-4">
                                    <label for="rol" class="form-label">Tipo de Cuenta</label>
                                    <select name="rol" id="rol" class="form-select" required>
                                        <option value="usuario">Usuario Normal</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" name="register" class="btn btn-premium">
                                        <i class="fas fa-user-plus me-2"></i>Registrarse
                                    </button>
                                </div>
                                <div class="text-center">
                                    <a href="?" class="text-muted"><i class="fas fa-arrow-left me-2"></i>Volver al inicio</a>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Detalles del Coche -->
<?php if ($showCarDetails && $selectedCar): ?>
    <section class="py-5" style="min-height: 80vh;">
        <div class="container">
            <div class="row">
                <br>
                <div class="col-12 mb-4 mt-4">
                    <a href="?" class="btn btn-outline-premium">
                        <i class="fas fa-arrow-left me-2"></i> Volver a la galería
                    </a>
                </div>
                
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="car-detail-container p-4 h-100">
                        <?php
                        $imagen_ruta = 'img/' . htmlspecialchars($selectedCar['imagen']);
                        $imagen_default = 'img/default_car.jpg';
                        $imagen_final = file_exists($imagen_ruta) ? $imagen_ruta : $imagen_default;
                        ?>
                        <img src="<?php echo $imagen_final; ?>" alt="<?php echo htmlspecialchars($selectedCar['modelo']); ?>" class="img-fluid rounded">
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="car-detail-container p-4 h-100">
                        <h2 class="blue-text mb-3"><?php echo $selectedCar['modelo']; ?></h2>
                        <h4 class="text-white mb-4"><?php echo number_format($selectedCar['precio'], 0, ',', '.'); ?> €</h4>
                        
                        <ul class="specs-list">
                            <li>
                                <i class="fas fa-tag spec-icon"></i>
                                <strong>Marca:</strong> <?php echo $selectedCar['marca']; ?>
                            </li>
                            <li>
                                <i class="fas fa-calendar-alt spec-icon"></i>
                                <strong>Año:</strong> <?php echo $selectedCar['ano']; ?>
                            </li>
                            <li>
                                <i class="fas fa-tachometer-alt spec-icon"></i>
                                <strong>Kilómetros:</strong> <?php echo number_format($selectedCar['kilometros'], 0, ',', '.'); ?> km
                            </li>
                            <li>
                                <i class="fas fa-gas-pump spec-icon"></i>
                                <strong>Combustible:</strong> <?php echo $selectedCar['combustible']; ?>
                            </li>
                            <li>
                                <i class="fas fa-bolt spec-icon"></i>
                                <strong>Potencia:</strong> <?php echo $selectedCar['potencia']; ?> CV
                            </li>
                            <li>
                                <i class="fas fa-palette spec-icon"></i>
                                <strong>Color:</strong> <?php echo $selectedCar['color']; ?>
                            </li>
                        </ul>
                        
                        <div class="mt-4">
                            <h5 class="blue-text">Descripción</h5>
                            <p><?php echo $selectedCar['descripcion']; ?></p>
                        </div>
                        
                        <div class="mt-4">
                            <?php if (isset($_SESSION['usuario'])): ?>
                                <button class="btn btn-premium w-100">
                                    <i class="fas fa-shopping-cart me-2"></i> Comprar ahora
                                </button>
                            <?php else: ?>
                                <a href="?form=login" class="btn btn-premium w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i> Inicia sesión para comprar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Contenido Principal (solo si no estamos en login/register o detalles de coche) -->
<?php if (!$showLogin && !$showRegister && !$showCarDetails): ?>

<!-- Sobre Nosotros -->
<section class="py-5 bg-dark bg-opacity-50" id="nosotros">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1632&q=80" 
                     alt="Showroom" class="img-fluid rounded hero-card">
            </div>
            <div class="col-lg-6">
                <h2 class="section-title blue-text">Sobre <span class="text-white">Nosotros</span></h2>
                <p class="lead">
                    Con más de 20 años de experiencia en el mercado de automóviles de lujo, Concesionario Xuri se ha consolidado como el referente indiscutible para los amantes de la excelencia automotriz.
                </p>
                <p>
                    Nuestra pasión por los vehículos de alta gama nos ha llevado a curar una colección exclusiva que combina rendimiento, innovación y diseño atemporal. Cada modelo en nuestro showroom ha sido seleccionado bajo estrictos criterios de calidad y exclusividad.
                </p>
                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <i class="fas fa-trophy feature-icon me-4"></i>
                            <div>
                                <h4 class="blue-text">Premios</h4>
                                <p>Ganadores del "Mejor Concesionario de Lujo" 5 años consecutivos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <i class="fas fa-star feature-icon me-4"></i>
                            <div>
                                <h4 class="blue-text">Experiencia</h4>
                                <p>+5,000 clientes satisfechos en toda Europa</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <i class="fas fa-shield-alt feature-icon me-4"></i>
                            <div>
                                <h4 class="blue-text">Garantía</h4>
                                <p>3 años de garantía extendida en todos nuestros vehículos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <i class="fas fa-hand-holding-usd feature-icon me-4"></i>
                            <div>
                                <h4 class="blue-text">Financiación</h4>
                                <p>Planes personalizados para cada cliente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vehículos Destacados -->
<section class="py-5" id="vehiculos">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title blue-text">Nuestros <span class="text-white">Vehículos</span></h2>
            <p class="lead">Descubre nuestra exclusiva selección de automóviles de lujo</p>
        </div>
        <div class="row g-4">
            <?php
            // Seleccionamos 4 coches de lujo de la base de datos
            $sql = "SELECT * FROM coches ORDER BY RAND() LIMIT 4";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Asegurarnos de que la ruta de la imagen es correcta
                    $imagen_ruta = 'img/' . htmlspecialchars($row['imagen']);
                    $imagen_default = 'img/default_car.jpg'; // Imagen por defecto si no existe
                    
                    // Verificar si la imagen existe, si no usar una por defecto
                    $imagen_final = file_exists($imagen_ruta) ? $imagen_ruta : $imagen_default;
                    
                    echo '
                    <div class="col-md-6 col-lg-3">
                        <div class="car-card h-100">
                            <img src="'.$imagen_final.'" alt="'.htmlspecialchars($row['modelo']).'" class="card-img-top car-img">
                            <div class="p-4">
                                <h4 class="blue-text">'.htmlspecialchars($row['modelo']).'</h4>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="blue-text fw-bold">'.number_format($row['precio'], 0, ',', '.').' €</span>
                                    <a href="?car_id='.$row['id'].'" class="btn btn-sm btn-outline-premium">Detalles</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="col-12 text-center"><p>No hay vehículos disponibles en este momento.</p></div>';
            }
            ?>
        </div>
        <div class="text-center mt-5">
            <a href="?form=login" class="btn btn-premium btn-lg">
                <i class="fas fa-car me-2"></i>Ver Todos los Modelos
            </a>
        </div>
    </div>
</section>

<!-- Servicios -->
<section class="py-5 bg-dark bg-opacity-50" id="servicios">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title blue-text">Nuestros <span class="text-white">Servicios</span></h2>
            <p class="lead">Experiencias personalizadas para clientes exclusivos</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="hero-card h-100 p-4 text-center">
                    <i class="fas fa-search-dollar feature-icon"></i>
                    <h4 class="blue-text">Búsqueda Personalizada</h4>
                    <p>Nuestros expertos localizarán el vehículo de tus sueños, incluso si no está en nuestro inventario actual.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hero-card h-100 p-4 text-center">
                    <i class="fas fa-exchange-alt feature-icon"></i>
                    <h4 class="blue-text">Programa de Intercambio</h4>
                    <p>Actualiza tu vehículo con nuestro exclusivo programa de intercambio con valor garantizado.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hero-card h-100 p-4 text-center">
                    <i class="fas fa-user-tie feature-icon"></i>
                    <h4 class="blue-text">Asesoría Exclusiva</h4>
                    <p>Acceso a nuestros especialistas en vehículos de lujo para asesoramiento personalizado.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hero-card h-100 p-4 text-center">
                    <i class="fas fa-plane feature-icon"></i>
                    <h4 class="blue-text">Entrega Internacional</h4>
                    <p>Gestión completa de envío y documentación para clientes internacionales.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hero-card h-100 p-4 text-center">
                    <i class="fas fa-spa feature-icon"></i>
                    <h4 class="blue-text">Detallado Premium</h4>
                    <p>Servicio de detailing de nivel concours para mantener tu vehículo en estado de exhibición.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hero-card h-100 p-4 text-center">
                    <i class="fas fa-key feature-icon"></i>
                    <h4 class="blue-text">Pruebas Privadas</h4>
                    <p>Experiencias de conducción privadas en ubicaciones exclusivas.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonios -->
<section class="py-5" id="testimonios">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title blue-text">Testimonios de <span class="text-white">Clientes</span></h2>
            <p class="lead">Lo que dicen nuestros distinguidos clientes</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card h-100">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Cliente" class="testimonial-img me-3">
                        <div>
                            <h5 class="blue-text mb-0">Alejandro </h5>
                            <small>CEO, TechGlobal</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        "La experiencia con Concesionario Xuri superó todas mis expectativas. Encontraron exactamente el Ferrari que buscaba y el proceso fue impecable."
                    </p>
                    <div class="mt-3 blue-text">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card h-100">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Cliente" class="testimonial-img me-3">
                        <div>
                            <h5 class="blue-text mb-0">Erik</h5>
                            <small>Directora, Luxury Investments</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        "Como coleccionista, aprecio su conocimiento experto y discreción. Mi Rolls-Royce Phantom llegó en condiciones perfectas."
                    </p>
                    <div class="mt-3 blue-text">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card h-100">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Cliente" class="testimonial-img me-3">
                        <div>
                            <h5 class="blue-text mb-0">Hector</h5>
                            <small>Fundador, Venture Capital</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        "El servicio postventa es excepcional. Dos años después de mi compra, siguen atendiendo cada detalle con esmero."
                    </p>
                    <div class="mt-3 blue-text">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contacto -->
<section class="py-5 bg-dark bg-opacity-50" id="contacto">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="section-title blue-text mb-5">Contacta con <span class="text-white">Nosotros</span></h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="hero-card p-3 h-100">
                            <i class="fas fa-map-marker-alt feature-icon"></i>
                            <h4 class="blue-text">Ubicación</h4>
                            <p>Avenida del Lujo, 123<br>Madrid, España</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="hero-card p-3 h-100">
                            <i class="fas fa-phone feature-icon"></i>
                            <h4 class="blue-text">Teléfono</h4>
                            <p>+34 910 123 456<br>Lunes - Viernes: 9am - 7pm</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="hero-card p-3 h-100">
                            <i class="fas fa-envelope feature-icon"></i>
                            <h4 class="blue-text">Email</h4>
                            <p>info@concesionarioxuri.com<br>atención 24/7</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 form-container mx-auto">
                    <h4 class="blue-text text-center mb-4">¿Tienes alguna pregunta?</h4>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <?php if (!isset($_SESSION['usuario'])): ?>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <?php endif; ?>
                            <div class="<?php echo isset($_SESSION['usuario']) ? 'col-12' : 'col-md-6'; ?>">
                                <input type="text" name="asunto" class="form-control" placeholder="Asunto" required>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" name="mensaje" rows="4" placeholder="Mensaje" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="contact" class="btn btn-premium w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Enviar Mensaje
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Mensajes -->
                <br>
                <?php if ($mensaje): ?>
                    <div class="container mb-4">
                        <div class="alert alert-info animate__animated animate__fadeIn">
                            <?php echo $mensaje; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<!-- Footer -->
<footer class="py-5 bg-dark">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h3 class="logo mb-3">
                    <i class="fas fa-car me-2"></i>Concesionario <span>Xuri</span>
                </h3>
                <p>
                    Líderes en automóviles de lujo desde 2003. Ofrecemos una experiencia de compra exclusiva y personalizada para los clientes más exigentes.
                </p>
                <div class="mt-4">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                <h5 class="blue-text mb-4">Enlaces Rápidos</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="?" class="text-white">Inicio</a></li>
                    <li class="mb-2"><a href="#vehiculos" class="text-white">Vehículos</a></li>
                    <li class="mb-2"><a href="#servicios" class="text-white">Servicios</a></li>
                    <li class="mb-2"><a href="#testimonios" class="text-white">Testimonios</a></li>
                    <li><a href="#contacto" class="text-white">Contacto</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                <h5 class="blue-text mb-4">Horario</h5>
                <ul class="list-unstyled text-white">
                    <li class="mb-2">Lunes - Viernes: 9am - 7pm</li>
                    <li class="mb-2">Sábados: 10am - 5pm</li>
                    <li>Domingos: Cerrado</li>
                </ul>
                <h5 class="blue-text mt-4 mb-2">Newsletter</h5>
                <div class="d-flex">
                    <input type="email" class="form-control form-control-sm" placeholder="Tu email">
                    <button class="btn btn-premium btn-sm ms-2">OK</button>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="blue-text mb-4">Contacto</h5>
                <address class="text-white">
                    <p><i class="fas fa-map-marker-alt blue-text me-2"></i> Avenida del Lujo, 123<br>Madrid, España</p>
                    <p><i class="fas fa-phone blue-text me-2"></i> +34 910 123 456</p>
                    <p><i class="fas fa-envelope blue-text me-2"></i> info@concesionarioxuri.com</p>
                </address>
            </div>
        </div>
        <hr class="my-4 bg-secondary">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">
                    &copy; 2025 Concesionario Xuri. Todos los derechos reservados.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0">
                    Diseñado con <i class="fas fa-heart text-danger"></i> por tu equipo
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Efecto de escritura para el título
    document.addEventListener('DOMContentLoaded', function() {
        const title = document.querySelector('.logo');
        title.classList.add('animate__animated', 'animate__fadeInDown');
        
        // Cambiar clase activa en navbar al hacer scroll
        const sections = document.querySelectorAll('section');
        const navItems = document.querySelectorAll('.nav-link');
        
        window.addEventListener('scroll', function() {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (pageYOffset >= (sectionTop - 300)) {
                    current = section.getAttribute('id');
                }
            });
            
            navItems.forEach(item => {
                item.classList.remove('active-link');
                if (item.getAttribute('href') === '#' + current || 
                    item.getAttribute('href').includes('#' + current)) {
                    item.classList.add('active-link');
                }
            });
        });
    });
</script>
</body>
</html>