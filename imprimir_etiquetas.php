<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partnumber = $_POST['partnumber'] ?? '';

    // Configuración de la base de datos
    $serverName = "";
    $connectionOptions = [
        "Database" => "bd",
        "Uid" => "user",
        "PWD" => "password",
        "Encrypt" => "no",
        "TrustServerCertificate" => "yes"
    ];

    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if (!$conn) {
        die(" Error conectando a DHV_EXTRAS_TEST: " . print_r(sqlsrv_errors(), true));
    }

    // Consulta de los datos
    $sql = "SELECT TOP 1 PartNumber, Descripcion, Ubicacion, CantidadNecesaria, Project FROM DHV_BK_RegistroTrazabilidad WHERE PartNumber = ? ORDER BY Id DESC";
    $stmt = sqlsrv_query($conn, $sql, [$partnumber]);
    if (!$stmt) {
        die(" Error en la consulta: " . print_r(sqlsrv_errors(), true));
    }

    $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if (!$data) {
        die(" No se encontraron datos para el PartNumber.");
    }

    $descripcion = $data['Descripcion'];
    $ubicacion = $data['Ubicacion'];
    $cantidad = $data['CantidadNecesaria'];
    $project = $data['Project'];

    // Ruta de almacenamiento
    $folder = '/var/www/html/etiquetas/';
    if (!file_exists($folder)) {
        if (!mkdir($folder, 0775, true)) {
            die(" No se pudo crear la carpeta de etiquetas: $folder");
        }
    }

    // Generar QR (usando PHP QR Code)
    include('phpqrcode/qrlib.php');
    $qr_file = $folder . 'qr_' . preg_replace('/[^A-Za-z0-9]/', '_', $partnumber) . '.png';
    QRcode::png($partnumber, $qr_file, QR_ECLEVEL_H, 3);

    // Crear imagen
    $image = imagecreatetruecolor(620, 300);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefilledrectangle($image, 0, 0, 620, 300, $white);

    $qr = imagecreatefrompng($qr_file);
    imagecopyresampled($image, $qr, 20, 20, 0, 0, 100, 100, imagesx($qr), imagesy($qr));

    $font = __DIR__ . '/DejaVuSans-Bold.ttf';

    // Texto
    imagettftext($image, 18, 0, 140, 40, $black, $font, $project);
    imagettftext($image, 18, 0, 140, 70, $black, $font, $descripcion);
    imagettftext($image, 18, 0, 140, 100, $black, $font, $ubicacion);
    imagettftext($image, 18, 0, 140, 130, $black, $font, "Cantidad: $cantidad");
    imagettftext($image, 20, 0, 20, 180, $black, $font, $partnumber);

    $etiqueta_file = $folder . 'etiqueta_' . preg_replace('/[^A-Za-z0-9]/', '_', $partnumber) . '.png';
    imagepng($image, $etiqueta_file);
    imagedestroy($image);

    // Imprimir en Godex usando lpr
    $printer_name = 'Godex_Oficina';
    exec("lp -d $printer_name $etiqueta_file", $output, $return_var);

    if ($return_var !== 0) {
        echo " Error al imprimir la etiqueta.";
    } else {
        echo " Etiqueta generada y enviada a imprimir.";
    }
} else {
    echo " Método no permitido.";
}
