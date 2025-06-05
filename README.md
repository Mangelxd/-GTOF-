# Gestión de Trazabilidad por Orden de Fabricación

Este proyecto proporciona una solución completa para gestionar la trazabilidad de materiales según órdenes de fabricación (`DocNum`) en un entorno que simula SAP Business One. A través de una interfaz web sencilla, permite consultar, modificar e imprimir datos críticos de materiales y ubicaciones de forma centralizada.

---

## Tabla de Contenidos

- [Características](#características)
- [Funcionamiento](#funcionamiento)
- [Estructura de Datos](#estructura-de-datos)
- [Interfaz Web](#interfaz-web)
- [Requisitos Técnicos](#requisitos-técnicos)
  - [Infraestructura de red](#infraestructura-de-red)
  - [Plan de Red](#plan-de-red)
  - [Servidor web](#servidor-web)
  - [Servidor de base de datos + Active Directory](#servidor-de-base-de-datos--active-directory)
  - [Impresión de etiquetas](#impresión-de-etiquetas)
  - [Seguridad y control de acceso](#seguridad-y-control-de-acceso)
- [Historial de Versiones](#🕓-historial-de-versiones)
- [Bibliografía](#📚-bibliografía)
- [Demo del Proyecto](#🎬-demo-del-proyecto)

---

## Características

- Consulta rápida de materiales pendientes por orden de fabricación.
- Visualización de ubicación física en almacén.
- Asociación clara a proyectos.
- Edición directa de información por línea (cantidad, lote, ubicación, observaciones).
- Impresión de etiquetas con códigos QR.
- Registro automático en una tabla histórica sin duplicados.
- Identificación única por línea en formato `DocNum;N`.

---

## Funcionamiento

1. El usuario accede a la aplicación web e introduce un número de orden de fabricación.
2. Se ejecuta el procedimiento `Actualizar_M_BK_ResgistroTrazabilidad`, que extrae los datos desde la base.
3. Se rellenan o actualizan los registros en la tabla `M_BK_ResgistroTrazabilidad`.
4. Cada línea puede editarse individualmente desde la interfaz.
5. Es posible imprimir una etiqueta por material, con código QR, seleccionando la impresora.

---

## Estructura de Datos

Los procedimientos almacenados acceden a tablas simuladas del entorno SAP, replicadas en la base de datos `ES_10`.  
Dado que SAP no permite la modificación directa de sus tablas, se ha creado una base de datos complementaria denominada `EXTRAS_TEST`, en la cual se vuelcan los datos necesarios para su consulta y edición.

![Estructura SAP 1](https://github.com/Mangelxd/-GTOF-/blob/main/bdTFG1.png?raw=true)
![Estructura SAP 2](https://github.com/Mangelxd/-GTOF-/blob/main/bdTFG2.png?raw=true)

---

## Interfaz Web

- **Pantalla principal:** búsqueda por `DocNum` y filtrado por `PartNumber`.
- **Resultados interactivos:** muestra todas las líneas de la orden con información relevante.
- **Formularios por línea:** edición de datos como ubicación, cantidad, lote y observaciones.
- **Selector de impresora:** para enviar etiquetas directamente desde la interfaz.
- **Impresión con QR:** etiquetas generadas automáticamente usando `phpqrcode`.

![Plan de red](https://github.com/Mangelxd/-GTOF-/blob/main/MIPDA_TFG.png)
![Plan de red](https://github.com/Mangelxd/-GTOF-/blob/main/Menu_PDA.png)
![Plan de red](https://github.com/Mangelxd/-GTOF-/blob/main/Ejemplo_PDA.png)


---

### Infraestructura de red

- **Servidor DNS** configurado (recomendado en Windows Server o Bind9 en Linux) para resolución de nombres local.
- **Servidor DHCP** para asignación automática de direcciones IP (opcional si se usa direccionamiento estático).
- Conectividad en red local (LAN) entre los equipos cliente, el servidor web y el servidor de base de datos.

### Plan de Red

![Plan de red](https://github.com/Mangelxd/-GTOF-/blob/main/Plan%20de%20red.png?raw=true)

### Servidor web

- Sistema operativo: **Ubuntu Server** (recomendado) o cualquier distribución Linux compatible.
- Servidor web: **Apache2** con PHP instalado.
- Extensiones de PHP necesarias:
  - `php-mbstring`
  - `php-gd`
  - `php-sqlsrv` (para conectar con SQL Server)
  - `phpqrcode` (para generar códigos QR)

### Servidor de base de datos + Active Directory

- **SQL Server** (puede alojarse en Windows Server o en una máquina virtual dedicada).
- **Base de datos principal:** `M_EXTRAS_TEST`, que almacena los datos de trazabilidad operativa.
- **Permisos necesarios:**
  - Creación y ejecución de procedimientos almacenados.
  - Lectura y escritura sobre las tablas de operación y trazabilidad.
- **Configuración de red:**
  - Habilitación del puerto **1433** (por defecto) en el firewall.
  - Acceso remoto habilitado para conexiones desde el servidor web.
- Se puede utilizar una **instancia nombrada o predeterminada**, según la configuración del entorno (ej. `SQLSERVER\MSSQLSERVER` o `.`).

### Impresión de etiquetas

- Sistema operativo compatible: **Ubuntu Server** 
- Servidor de impresión instalado con soporte para CUPS (`cups` y `lp` en Linux)
- Impresoras compatibles (Godex, Zebra u otras que admitan impresión de imágenes PNG)
- Acceso a red de las impresoras desde el servidor web

---
### 🐍 Python – Inserción de datos a SAP simulado

Se ha desarrollado una aplicación de escritorio en Python (Tkinter) para insertar datos relacionados con órdenes de fabricación en la base de datos `ES_10`, que simula SAP Business One. La herramienta permite registrar artículos, órdenes de fabricación, ubicaciones y stock de forma rápida y estructurada.

**Características principales:**

- Interfaz para introducir: `ItemCode`, `Descripción`, `Cantidad`, `DocNum`, `Proyecto` y `Ubicación`.
- Inserción automática en: `OITM`, `OWOR`, `WOR1`, `OBIN`, `OITW` y `OIBQ`.
- Validación previa para evitar duplicados.
- Desplegable dinámico con ubicaciones de `OBIN`.
- Conexión mediante `pyodbc` a SQL Server.
- Mensajes de éxito y error integrados (`tkinter.messagebox`).

---

### Seguridad y control de acceso

- Sistema de login con sesiones (ya integrado)
- Firewall configurado para permitir tráfico HTTP/HTTPS y SQL Server
---

### 🕓 Historial de Versiones
📌 **Versión 1.0**  
**Fecha:** Mayo 2025  
**Descripción:**  
Primera versión funcional del Sistema de Trazabilidad y Consulta de Fabricación (STCF).  
Se ha desarrollado una solución web con interfaz sencilla para gestionar información de materiales basada en órdenes de fabricación, permitiendo consultar, editar e imprimir etiquetas asociadas a cada línea.  
En esta versión se implementan las siguientes funcionalidades:

- Login básico por usuario.
- Conexión a base de datos MySQL.
- Consulta dinámica por DocNum.
- Visualización de materiales, cantidades y ubicaciones.
- Edición en línea de campos como cantidad pendiente y observación.
- Generación de etiquetas a partir de los datos obtenidos.
- Estilo visual personalizado mediante style.css.

📌 **Versión 2.0 – Transición a entorno local y autenticación con Active Directory**  
**Fecha de lanzamiento:** Mayo 2025  
**Estado:** Estable  

🧾 **Descripción General**  
La versión 2.0 del proyecto STCF representa una evolución completa respecto a la versión 1.0.  
El sistema ha sido rediseñado para ejecutarse de forma local en un entorno basado en XAMPP y MySQL, integrando autenticación con Active Directory mediante el protocolo LDAP, lo cual habilita una gestión centralizada y segura del acceso a la plataforma.

🔄 **Cambios y Mejoras en esta versión**

🧠 *Reestructuración técnica:*
- Reemplazo completo del sistema de conexión `sqlsrv` por `mysqli` (MySQL).
- Separación clara entre bases de datos:
  - `bd_trazabilidad` para datos operativos.
  - `bd_usuarios` o Active Directory para autenticación.
- Adaptación de las sentencias SQL al estándar de MySQL.

🔐 *Autenticación con Active Directory:*
- Implementación del protocolo LDAP en `login.php`.
- Búsqueda de usuario y recuperación de atributos (`cn`, `mail`) desde el servidor de dominio.
- Eliminación del login local básico de la versión anterior (opcional).
- Control de errores silencioso y seguro en caso de fallos de autenticación.

💡 *Nuevas funcionalidades:*
- Sesiones PHP seguras tras autenticación LDAP.
- Visualización del nombre completo del usuario tras login.
- Preparación para registro de auditoría de accesos (pendiente para v2.1).
- Soporte para despliegue en redes con dominio `asir.local` o equivalente.

🛠️ *Ajustes de compatibilidad:*
- Visualización de errores PHP activada en entorno local (`error_reporting`).
- Conexión funcional en XAMPP sin contraseñas de MySQL por defecto.
- Código portable entre Windows y Linux (adaptado a Apache + PHP 7.4+).

---

### 📚 Bibliografía

- Documentación oficial de PHP: https://www.php.net/manual/es/
- Documentación de MySQL: https://dev.mysql.com/doc/
- Manual de HTML y CSS - MDN Web Docs: https://developer.mozilla.org/es/docs/Web
- FPDF para generación de PDFs en PHP: https://www.fpdf.org/
- Guía de conexión PHP a MySQL (W3Schools): https://www.w3schools.com/php/php_mysql_connect.asp
- SAP Business One – Guía de usuario (referencia conceptual para estructura de datos y trazabilidad): *[Manual interno/no público]*

---

### 🎬 Demo del Proyecto

https://youtu.be/qVfYtmuvNuA
