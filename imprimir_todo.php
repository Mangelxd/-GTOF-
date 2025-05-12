<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $DocNum = $_POST['DocNum'] ?? '';
    $printer_name = $_POST['printer'] ?? 'Godex_Oficina';

    if (empty($DocNum)) {
        die("❌ DocNum no proporcionado");
    }

    $serverName = "";
    $connectionOptions = [
        "Database" => "",
        "Uid" => "",
        "PWD" => "",
        "Encrypt" => "no",
        "TrustServerCertificate" => "yes"
    ];
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if (!$conn) {
        die("❌ Error conectando a la base de datos: " . print_r(sqlsrv_errors(), true));
    }

    $sql = "SELECT PartNumber, Descripcion, Ubicacion, CantidadNecesaria, Project
            FROM DHV_BK_ResgistroTrazabilidad
            WHERE DocNum = ?
            ORDER BY Id ASC";

    $params = [intval($DocNum)];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if (!$stmt) {
        die("❌ Error en la consulta: " . print_r(sqlsrv_errors(), true));
    }

    include('phpqrcode/qrlib.php');
    $folder = '/var/www/html/etiquetas/';
    if (!file_exists($folder)) {
        mkdir($folder, 0775, true);
    }

    $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
    if (!file_exists($font)) {
        die("❌ Fuente no encontrada: $font");
    }

    while ($data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $partnumber = $data['PartNumber'];
        $descripcion = $data['Descripcion'];
        $ubicacion = $data['Ubicacion'];
        $cantidad = $data['CantidadNecesaria'];
        $project = $data['Project'];

        $qr_file = $folder . 'qr_' . preg_replace('/[^A-Za-z0-9]/', '_', $partnumber) . '.png';
        QRcode::png($partnumber, $qr_file, QR_ECLEVEL_H, 6);

        $image = imagecreatetruecolor(800, 400);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, 800, 400, $white);

        $qr = imagecreatefrompng($qr_file);
        $qr_x = 120;
        $qr_y = 120;
        $qr_size = 140;
        imagecopyresampled($image, $qr, $qr_x, $qr_y, 0, 0, $qr_size, $qr_size, imagesx($qr), imagesy($qr));

        // Mostrar DocNum centrado debajo del QR
        $font_size_docnum = 15;
        $docnum_text = "OF: $DocNum";
        $bbox = imagettfbbox($font_size_docnum, 0, $font, $docnum_text);
        $text_width = $bbox[2] - $bbox[0];
        $docnum_x = $qr_x + ($qr_size / 2) - ($text_width / 2);
        $docnum_y = $qr_y + $qr_size + 25;
        imagettftext($image, $font_size_docnum, 0, $docnum_x, $docnum_y, $black, $font, $docnum_text);

        // Texto principal
        $start_x = 280;
        $line_y = 150;
        $line_spacing = 30;

        imagettftext($image, 20, 0, $start_x, $line_y, $black, $font, $partnumber);
        imagettftext($image, 20, 0, $start_x, $line_y + $line_spacing, $black, $font, $ubicacion);

        $descripcion_wrapped = wordwrap($descripcion, 20, "\n", true);
        $descripcion_lineas = explode("\n", $descripcion_wrapped);
        $descripcion_base_y = $line_y + 2 * $line_spacing;
        foreach ($descripcion_lineas as $i => $linea) {
            imagettftext($image, 20, 0, $start_x, $descripcion_base_y + ($i * $line_spacing), $black, $font, $linea);
        }

        $cantidad_y = $descripcion_base_y + (count($descripcion_lineas) * $line_spacing);
        imagettftext($image, 20, 0, $start_x, $cantidad_y, $black, $font, "Cantidad: $cantidad");
        imagettftext($image, 20, 0, $start_x, $cantidad_y + $line_spacing, $black, $font, "Pro: $project");

        $etiqueta_file = $folder . 'etiqueta_' . preg_replace('/[^A-Za-z0-9]/', '_', $partnumber) . '.png';
        imagepng($image, $etiqueta_file);
        imagedestroy($image);

        exec("lp -d $printer_name -o fit-to-page $etiqueta_file", $output, $return_var);
    }

    $impresora = htmlspecialchars($printer_name);
    $DocNum_encoded = urlencode($DocNum);
    echo '<script>
        alert("✅ Se han enviado todas las etiquetas a la impresora ' . $impresora . '");
        window.location.href = "index.php?of=' . $DocNum_encoded . '";
    </script>';
}
?>
