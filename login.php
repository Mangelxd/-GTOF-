<?php
ob_start(); // ¡Agregado para evitar problemas con header()!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"] ?? '';
    $password = $_POST["password"] ?? '';

    $ldap_server = "ldap://192.168.1.100";
    $ldap_domain = "GTOF.local";
    $ldap_dn_base = "DC=GTOF,DC=local";
    $ldap_user = "$usuario@GTOF.local";

    $ldap_conn = ldap_connect($ldap_server);
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (!$ldap_conn) {
        $error = "❌ No se pudo conectar al servidor LDAP.";
    } else {
        if (@ldap_bind($ldap_conn, $ldap_user, $password)) {
            $filter = "(sAMAccountName=$usuario)";
            $attributes = ["cn", "mail"];
            $search = ldap_search($ldap_conn, $ldap_dn_base, $filter, $attributes);

            if (!$search) {
                $error = "❌ Falló la búsqueda LDAP. " . ldap_error($ldap_conn);
            } else {
                $entries = ldap_get_entries($ldap_conn, $search);
                if ($entries["count"] == 0) {
                    $error = "⚠️ Usuario autenticado pero no se encontraron datos.";
                } else {
                    $_SESSION["usuario"] = $usuario;
                    $_SESSION["nombreCompleto"] = $entries[0]["cn"][0] ?? $usuario;
                    $_SESSION["correo"] = $entries[0]["mail"][0] ?? "";
                    ldap_unbind($ldap_conn);
                    header("Location: index.php");
                    exit;
                }
            }
        } else {
            $error = "❌ Usuario o contraseña incorrectos. (" . ldap_error($ldap_conn) . ")";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login AD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar sesión en GTOF</h2>
        <form method="post" action="login.php">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar sesión</button>
        </form>
        <?php if (isset($error)) echo "<p class='error' style='color:red;'>$error</p>"; ?>
    </div>
</body>
</html>
