<?php
require_once 'includes/conexion.php';
session_start();
header('Content-Type: application/json');

// Inicializar
$msg = strtolower(trim($_POST['mensaje'] ?? ''));
$state = json_decode($_POST['state'] ?? '{}', true);
$response = '';
$email = $_SESSION['email'] ?? 'invitado';

// Función para validar números positivos
function validarNumero($input) {
    return is_numeric($input) && intval($input) > 0;
}

switch ($state['step'] ?? 'init') {
    case 'init':
        if (strpos($msg, '1') !== false || strpos($msg, 'buscar') !== false) {
            $state['step'] = 'price_from';
            $response = "¿Cuál es el precio **mínimo** del coche que buscas? (Ej: 8000)";
        } elseif (strpos($msg, '2') !== false || strpos($msg, 'problema') !== false) {
            $state['step'] = 'problem_desc';
            $response = "Describe brevemente el problema que estás teniendo.";
        } else {
            $response = "Por favor, elige una opción:\n1. Buscar coche\n2. Reportar un problema\n3. Otra consulta";
        }
        break;

    case 'price_from':
        if (validarNumero($msg)) {
            $state['priceFrom'] = intval($msg);
            $state['step'] = 'price_to';
            $response = "¿Y cuál es el precio **máximo**?";
        } else {
            $response = "Introduce un número válido. ¿Cuál es el precio mínimo?";
        }
        break;

    case 'price_to':
        if (validarNumero($msg)) {
            $state['priceTo'] = intval($msg);
            $from = $state['priceFrom'];
            $to = $state['priceTo'];
            if ($from >= $to) {
                $response = "El precio máximo debe ser mayor que el mínimo. Intenta de nuevo.";
                $state['step'] = 'price_to';
            } else {
                // Aquí podrías hacer una consulta real a la BBDD
                $response = "Mostrando coches entre $from€ y $to€:\n- Toyota Corolla 12.000€\n- Renault Clio 9.500€\n(Puedes ver más en nuestro catálogo).";
                $state = ['step' => 'init'];
            }
        } else {
            $response = "Introduce un número válido. ¿Cuál es el precio máximo?";
        }
        break;

    case 'problem_desc':
        if (strlen($msg) < 10) {
            $response = "Describe el problema con un poco más de detalle, por favor.";
        } else {
            $state['descripcion'] = $msg;
            $state['step'] = 'contacto';
            $response = "Gracias. ¿Cómo podemos contactarte? (Escribe tu email o teléfono)";
        }
        break;

    case 'contacto':
        $descripcion = strip_tags($state['descripcion']);
        $contacto = strip_tags($msg);
        $incidencia = "Problema: $descripcion\nContacto: $contacto";

        try {
            $stmt = $conn->prepare("INSERT INTO incidencias (usuario_email, mensaje) VALUES (?, ?)");
            if (!$stmt) throw new Exception("Error en prepare()");
            $stmt->bind_param("ss", $email, $incidencia);
            $stmt->execute();
            $stmt->close();

            $response = "Gracias. Tu incidencia ha sido registrada correctamente. Te contactaremos pronto.";
        } catch (Exception $e) {
            $response = "Hubo un error al registrar tu incidencia. Por favor, intenta más tarde.";
        }

        $state = ['step' => 'init'];
        break;

    default:
        $response = "No entendí eso. Elige una opción:\n1. Buscar coche\n2. Reportar un problema";
        $state['step'] = 'init';
}

echo json_encode(['respuesta' => $response, 'state' => $state]);
