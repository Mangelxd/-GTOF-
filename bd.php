<?php
$serverName = "";

// Conexión a EXTRAS_TEST (datos de trazabilidad, OF, etc.)
$connectionExtras = [
    "Database" => "EXTRAS_TEST",
    "Uid" => "",
    "PWD" => "",
    "Encrypt" => "no",
    "TrustServerCertificate" => "yes"
];
$connExtras = sqlsrv_connect($serverName, $connectionExtras);

if (!$connExtras) {
    die("❌ Error conectando a EXTRAS_TEST: " . print_r(sqlsrv_errors(), true));
}

// Conexión a ES_10 (tabla OHEM para login)
$connectionUsuarios = [
    "Database" => "_ES_10",
    "Uid" => "",
    "PWD" => "",
    "Encrypt" => "no",
    "TrustServerCertificate" => "yes"
];
$connUsuarios = sqlsrv_connect($serverName, $connectionUsuarios);

if (!$connUsuarios) {
    die("❌ Error conectando a ES_10: " . print_r(sqlsrv_errors(), true));
}

// 👉 Esta es la conexión principal que usarán los archivos normales
$conn = $connExtras;
?>


