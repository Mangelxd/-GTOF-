Proyecto de Gestión de Trazabilidad por Orden de Fabricación
Este sistema ha sido desarrollado para facilitar la gestión de trazabilidad de materiales vinculados a órdenes de fabricación (DocNum) en un entorno de tipo SAP Business One. Proporciona una interfaz web práctica y eficiente para visualizar, modificar e imprimir información crítica asociada a cada orden.

Características principales
Al introducir una orden de fabricación (DocNum), la aplicación permite:

Consultar los materiales pendientes por línea.

Identificar su ubicación física dentro del almacén (bins).

Visualizar el proyecto asociado a cada orden.

Editar información detallada por línea: cantidad, lote, observaciones, ubicación.

Imprimir etiquetas con código QR para cada material.

Guardar automáticamente los datos en una tabla histórica, sin duplicados.

Generar identificadores únicos por línea en formato DocNum;N.

Funcionamiento del sistema
El usuario accede a la web e introduce un DocNum.

Se ejecuta el procedimiento Actualizar_M_BK_ResgistroTrazabilidad, que consulta los datos desde el sistema base.

Los resultados se insertan o actualizan en la tabla M_BK_ResgistroTrazabilidad.

Cada línea se muestra en pantalla y puede ser editada.

Desde esa misma vista, el usuario puede imprimir una etiqueta individual con QR.

El sistema garantiza que no se dupliquen líneas ya existentes.

Estructura de datos y orígenes
La aplicación utiliza procedimientos que consultan directamente tablas simuladas del entorno SAP:

OWOR, WOR1 para órdenes de fabricación.

OITM, OITW, OIBQ, OBIN para artículos, stock y ubicaciones.

La tabla de trabajo principal es M_BK_ResgistroTrazabilidad.

Interfaz web
La aplicación incluye:

Una pantalla de búsqueda por número de orden.

Resultados dinámicos agrupados por línea de material.

Formularios para modificar campos clave: ubicación, cantidad a adquirir, lote, observaciones.

Selector de impresora para generar etiquetas directamente desde el navegador.

Código QR generado en tiempo real con la librería phpqrcode.

Sistema de autenticación por sesión.

Requisitos técnicos
SQL Server

Base de datos M_EXTRAS_TEST

Esquema de datos compatible con SAP Business One

Servidor web con PHP (probado en Apache sobre Ubuntu)

Librería phpqrcode instalada

Sistema de impresión con cups y soporte para lp

Público objetivo
Este sistema está orientado a responsables de producción, logística y calidad en entornos industriales. Es especialmente útil para empresas que trabajan con fabricación bajo pedido y necesitan trazabilidad detallada por línea, sin depender exclusivamente del ERP.

