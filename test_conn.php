<?php
$serverName = "192.168.3.54"; // Cambia a la IP de tu servidor SQL Server

$connectionOptions = array(
    "Database" => "DHV_EXTRAS_TEST",
    "UID" => "dhv",
    "PWD" => "dhv",
    "Encrypt" => "false", // <-- CAMBIA esta línea
    "TrustServerCertificate" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    echo "✅ Conexión exitosa!";
} else {
    echo "❌ Conexión fallida:<br>";
    print_r(sqlsrv_errors());
}
?>
