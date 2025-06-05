<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $DocNum = $_POST['DocNum'] ?? '';
    $printer_name = $_POST['printer'] ?? 'Godex_Oficina';

    if (empty($DocNum)) {
        die("❌ DocNum no proporcionado.");
    }

    $serverName = "192.168.1.100";
    $connectionOptions = [
        "Database" => "EXTRAS_TEST",
        "Uid" => "usuario",
        "PWD" => "Usu@rio123!",
        "Encrypt" => "no",
        "TrustServerCertificate" => "yes"
    ];
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if (!$conn) {
        die("❌ Error conectando a la base de datos: " . print_r(sqlsrv_errors(), true));
    }

    // Ejecutar procedimiento
    $sql = "SELECT DocNum, Proyecto, Descripcion, Caja, Ubicacion
        FROM EtiquetaCaja
        WHERE DocNum = ?";
    $stmt = sqlsrv_query($conn, $sql, [$bDocNum]);

    if (!$stmt) {
        die("❌ Error ejecutando el procedimiento: " . print_r(sqlsrv_errors(), true));
    }

    // Seleccionar la caja con ubicación más baja
    $data = null;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($data === null || strcmp($row['CodigoUbicacion'], $data['CodigoUbicacion']) < 0) {
            $data = $row;
        }
    }

    if (!$data) {
        die("❌ No se encontraron cajas para la OF '$DocNum'.");
    }

    $ubicacion = $data['CodigoUbicacion'];
    $producto = $data['Producto'];
    $project = $data['Project'];
    $numCaja = $data['NumCaja'];

    $folder = '/var/www/html/etiquetas/';
    if (!file_exists($folder)) mkdir($folder, 0775, true);

    include('phpqrcode/qrlib.php');
    $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
    if (!file_exists($font)) die("❌ Fuente no encontrada: $font");

    // === ETIQUETA 1: UBICACIÓN ===
    $img1 = imagecreatetruecolor(600, 300);
    $white = imagecolorallocate($img1, 255, 255, 255);
    $black = imagecolorallocate($img1, 0, 0, 0);
    imagefilledrectangle($img1, 0, 0, 600, 300, $white);

    imagettftext($img1, 18, 0, 200, 100, $black, $font, "UBICACIÓN");
    imagettftext($img1, 30, 0, 180, 180, $black, $font, $ubicacion);

    $file1 = $folder . 'ubicacion_' . preg_replace('/[^A-Za-z0-9]/', '_', $ubicacion) . '.png';
    imagepng($img1, $file1);
    imagedestroy($img1);

    // === ETIQUETA 2: CAJA (enmarcada) ===
    $img2 = imagecreatetruecolor(600, 400);
    imagefilledrectangle($img2, 0, 0, 600, 400, $white);


    $x = 200;
    $y = 150;
    $line = 60;

    imagettftext($img2, 20, 0, $x, $y, $black, $font, "OF: $DocNum");
    imagettftext($img2, 20, 0, $x, $y + $line, $black, $font, "$project");
    imagettftext($img2, 20, 0, $x, $y + 2 * $line, $black, $font, "$producto");
    imagettftext($img2, 20, 0, $x, $y + 3 * $line, $black, $font, "Caja: $numCaja");

    $file2 = $folder . 'caja_' . preg_replace('/[^A-Za-z0-9]/', '_', $producto) . '.png';
    imagepng($img2, $file2);
    imagedestroy($img2);

    // Imprimir ambas etiquetas
    exec("lp -d $printer_name -o fit-to-page $file1", $out1, $ret1);
    exec("lp -d $printer_name -o fit-to-page $file2", $out2, $ret2);

    if ($ret1 !== 0 || $ret2 !== 0) {
        echo "<script>
            alert('❌ Error al imprimir una o ambas etiquetas.');
            window.location.href = 'index.php?DocNum=" . urlencode($DocNum) . "';
        </script>";
    } else {
        echo "<script>
            alert('✅ Etiquetas enviadas a \"$printer_name\".');
            window.location.href = 'index.php?DocNum=" . urlencode($DocNum) . "';
        </script>";
    }
}
?>
