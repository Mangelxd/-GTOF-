<?php
session_start();
require 'bd.php';
date_default_timezone_set('Europe/Madrid');
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo "No autenticado";
    exit;
}

$accion = $_POST['accion'] ?? '';
$of = $_POST['of'] ?? '';
$usuario = $_SESSION['usuario'] ?? '';

if (!$of || !$accion) {
    http_response_code(400);
    echo "Datos incompletos";
    exit;
}

$hora = date('Y-m-d H:i:s');

$conn = $conn ?? null;
if (!$conn) {
    http_response_code(500);
    echo "Sin conexión a la base de datos.";
    exit;
}

switch ($accion) {
    case 'empezar':
        $queryCheck = "SELECT COUNT(*) AS total FROM Prod_Of WHERE usuario = ? AND hora_fin IS NULL";
        $params = [$usuario];
        $stmt = sqlsrv_query($conn, $queryCheck, $params);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($row && $row['total'] > 0) {
            echo "Ya tienes una tarea activa. Finalízala antes de comenzar otra.";
            exit;
        }

        $insert = "INSERT INTO Prod_Of (id_of, usuario, hora_inicio) VALUES (?, ?, ?)";
        sqlsrv_query($conn, $insert, [$of, $usuario, $hora]);

        $update = "UPDATE Picking_OF SET fecha_inicio = ?, estado = '1' WHERE NumOF = ? AND (estado = '0' OR estado = '3')";
        sqlsrv_query($conn, $update, [$hora, $of]);

        echo "Tarea iniciada.";
        break;

    case 'parar':
        $update = "UPDATE Prod_Of SET hora_fin = ? WHERE usuario = ? AND id_of = ? AND hora_fin IS NULL";
        $result = sqlsrv_query($conn, $update, [$hora, $usuario, $of]);

        if ($result) {
            echo "Tarea pausada.";
        } else {
            echo "Error al pausar.";
        }
        break;

    case 'cerrar':
        $queryCheck = "SELECT COUNT(*) AS total FROM Prod_Of WHERE id_of = ? AND hora_fin IS NULL";
        $params = [$of];
        $stmt = sqlsrv_query($conn, $queryCheck, $params);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($row && $row['total'] > 0) {
            echo "Otro operario está en esta tarea.";
            exit;
        }

        $update = "UPDATE Picking_OF SET fecha_fin = ?, estado = '2' WHERE NumOF = ?";
        sqlsrv_query($conn, $update, [$hora, $of]);

        echo "Tarea finalizada.";
        break;

    default:
        echo "Acción no válida.";
        break;
}
?>
