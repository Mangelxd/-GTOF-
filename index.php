<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require 'bd.php';

$DocNum = $_GET['of'] ?? '';
$partnumber = $_GET['partnumber'] ?? '';
$resultados = [];

if ($conn) {
    if ($DocNum !== '') {
        sqlsrv_query($conn, "EXEC dbo.Actualizar_DHV_BK_ResgistroTrazabilidad @DocNum = ?", [$DocNum]);

        $sql = "SELECT Id, PartNumber, Descripcion, CantidadPendiente, CantidadNecesaria, Ubicacion,
                       Observacion, Lote, UltimaActualizacion, DocNum, Project
                FROM dbo.DHV_BK_ResgistroTrazabilidad
                WHERE DocNum = ?";
        $params = [$DocNum];
        if (!empty($partnumber)) {
            $sql .= " AND PartNumber LIKE ?";
            $params[] = '%' . $partnumber . '%';
        }
    } else {
        $sql = "SELECT p.NumOF, p.Project, p.Producto, p.Descripcion, p.FechaPicking
                FROM [DHV_EXTRAS_TEST].[dbo].[DHV_Picking_OF] AS p
                LEFT JOIN [DHV_EXTRAS_TEST].[dbo].[DHV_Ubicaciones] AS u
                  ON p.UbicacionId = u.IdUbicacion
                WHERE p.FechaPicking <= DATEADD(MONTH, +3, GETDATE())
                  AND p.statusSAP NOT IN ('C','L')
                ORDER BY p.FechaPicking DESC";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta OF</title>
    <link rel="stylesheet" href="style.css">
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
      <img src="logo.png" alt="Logo empresa" />
      <h1 style="margin: 0 0.5rem;">DHV Technology</h1>
    </div>
    <div style="margin-left: auto;">
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </header>
<h2>
  <?php if (isset($_SESSION["nombreCompleto"])): ?>
  <div class="bienvenida-centro">
    Welcome, <?= htmlspecialchars($_SESSION["nombreCompleto"]) ?>
  </div>
<?php endif; ?>
</h2>
<h2>Consulta de Órdenes</h2>

<!-- BOTÓN PARA FILTRAR -->
<button id="btn-filtrar" type="button" style="margin-bottom: 1rem;">Filtrar por OF y PartNumber</button>

<!-- FORMULARIO DE BÚSQUEDA -->
<div id="formulario-busqueda">
    <form method="GET" action="">
        <label for="of">Orden de Fabricación (OF)</label>
        <input type="number" name="of" id="of" value="<?= htmlspecialchars($DocNum) ?>" />

        <label for="partnumber">PartNumber (opcional)</label>
        <input type="text" name="partnumber" id="partnumber" value="<?= htmlspecialchars($partnumber) ?>" />

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
          <input type="hidden" name="DocNum" value="<?= htmlspecialchars($fila['DocNum']) ?>">
          <input type="hidden" name="partnumber" value="<?= htmlspecialchars($fila['PartNumber']) ?>">
          <input type="hidden" name="cantidad_original" value="<?= $necesaria ?>">

          <label>Ubicación:</label>
          <input type="text" name="ubicacion" value="<?= $fila['Ubicacion'] ?>">

          <label>Cantidad a adquirir:</label>
          <input type="number" name="cantidad_a_adquirir" min="0" max="<?= $necesaria ?>" value="0"
                 oninput="actualizarCantidad('<?= $i ?>', <?= $necesaria ?>)">

          <small id="nueva-cantidad-<?= $i ?>">Cantidad restante: <?= $necesaria ?></small>

          <label>Lote:</label>
          <input type="text" name="lote" value="<?= $fila['Lote'] ?>">

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
              <option value="Godex_Oficina">Godex Oficina</option>
              <option value="Godex_Almacen">Godex Almacén</option>
              <option value="Zebra_Sala">Zebra Sala</option>
          </select>

          <button type="submit">Imprimir etiqueta</button>
        </form>
      </div>
    <?php else: ?>
      <div class="card-resumen blanca" onclick="autocompletarDocNum(<?= $fila['NumOF'] ?>)">
        <p><strong>NumOF:</strong> <?= htmlspecialchars($fila['NumOF']) ?></p>
        <p><strong>Proyecto:</strong> <?= htmlspecialchars($fila['Project']) ?></p>
        <p><strong>Producto:</strong> <?= htmlspecialchars($fila['Producto']) ?></p>
        <p><strong>Descripción:</strong> <?= htmlspecialchars($fila['Descripcion']) ?></p>
        <p><strong>Fecha Picking:</strong> <?= $fila['FechaPicking'] instanceof DateTime ? $fila['FechaPicking']->format('d/m/Y') : '' ?></p>
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

  document.addEventListener('DOMContentLoaded', function() {
    const botonFiltrar = document.getElementById('btn-filtrar');
    const formularioBusqueda = document.getElementById('formulario-busqueda');

    botonFiltrar.addEventListener('click', function() {
      formularioBusqueda.classList.toggle('mostrar');
      if (formularioBusqueda.classList.contains('mostrar')) {
        document.getElementById('of').focus();
      }
    });

    document.querySelectorAll('select[name="printer"]').forEach(select => {
      const saved = localStorage.getItem('impresoraSeleccionada');
      if (saved) select.value = saved;
      select.addEventListener('change', () => {
        localStorage.setItem('impresoraSeleccionada', select.value);
      });
    });
  });

  function autocompletarDocNum(DocNum) {
    document.getElementById('of').value = DocNum;
    document.getElementById('partnumber').value = '';
    const form = document.querySelector('#formulario-busqueda form');
    form.submit();
  }
</script>
</body>
</html>
