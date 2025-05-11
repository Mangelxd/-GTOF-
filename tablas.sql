-- Tabla principal de trazabilidad
CREATE TABLE M_BK_ResgistroTrazabilidad (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    PartNumber VARCHAR(50) NOT NULL,
    Descripcion VARCHAR(200),
    CantidadPendiente INT,
    CantidadNecesaria INT,
    Ubicacion VARCHAR(100),
    UltimaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Lote VARCHAR(100),
    Observacion VARCHAR(500),
    DocNum INT NOT NULL,
    Project VARCHAR(50),
    DocNumSecuencial VARCHAR(50)
);

-- Tabla de artículos
CREATE TABLE SBO_M_ES_10_OITM (
    ItemCode VARCHAR(50) PRIMARY KEY,
    ItemName VARCHAR(200)
);

-- Tabla de órdenes de fabricación
CREATE TABLE SBO_M_ES_10_OWOR (
    DocEntry INT PRIMARY KEY,
    DocNum INT UNIQUE,
    Status CHAR(1),
    Project VARCHAR(50)
);

-- Detalles de órdenes de fabricación
CREATE TABLE SBO_M_ES_10_WOR1 (
    DocEntry INT,
    ItemCode VARCHAR(50),
    PlannedQty INT
);

-- Stock por almacén
CREATE TABLE SBO_M_ES_10_OITW (
    ItemCode VARCHAR(50),
    WhsCode VARCHAR(10),
    OnHand INT
);

-- Stock por ubicación
CREATE TABLE SBO_M_ES_10_OIBQ (
    ItemCode VARCHAR(50),
    WhsCode VARCHAR(10),
    BinAbs INT,
    OnHandQty INT
);
