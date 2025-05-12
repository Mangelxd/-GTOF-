<?php
require 'vendor/autoload.php'; // Incluye PHP QR Code y GD Fonts si es necesario

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partNumber = $_POST['partNumber'] ?? 'UNKNOWN';
    $descripcion = $_POST['descripcion'] ?? 'No description';
    $ubicacion = $_POST['ubicacion'] ?? 'No location';
    $cantidad = $_POST['cantidad'] ?? '0';

    $fecha = date("d-m-Y");
    $nombreArchivo = $partNumber . "_" . $fecha . ".png";
    $carpeta = __DIR__ . "/etiquetas_creadas";

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $ancho = 632;
    $alto = 312;
    $img = imagecreate($ancho, $alto);
    $blanco = imagecolorallocate($img, 255, 255, 255);
    $negro = imagecolorallocate($img, 0, 0, 0);

    $qr = QrCode::create($partNumber)->setSize(100);
    $writer = new PngWriter();
    $result = $writer->write($qr);

    $tmpQrPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    $result->saveToFile($tmpQrPath);
    $qrImg = imagecreatefrompng($tmpQrPath);

    imagecopy($img, $qrImg, 20, 100, 0, 0, imagesx($qrImg), imagesy($qrImg));

    $font = 3;
    $xText = 150;
    $yText = 100;
    imagestring($img, $font, $xText, $yText, "PatNum: $partNumber", $negro);
    imagestring($img, $font, $xText, $yText+20, "Descripcion: " . substr($descripcion, 0, 33), $negro);
    imagestring($img, $font, $xText, $yText+40, substr($descripcion, 33), $negro);
    imagestring($img, $font, $xText, $yText+60, "Ubicacion: $ubicacion", $negro);
    imagestring($img, $font, $xText, $yText+80, "Cantidad Necesaria: $cantidad", $negro);

    $rutaFinal = "$carpeta/$nombreArchivo";
    imagepng($img, $rutaFinal);

    imagedestroy($img);
    unlink($tmpQrPath);

    echo "<p>Etiqueta generada: <a href='etiquetas_creadas/$nombreArchivo' target='_blank'>$nombreArchivo</a></p>";
}
?>