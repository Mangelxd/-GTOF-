import tkinter as tk
from tkinter import ttk, messagebox
import pyodbc

# Configuración de la conexión a SQL Server
server = '192.168.1.100,1433'
database = 'ES_10'
username = 'usuario'
password = 'Usu@rio123!'
conn_str = (
    f'DRIVER={{ODBC Driver 17 for SQL Server}};SERVER={server};'
    f'DATABASE={database};UID={username};PWD={password}'
)

# Obtener ubicaciones desde la tabla OBIN
def obtener_ubicaciones():
    try:
        conn = pyodbc.connect(conn_str)
        cursor = conn.cursor()
        cursor.execute("SELECT BinCode FROM OBIN")
        ubicaciones = [row[0] for row in cursor.fetchall()]
        conn.close()
        return ubicaciones
    except Exception as e:
        messagebox.showerror("Error", f"No se pudieron cargar las ubicaciones:\n{e}")
        return []

# Función para insertar datos
def insertar_datos():
    try:
        conn = pyodbc.connect(conn_str)
        cursor = conn.cursor()

        item_code = entradas["ItemCode"].get()
        item_name = entradas["ItemName"].get()
        planned_qty = int(entradas["PlannedQty"].get())
        docnum = int(entradas["DocNum"].get())
        project = entradas["Project"].get()
        bin_code = ubicacion_var.get()

        # Insertar en OITM (si no existe)
        cursor.execute("""
            IF NOT EXISTS (SELECT 1 FROM OITM WHERE ItemCode = ?)
            INSERT INTO OITM (ItemCode, ItemName) VALUES (?, ?)
        """, item_code, item_code, item_name)

        # Insertar en OWOR y obtener DocEntry generado
        cursor.execute("""
            INSERT INTO OWOR (DocNum, Status, Project)
            OUTPUT INSERTED.DocEntry
            VALUES (?, 'R', ?)
        """, docnum, project)
        doc_entry = cursor.fetchone()[0]

        # Insertar en WOR1
        cursor.execute("""
            INSERT INTO WOR1 (DocEntry, ItemCode, PlannedQty)
            VALUES (?, ?, ?)
        """, doc_entry, item_code, planned_qty)

        # Insertar en OBIN (si no existe)
        cursor.execute("""
            IF NOT EXISTS (SELECT 1 FROM OBIN WHERE BinCode = ?)
            INSERT INTO OBIN (BinCode) VALUES (?)
        """, bin_code, bin_code)

        # Obtener AbsEntry del nuevo bin
        cursor.execute("SELECT AbsEntry FROM OBIN WHERE BinCode = ?", bin_code)
        bin_abs = cursor.fetchone()[0]

        # Insertar en OITW (si no existe)
        cursor.execute("""
            IF NOT EXISTS (SELECT 1 FROM OITW WHERE ItemCode = ? AND WhsCode = '01')
            INSERT INTO OITW (ItemCode, WhsCode, OnHand)
            VALUES (?, '01', ?)
        """, item_code, item_code, planned_qty)

        # Insertar en OIBQ
        cursor.execute("""
            INSERT INTO OIBQ (ItemCode, WhsCode, BinAbs, OnHandQty)
            VALUES (?, '01', ?, ?)
        """, item_code, bin_abs, planned_qty)

        conn.commit()
        conn.close()
        messagebox.showinfo("Éxito", "Datos insertados correctamente.")
    except Exception as e:
        messagebox.showerror("Error", f"Ocurrió un error:\n{e}")

# Crear interfaz gráfica
root = tk.Tk()
root.title("Insertar Datos para OF")
root.geometry("500x400")  # Tamaño de la ventana

font_etiqueta = ("Arial", 12)
font_entrada = ("Arial", 12)

frame = tk.Frame(root, padx=20, pady=20)
frame.pack(expand=True)

campos = [
    ("PathNumber", "ItemCode"),
    ("Descripción", "ItemName"),
    ("Cantidad", "PlannedQty"),
    ("OF", "DocNum"),
    ("Proyecto", "Project"),
]

entradas = {}
for i, (label_text, varname) in enumerate(campos):
    tk.Label(frame, text=label_text + ":", font=font_etiqueta).grid(row=i, column=0, sticky="e", pady=5)
    entry = tk.Entry(frame, font=font_entrada, width=30)
    entry.grid(row=i, column=1, pady=5)
    entradas[varname] = entry

# Desplegable para ubicación
tk.Label(frame, text="Ubicación:", font=font_etiqueta).grid(row=len(campos), column=0, sticky="e", pady=5)
ubicacion_var = tk.StringVar()
ubicaciones_disponibles = obtener_ubicaciones()
ubicacion_combo = ttk.Combobox(frame, textvariable=ubicacion_var, values=ubicaciones_disponibles, font=font_entrada, width=28)
ubicacion_combo.grid(row=len(campos), column=1, pady=5)
if ubicaciones_disponibles:
    ubicacion_combo.current(0)

# Botón para insertar
tk.Button(frame, text="Insertar", font=("Arial", 12, "bold"), command=insertar_datos).grid(row=len(campos)+1, columnspan=2, pady=20)

root.mainloop()
