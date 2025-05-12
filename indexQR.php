<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Escaneo QR Demo</title>
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  <style>
    body { font-family: sans-serif; text-align: center; margin: 2rem; }
    #reader { width: 300px; margin: 1rem auto; }
    input { padding: 0.5rem; font-size: 1.2rem; width: 90%; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <h2>Escaneo de QR con Cámara</h2>
  <input type="text" id="partnumber" placeholder="Aquí se mostrará el QR escaneado">
  <button onclick="startScanner()">📷 Escanear QR</button>
  <div id="reader"></div>

  <script>
    let scannerActivo = false;
    const html5QrCode = new Html5Qrcode("reader");

    function startScanner() {
      if (scannerActivo) return;
      scannerActivo = true;

      Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
          const cameraId = devices[0].id;
          html5QrCode.start(
            cameraId,
            { fps: 10, qrbox: 250 },
            qrCodeMessage => {
              document.getElementById("partnumber").value = qrCodeMessage;
              html5QrCode.stop().then(() => {
                document.getElementById("reader").innerHTML = "";
                scannerActivo = false;
              });
            },
            errorMessage => {
              console.warn("Escaneo fallido:", errorMessage);
            }
          ).catch(err => {
            console.error("Error al iniciar el escáner:", err);
            scannerActivo = false;
          });
        }
      }).catch(err => {
        console.error("Error al acceder a la cámara:", err);
        scannerActivo = false;
      });
    }
  </script>
</body>
</html>
