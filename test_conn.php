<?php
$serverName = ""; // Cambia a la IP de tu servidor SQL Server

$connectionOptions = array(
    "Database" => "",
    "UID" => "",
    "PWD" => "",
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
