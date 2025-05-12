<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require 'bd.php';

$DocNum = $_GET['of'] ?? '';
$partnumber = $_GET['partnumber'] ?? '';
$loteQR = $_GET['lote_qr'] ?? '';
$ubicacionQR = $_GET['ubicacion_qr'] ?? '';
$resultados = [];

if ($conn) {
    if ($DocNum !== '') {
        sqlsrv_query($conn, "EXEC dbo.Actualizar_DHV_BK_ResgistroTrazabilidad @DocNum = ?", [$DocNum]);

        $sql = "SELECT Id, PartNumber, Descripcion, CantidadPendiente, CantidadNecesaria, Ubicacion,
               Observacion, Lote, UltimaActualizacion, DocNum, Project
        FROM dbo.DHV_BK_ResgistroTrazabilidad
        WHERE DocNum = ?
          AND (
              Ubicacion IS NULL
              OR Ubicacion = ''
              OR (Ubicacion NOT IN ('01-BIC', '01-AQS') AND Ubicacion NOT LIKE '06%')
          )";
$params = [$DocNum];


        if (!empty($partnumber)) {
            $sql .= " AND PartNumber LIKE ?";
            $params[] = '%' . $partnumber . '%';
        }
        $sql .= " ORDER BY Ubicacion ASC";
    } else {
        $sql = "SELECT * 
        FROM [DHV_EXTRAS_TEST].[dbo].[DHV_Picking_OF] AS p
        LEFT JOIN [DHV_EXTRAS_TEST].[dbo].[DHV_Ubicaciones] AS u
        ON p.UbicacionId = u.IdUbicacion
        WHERE p.FechaPicking <= DATEADD(MONTH, +2, GETDATE())
          AND p.statusSAP NOT IN ('C','L')
          AND p.Estado NOT IN (2, 4)
        ORDER BY p.Prioridad DESC, p.Estado DESC, p.FechaPicking DESC";

$params = [];

    }

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $resultados[] = $row;
        }
    } else {
        die("<h2>Error en la consulta:</h2><pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Consulta OF</title>
    <link rel="preload" href="style.css?v=3" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="style.css?v=3"></noscript>
    <style>
        #formulario-busqueda {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease, padding 0.3s ease;
        }
        #formulario-busqueda.mostrar {
            max-height: 400px;
            padding-top: 1rem;
        }
    </style>
</head>
<body>
<header>
    <div style="display: flex; align-items: center;">
        <img src="https://andaluciaaerospace.com/wp-content/uploads/2022/06/dhv-logo.png" alt="Logo empresa" />
        <h1 style="margin: 0 0.5rem;">DHV Technology</h1>
    </div>
    <div style="margin-left: auto;">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<?php if (isset($_SESSION["nombreCompleto"])): ?>
    <div class="bienvenida-centro">
       <?= htmlspecialchars($_SESSION["nombreCompleto"]) ?>
    </div>
<?php endif; ?>

<h2> <?= $DocNum ? '(OF: ' . htmlspecialchars($DocNum) . ')' : '' ?></h2>

	<?php if ($DocNum): ?>
<div class="acciones-of">
    <form id="form-accion-of" method="POST" action="acciones_of.php" onsubmit="return enviarAccion(event)">
        <input type="hidden" name="of" value="<?= htmlspecialchars($DocNum) ?>">
        <input type="hidden" name="accion" id="accion-of" value="">

        <button type="button" onclick="setAccion('empezar')" class="btn btn-verde">▶ Empezar</button>
        <button type="button" onclick="setAccion('parar')" class="btn btn-amarillo">⏸ Parar</button>
<!--        <button type="button" onclick="setAccion('cerrar')" class="btn btn-rojo">⛔ Terminar</button> -->
    </form>
</div>
<?php endif; ?>

<?php if ($DocNum): ?>
<form method="POST" action="imprimir_todo.php" onsubmit="return confirmarImpresion()">
    <input type="hidden" name="DocNum" value="<?= htmlspecialchars($DocNum) ?>">
    <label for="printer-select"><strong>Impresora:</strong></label>
    <select name="printer" id="printer-select" required>
        <option value="">-- Seleccionar impresora --</option>
 <!--       <option value="Godex_Oficina">Godex Oficina</option> -->
        <option value="Godex_Almacen">Godex Almacén</option>
        <option value="Zebra_Sala">Zebra Sala</option>
    </select>
    <button type="submit" class="print-of-btn">🖨ï¸ Imprimir toda la OF</button>
</form>
<form method="POST" action="imprimir_caja.php" target="_blank" onsubmit="return confirmarImpresionCaja()" style="margin-top: 0.5rem;">
    <input type="hidden" name="DocNum" value="<?= htmlspecialchars($DocNum) ?>">
    <label for="printer-caja-select"><strong>Impresora:</strong></label>
    <select name="printer" id="printer-caja-select" required>
        <option value="">-- Seleccionar impresora --</option>
 <!--       <option value="Godex_Oficina">Godex Oficina</option> -->
        <option value="Godex_Almacen">Godex Almacén</option>
        <option value="Zebra_Sala">Zebra Sala</option>
    </select>

    <button type="submit">📦 Imprimir etiqueta de caja</button>
</form>

<?php endif; ?>

<button id="btn-filtrar" type="button" style="margin-bottom: 1rem;">Filtrar por OF y PartNumber</button>

<div id="formulario-busqueda">
    <form method="GET" action="">
        <label for="of"> (OF)</label>
        <input type="number" name="of" id="of" value="<?= htmlspecialchars($DocNum) ?>" />

        <label for="partnumber">Escanea QR</label>
        <input type="text" name="partnumber" id="partnumber" value="<?= htmlspecialchars($partnumber) ?>" autocomplete="off" />
<button type="button" onclick="startScanner()">📷  Escanear QR</button>
<!-- Contenedor modal del lector QR -->
<div id="reader-modal" style="display: none; flex-direction: column; align-items: center; margin-top: 1rem;">
  <div id="reader" style="width: 300px; height: 300px;"></div>
  <button onclick="stopScanner()" style="margin-top: 1rem;">❌ Cancelar escaneo</button>
</div>

        <input type="hidden" name="lote_qr" id="lote_qr" value="<?= htmlspecialchars($loteQR) ?>" />
        <input type="hidden" name="ubicacion_qr" id="ubicacion_qr" value="<?= htmlspecialchars($ubicacionQR) ?>" />

        <button type="submit">Buscar</button>
    </form>
    <div style="text-align: right;">
        <a href="index.php" class="btn-volver">Volver al inicio</a>
    </div>
</div>

<?php if (!empty($resultados)): ?>
    <?php foreach ($resultados as $i => $fila): ?>
        <?php if ($DocNum !== ''): ?>
            <?php
                $cantidad = $fila['CantidadPendiente'];
                $necesaria = $fila['CantidadNecesaria'];
                $color = 'blanca';
                if ($cantidad == 0) $color = 'verde';
                elseif ($cantidad != $necesaria) $color = 'roja';
            ?>
            <div class="card-resumen <?= $color ?>" onclick="toggleForm('<?= $i ?>')">
                <div>
                    <strong><?= $fila['PartNumber'] ?></strong>
                    <span class="arrow" id="arrow-<?= $i ?>">▼</span>
                </div>
                <p><small><strong>Descripción:</strong> <?= $fila['Descripcion'] ?></small></p>
                <p><small><strong>Ubicación:</strong> <?= $fila['Ubicacion'] ?></small></p>
                <p><small><strong>Cantidad necesaria:</strong> <?= $necesaria ?></small></p>
                <p><small><strong>Cantidad pendiente:</strong> <?= $cantidad ?></small></p>
            </div>

            <div id="form-<?= $i ?>" class="card-expandido">
                <form method="post" action="guardar.php">
                    <input type="hidden" name="id" value="<?= $fila['Id'] ?>">
                    <input type="hidden" name="DocNum" value="<?= $DocNum ?>">
                    <input type="hidden" name="partnumber" value="<?= $partnumber ?>">
                    <input type="hidden" name="cantidad_original" value="<?= $necesaria ?>">

                    <label>Ubicación:</label>
                    <input type="text" name="ubicacion" value="<?= !empty($ubicacionQR) ? $ubicacionQR : $fila['Ubicacion'] ?>">

                    <label>Cantidad a adquirir:</label>
                    <input type="number" name="cantidad_a_adquirir" min="0" max="<?= $necesaria ?>" value="0"
                           oninput="actualizarCantidad('<?= $i ?>', <?= $necesaria ?>)">
                    <small id="nueva-cantidad-<?= $i ?>">Cantidad restante: <?= $necesaria ?></small>

                    <label>Lote:</label>
                    <input type="text" name="lote" value="<?= !empty($loteQR) ? $loteQR : $fila['Lote'] ?>">

                    <label>Observación:</label>
                    <textarea name="observacion"><?= $fila['Observacion'] ?></textarea>

                    <small>Última actualización: <?= $fila['UltimaActualizacion']->format('d/m/Y') ?></small>

                    <button type="submit" class="guardar-btn">Guardar</button>
                </form>

                <form method="post" action="imprimir.php" target="_blank" style="margin-top: 0.5rem;">
                    <input type="hidden" name="partnumber" value="<?= htmlspecialchars($fila['PartNumber']) ?>">
                    <input type="hidden" name="descripcion" value="<?= htmlspecialchars($fila['Descripcion']) ?>">
                    <input type="hidden" name="ubicacion" value="<?= htmlspecialchars($fila['Ubicacion']) ?>">
                    <input type="hidden" name="cantidad" value="<?= htmlspecialchars($fila['CantidadNecesaria']) ?>">
                    <input type="hidden" name="project" value="<?= htmlspecialchars($fila['Project']) ?>">
                    <input type="hidden" name="DocNum" value="<?= htmlspecialchars($fila['DocNum']) ?>">

                    <label for="printer"><small>Impresora:</small></label>
                    <select name="printer" id="printer" required>
                        <option value="">-- Seleccionar impresora --</option>
                 <!--   <option value="Godex_Oficina">Godex Oficina</option> -->
                        <option value="Godex_Almacen">Godex Almacén</option>
                        <option value="Zebra_Sala">Zebra Sala</option>
                    </select>

                    <button type="submit">Imprimir etiqueta</button>
                </form>
            </div>
<?php else: ?>
        <?php
$estado = $fila['estado'] ?? 0;
$claseEstado = 'estado-' . intval($estado);
?>
<div class="card-resumen <?= $claseEstado ?>" onclick="autocompletarDocNum(<?= $fila['NumOF'] ?>)">
    <p><strong>NumOF:</strong> <?= htmlspecialchars($fila['NumOF']) ?></p>
    <p><strong>Proyecto:</strong> <?= htmlspecialchars($fila['Project']) ?></p>
    <p><strong>Producto:</strong> <?= htmlspecialchars($fila['Producto']) ?></p>
    <p><strong>Descripción:</strong> <?= htmlspecialchars($fila['Descripcion']) ?></p>
    <p><strong>Fecha Picking:</strong> <?= $fila['FechaPicking'] instanceof DateTime ? $fila['FechaPicking']->format('d/m/Y') : '' ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($estado) ?></p> <!-- Solo para verificar -->
</div>

        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>


<script>

function toggleForm(id) {
  const form = document.getElementById('form-' + id);
  const arrow = document.getElementById('arrow-' + id);
  form.classList.toggle('mostrar');
  arrow.classList.toggle('rotada');
}

function actualizarCantidad(id, original) {
  const input = document.querySelector(`#form-${id} input[name='cantidad_a_adquirir']`);
  const cantidad = parseInt(input.value) || 0;
  const nueva = Math.max(original - cantidad, 0);
  document.getElementById(`nueva-cantidad-${id}`).textContent = "Cantidad restante: " + nueva;
}

function autocompletarDocNum(DocNum) {
  document.getElementById('of').value = DocNum;
  document.getElementById('partnumber').value = '';
  document.querySelector('#formulario-busqueda form').submit();
}

function confirmarImpresion() {
  const printer = document.getElementById('printer-select').value;
  if (!printer) {
    alert('Debes seleccionar una impresora.');
    return false;
  }
  return confirm('¿Seguro que quieres imprimir todas las etiquetas de esta OF?');
}

function confirmarImpresionCaja() {
  const printer = document.getElementById('printer-caja-select').value;
  const partnumber = document.getElementById('partnumber-caja')?.value || '';
  if (!printer || !partnumber) {
    alert('Debes seleccionar una impresora y un producto para imprimir la etiqueta de caja.');
    return false;
  }
  return confirm('¿Imprimir etiqueta de caja para ese producto?');
}

function startScanner() {
  document.getElementById("reader-modal").style.display = "flex";

  if (!window.html5QrCode) {
    window.html5QrCode = new Html5Qrcode("reader");
  }

  Html5Qrcode.getCameras().then(devices => {
    if (devices && devices.length) {
      const backCamera = devices.find(device => device.label.toLowerCase().includes("back"));
      const cameraId = backCamera ? backCamera.id : devices[0].id;

      html5QrCode.start(
        cameraId,
        { fps: 10, qrbox: { width: 260, height: 260 } },
        qrCodeMessage => {
          procesarQR(qrCodeMessage);
          html5QrCode.stop().then(() => {
            document.getElementById("reader-modal").style.display = "none";
            document.getElementById("reader").innerHTML = "";
          });
        },
        errorMessage => {
          console.warn("Error escaneando:", errorMessage);
        }
      ).catch(err => {
        console.error("No se pudo iniciar el escáner:", err);
        document.getElementById("reader-modal").style.display = "none";
      });
    }
  }).catch(err => {
    console.error("No se pudo acceder a la cámara:", err);
    document.getElementById("reader-modal").style.display = "none";
  });
}

function stopScanner() {
  if (window.html5QrCode) {
    html5QrCode.stop().then(() => {
      html5QrCode.clear();
    });
  }
  document.getElementById("reader-modal").style.display = "none";
  document.getElementById("reader").innerHTML = "";
}

function procesarQR(valor) {
  if (valor.includes(';')) {
    const partes = valor.split(';');
    if (partes.length >= 3) {
      document.getElementById('partnumber').value = partes[0];
      document.getElementById('lote_qr').value = partes[1];
      document.getElementById('ubicacion_qr').value = partes[2];
      setTimeout(() => {
        document.querySelector("#formulario-busqueda form").submit();
      }, 200);
    }
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const botonFiltrar = document.getElementById('btn-filtrar');
  const formularioBusqueda = document.getElementById('formulario-busqueda');

  botonFiltrar.addEventListener('click', function () {
    formularioBusqueda.classList.toggle('mostrar');
    if (formularioBusqueda.classList.contains('mostrar')) {
      document.getElementById('of').focus();
    }
  });

  // Restaurar impresora seleccionada desde localStorage
  document.querySelectorAll('select[name="printer"]').forEach(select => {
    const saved = localStorage.getItem('impresoraSeleccionada');
    if (saved) select.value = saved;
    select.addEventListener('change', () => {
      localStorage.setItem('impresoraSeleccionada', select.value);
    });
  });

  // Escaneo físico con escáner láser/infrarrojo
  const inputQR = document.getElementById('partnumber');
  inputQR.addEventListener('input', function () {
    procesarQR(this.value);
  });
}); 
function setAccion(accion) {
    document.getElementById("accion-of").value = accion;
    document.getElementById("form-accion-of").submit();
}

function enviarAccion(e) {
    e.preventDefault();

    const form = e.target;
    const accion = document.getElementById("accion-of").value;

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        location.reload(); // Recargar para reflejar cambios
    })
    .catch(err => {
        alert("Error al enviar acción: " + err);
    });

    return false;
}
</script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</body>
</html>
