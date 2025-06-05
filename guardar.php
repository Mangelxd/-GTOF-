<?php
require_once "bd.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form = $_POST;
    $cantidad_original = (int) $form['cantidad_original'];
    $cantidad_adquirir = (int) $form['cantidad_a_adquirir'];
    $nueva_cantidad = max($cantidad_original - $cantidad_adquirir, 0);

    $sql = "UPDATE M_BK_ResgistroTrazabilidad
            SET Ubicacion = ?, CantidadPendiente = ?, Observacion = ?, Lote = ?, UltimaActualizacion = GETDATE()
            WHERE Id = ?";
    $params = [
        $form['ubicacion'],
        $nueva_cantidad,
        $form['observacion'],
        $form['lote'],
        $form['id']
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    $redir = "index.php?of=" . urlencode($form['DocNum']);
    if (!empty($form['partnumber'])) {
        $redir .= "&partnumber=" . urlencode($form['partnumber']);
    }

    if ($stmt) {
        header("Location: $redir");
        exit();
    } else {
        die("❌ Error al guardar: " . print_r(sqlsrv_errors(), true));
    }
}
?>
