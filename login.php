<?php
session_start();
require_once "bd.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"] ?? "";
    $password = $_POST["password"] ?? "";

    $sql = "SELECT U_SEIUserWeb AS empleado, U_SEIPassWeb AS password, firstName, middleName, lastName 
            FROM dbo.OHEM 
            WHERE U_SEIUserWeb = ?";
    $params = [$usuario];
    $stmt = sqlsrv_query($connUsuarios, $sql, $params);

    if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
        if (rtrim($row["password"]) === $password) {
            $_SESSION["usuario"] = $row["empleado"];
            $_SESSION["nombreCompleto"] = trim($row["firstName"] . ' ' . $row["lastName"]);
            header("Location: index.php");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Iniciar sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <img src="" alt="Logo empresa" />
        <h1>Mi Empresa</h1>
    </header>

    <h2>Iniciar sesión</h2>

    <form method="post">
        <?php if (!empty($error)): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" id="usuario" required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
