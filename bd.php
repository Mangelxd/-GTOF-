<?php
// Datos de conexión comunes
$host = "localhost";
$user = "root";
$password = "root";

// Conexión a la base de datos de trazabilidad
$databaseExtras = "bd_trazabilidad";
$connExtras = mysqli_connect($host, $user, $password, $databaseExtras);
if (!$connExtras) {
    die("Error conectando a $databaseExtras: " . mysqli_connect_error());
}

// Conexión a la base de datos de usuarios (por ejemplo, para login)
$databaseUsuarios = "bd_usuarios";
$connUsuarios = mysqli_connect($host, $user, $password, $databaseUsuarios);
if (!$connUsuarios) {
    die("Error conectando a $databaseUsuarios: " . mysqli_connect_error());
}

// Esta es la conexión principal que usarán los archivos normales
$conn = $connExtras;
?>
