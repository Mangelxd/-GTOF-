import sys
import qrcode
from PIL import Image

def generar_etiqueta(part_number, descripcion, ubicacion, cantidad):
    # Generación del código QR
    qr = qrcode.QRCode(
        version=1,
        error_correction=qrcode.constants.ERROR_CORRECT_L,
        box_size=10,
        border=4,
    )
    qr.add_data(part_number)
    qr.make(fit=True)

    img_qr = qr.make_image(fill="black", back_color="white")

    # Generar la imagen para la etiqueta
    etiqueta = Image.new('RGB', (300, 200), color = 'white')
    img_qr = img_qr.resize((80, 80))  # Ajustar el tamaño del QR
    etiqueta.paste(img_qr, (10, 10))

    # Añadir texto (por ejemplo)
    from PIL import ImageDraw, ImageFont
    draw = ImageDraw.Draw(etiqueta)
    font = ImageFont.load_default()

    draw.text((100, 10), f"PatNum: {part_number}", fill="black", font=font)
    draw.text((100, 40), f"Descripcion: {descripcion[:30]}", fill="black", font=font)
    draw.text((100, 60), f"Ubicacion: {ubicacion}", fill="black", font=font)
    draw.text((100, 80), f"Cantidad: {cantidad}", fill="black", font=font)

    # Guardar la imagen en un archivo
    etiqueta_path = f"/var/www/html/etiquetas_creadas/{part_number}_etiqueta.png"
    etiqueta.save(etiqueta_path)
    
    return etiqueta_path

if __name__ == "__main__":
    # Leer parámetros desde PHP
    part_number = sys.argv[1]
    descripcion = sys.argv[2]
    ubicacion = sys.argv[3]
    cantidad = sys.argv[4]
    
    # Llamar a la función de generar la etiqueta
    etiqueta_path = generar_etiqueta(part_number, descripcion, ubicacion, cantidad)
    
    # Devolver el path de la etiqueta generada
    print(etiqueta_path)
