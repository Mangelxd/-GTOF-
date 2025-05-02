<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];

    // Datos del servidor LDAP/AD
    $ldap_server = "ldap://192.168.3.10"; // IP o nombre del servidor AD
    $ldap_domain = "asir.local";          // Tu dominio
    $ldap_dn_base = "DC=asir,DC=local";   // Base DN

    $ldap_user = "$ldap_domain\\$usuario"; // Ejemplo: asir.local\\jlopez

    $ldap_conn = ldap_connect($ldap_server);
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (@ldap_bind($ldap_conn, $ldap_user, $password)) {
        // Buscar datos del usuario
        $filter = "(sAMAccountName=$usuario)";
        $result = ldap_search($ldap_conn, $ldap_dn_base, $filter);
        $entries = ldap_get_entries($ldap_conn, $result);

        $_SESSION["usuario"] = $usuario;
        $_SESSION["nombreCompleto"] = $entries[0]["cn"][0] ?? $usuario;
        header("Location: index.php");
        exit();
    } else {
        $error = " Usuario o contraseña incorrectos en AD.";
    }
}
?>
