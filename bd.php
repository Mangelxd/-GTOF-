<?php
$serverName = "";

// Conexión  a la tabla (datos de trazabilidad, OF, etc.)
$connectionExtras = [
    "Database" => "Nombre de la bd",
    "Uid" => "username",
    "PWD" => "password",
    "Encrypt" => "no",
    "TrustServerCertificate" => "yes"
];
$connExtras = sqlsrv_connect($serverName, $connectionExtras);

if (!$connExtras) {
    die(" Error conectando a DHV_EXTRAS_TEST: " . print_r(sqlsrv_errors(), true));
}

// Conexión a  (tabla OHEM para login)
$connectionUsuarios = [
    "Database" => "bd",
    "Uid" => "username",
    "PWD" => "password",
    "Encrypt" => "no",
    "TrustServerCertificate" => "yes"
];
$connUsuarios = sqlsrv_connect($serverName, $connectionUsuarios);

if (!$connUsuarios) {
    die("Error conectando de conexion: " . print_r(sqlsrv_errors(), true));
}

//  Esta es la conexión principal que usarán los archivos normales
$conn = $connExtras;
?>
