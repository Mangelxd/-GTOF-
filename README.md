# Gestión de Trazabilidad por Orden de Fabricación

Este proyecto proporciona una solución completa para gestionar la trazabilidad de materiales según órdenes de fabricación (`DocNum`) en un entorno que simula SAP Business One. A través de una interfaz web sencilla, permite consultar, modificar e imprimir datos críticos de materiales y ubicaciones de forma centralizada.

---

## Tabla de Contenidos

- [Características](#características)
- [Funcionamiento](#funcionamiento)
- [Estructura de Datos](#estructura-de-datos)
- [Interfaz Web](#interfaz-web)
- [Requisitos Técnicos](#requisitos-técnicos)
- [Público Objetivo](#público-objetivo)

---

## Características

- Consulta rápida de materiales pendientes por orden de fabricación.
- Visualización de ubicación física en almacén.
- Asociación clara a proyectos.
- Edición directa de información por línea (cantidad, lote, ubicación, observaciones).
- Impresión de etiquetas con códigos QR.
- Registro automático en una tabla histórica sin duplicados.

---

## Funcionamiento

1. El usuario accede a la aplicación web e introduce un número de orden de fabricación.
2. Se ejecuta el procedimiento `Actualizar_M_BK_ResgistroTrazabilidad`, que extrae los datos desde la base.
3. Se rellenan o actualizan los registros en la tabla `M_BK_ResgistroTrazabilidad`.
4. Cada línea puede editarse individualmente desde la interfaz.
5. Es posible imprimir una etiqueta por material, con código QR, seleccionando la impresora.

---
## Estructura de Datos

Los procedimientos almacenados acceden a tablas simuladas del entorno SAP, replicadas en la base de datos ES_10. Debido a las restricciones de edición en la base de datos de SAP, se ha creado una base adicional denominada EXTRAS_TEST, donde se vuelcan los datos necesarios para su análisis y modificación.

![Diagrama 1](https://github.com/Mangelxd/-GTOF-/blob/main/bdTFG1.png?raw=true)
![Diagrama 2](https://github.com/Mangelxd/-GTOF-/blob/main/bdTFG2.png?raw=true)

---

## Interfaz Web

- **Pantalla principal:** búsqueda por `DocNum` y filtrado por `PartNumber`.
- **Resultados interactivos:** muestra todas las líneas de la orden con información relevante.
- **Formularios por línea:** edición de datos como ubicación, cantidad, lote y observaciones.
- **Selector de impresora:** para enviar etiquetas directamente desde la interfaz.
- **Impresión con QR:** etiquetas generadas automáticamente usando `phpqrcode`.

![InterFazWEB1](https://github.com/Mangelxd/-GTOF-/blob/main/MIPDA_TFG.png)
![InterFazWEB2](https://github.com/Mangelxd/-GTOF-/blob/main/Menu_PDA.png)
![InterFazWEB1](https://github.com/Mangelxd/-GTOF-/blob/main/Ejemplo_PDA.png)


---

### Infraestructura de red

![Plan de red](https://github.com/Mangelxd/-GTOF-/blob/main/Plan%20de%20red.png?raw=true)

### Servidor web

- Sistema operativo: **Ubuntu Server** (recomendado) o cualquier distribución Linux compatible.
- Servidor web: **Apache2** con PHP instalado.
- Extensiones de PHP necesarias:
  - `php-mbstring`
  - `php-gd`
  - `php-sqlsrv` (para conectar con SQL Server)
  - `phpqrcode` (para generar códigos QR)

### Servidor de base de datos

- **SQL Server** (puede alojarse en Windows Server)
- Base de datos: `M_EXTRAS_TEST`
- Permisos para crear procedimientos almacenados y tablas
- Configuración del puerto SQL (por defecto: 1433) y acceso desde el servidor web

### Impresión de etiquetas

- Sistema operativo compatible: **Ubuntu Server** o **Windows Server**
- Servidor de impresión instalado con soporte para CUPS (`cups` y `lp` en Linux)
- Impresoras compatibles (Godex, Zebra u otras que admitan impresión de imágenes PNG)
- Acceso a red de las impresoras desde el servidor web

### Seguridad y control de acceso

- Sistema de login con sesiones (ya integrado)
- Firewall configurado para permitir tráfico HTTP/HTTPS y SQL Server
- Opcional: VLAN o subred dedicada para el tráfico de impresión

---

### 🕓 Historial de Versiones
📌 Versión 1.0 
Fecha: Mayo 2025
Descripción:
Primera versión funcional del Sistema de Trazabilidad y Consulta de Fabricación (STCF).
Se ha desarrollado una solución web con interfaz sencilla para gestionar información de materiales basada en órdenes de fabricación, permitiendo consultar, editar e imprimir etiquetas asociadas a cada línea.
En esta versión se implementan las siguientes funcionalidades:

Login básico por usuario.

Conexión a base de datos MySQL.

Consulta dinámica por DocNum.

Visualización de materiales, cantidades y ubicaciones.

Edición en línea de campos como cantidad pendiente y observación.

Generación de etiquetas a partir de los datos obtenidos.

Estilo visual personalizado mediante style.css.

📌 Versión 2.0 – Transición a entorno local y autenticación con Active Directory
Fecha de lanzamiento: Mayo 2025
Estado: Estable

🧾 Descripción General
La versión 2.0 del proyecto STCF (Sistema de Trazabilidad y Consulta de Fabricación) representa una evolución completa respecto a la versión 1.0. El sistema ha sido rediseñado para ejecutarse de forma local en un entorno basado en XAMPP y MySQL, integrando ahora autenticación con Active Directory mediante el protocolo LDAP, lo cual habilita una gestión centralizada y segura del acceso a la plataforma.

Esta versión busca alinear el proyecto con entornos empresariales reales, donde los sistemas de trazabilidad requieren autenticación robusta y conexión con servidores de dominio.

🔄 Cambios y Mejoras en esta versión
🧠 Reestructuración técnica:
Reemplazo completo del sistema de conexión sqlsrv por mysqli (MySQL).

Separación clara entre bases de datos:

bd_trazabilidad para datos operativos.

bd_usuarios o Active Directory para la autenticación.

Adaptación de las sentencias SQL al estándar de MySQL.

🔐 Autenticación con Active Directory:
Implementación del protocolo LDAP en login.php.

Búsqueda de usuario y recuperación de atributos (cn, mail) desde el servidor de dominio.

Eliminación del login local básico de la versión anterior (opcional).

Control de errores silencioso y seguro en caso de fallos de autenticación.

💡 Nuevas funcionalidades:
Sesiones PHP seguras tras autenticación LDAP.

Visualización del nombre completo del usuario tras login.

Preparación para registro de auditoría de accesos (pendiente para v2.1).

Soporte para despliegue en redes con dominio asir.local o equivalente.

🛠️ Ajustes de compatibilidad:
Visualización de errores PHP activada en entorno local (error_reporting).

Conexión funcional en XAMPP sin contraseñas de MySQL por defecto.

Código portable entre Windows y Linux (adaptado a Apache + PHP 7.4+).


---

### 📚 Bibliografía
Documentación oficial de PHP: https://www.php.net/manual/es/

Documentación de MySQL: https://dev.mysql.com/doc/

Manual de HTML y CSS - MDN Web Docs: https://developer.mozilla.org/es/docs/Web

Fpdf para generación de PDFs en PHP: https://www.fpdf.org/

Guía de conexión PHP a MySQL (W3Schools): https://www.w3schools.com/php/php_mysql_connect.asp

SAP Business One – Guía de usuario (referencia conceptual para estructura de datos y trazabilidad): [Manual interno/no público]


---
### https://youtu.be/qVfYtmuvNuA
