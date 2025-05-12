<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"] ?? '';
    $password = $_POST["password"] ?? '';

    // Configuración del servidor Active Directory
    $ldap_server = "ldap://192.168.3.10";         // IP o nombre del servidor AD
    $ldap_domain = "asir.local";                 // Dominio (FQDN)
    $ldap_dn_base = "DC=asir,DC=local";          // Distinguished Name base de búsqueda

    // Formato del usuario para la conexión
    $ldap_user = "$ldap_domain\\$usuario";       // Ej: asir.local\\jlopez

    // Conexión LDAP
    $ldap_conn = ldap_connect($ldap_server);
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (@ldap_bind($ldap_conn, $ldap_user, $password)) {
        // Autenticación exitosa, ahora buscamos datos del usuario
        $filter = "(sAMAccountName=$usuario)";
        $attributes = ["cn", "mail"];
        $search = ldap_search($ldap_conn, $ldap_dn_base, $filter, $attributes);
        $entries = ldap_get_entries($ldap_conn, $search);

        // Guardamos la sesión del usuario
        $_SESSION["usuario"] = $usuario;
        $_SESSION["nombreCompleto"] = $entries[0]["cn"][0] ?? $usuario;
        $_SESSION["correo"] = $entries[0]["mail"][0] ?? "";

        ldap_unbind($ldap_conn); // Cerramos la conexión LDAP

        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos en Active Directory.";
    }
}
?>

<!-- Formulario simple -->
<!DOCTYPE html>
<html>
<head>
    <title>Login AD</title>
</head>
<body>
    <h2>Iniciar sesión con Active Directory</h2>
    <form method="post" action="login.php">
        <input type="text" name="usuario" placeholder="Usuario" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <button type="submit">Iniciar sesión</button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
