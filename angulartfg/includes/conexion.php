<?php
//usuario admin  suri@gmail.com psswrd toor
//usuario normal suri@gmail.com psswrd toor
$host = "localhost:3310";
$user = "root";
$password = "";
$database = "xuri_concesionario1";

$conn = new mysqli($host, $user, $password, $database);

if($conn->connect_error){
    die("Error de conexión: ". $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
