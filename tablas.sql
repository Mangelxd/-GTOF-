CREATE TABLE dbo.M_BK_ResgistroTrazabilidad (
    Id INT IDENTITY(1,1) PRIMARY KEY,
    PartNumber NVARCHAR(50) NOT NULL,
    Descripcion NVARCHAR(200) NULL,
    CantidadPendiente INT NULL,
    CantidadNecesaria INT NULL,
    Ubicacion NVARCHAR(100) NULL,
    UltimaActualizacion DATETIME NOT NULL DEFAULT GETDATE(),
    Lote NVARCHAR(100) NULL,
    Observacion NVARCHAR(500) NULL,
    DocNum INT NOT NULL,
    Project NVARCHAR(50) NULL,
    DocNumSecuencial NVARCHAR(50) NULL
);
CREATE TABLE SBO_M_ES_10.dbo.OITM (
    ItemCode NVARCHAR(50) PRIMARY KEY,
    ItemName NVARCHAR(200)
);

CREATE TABLE SBO_M_ES_10.dbo.OWOR (
    DocEntry INT PRIMARY KEY,
    DocNum INT UNIQUE,
    Status CHAR(1),
    Project NVARCHAR(50)
);

CREATE TABLE SBO_M_ES_10.dbo.WOR1 (
    DocEntry INT,
    ItemCode NVARCHAR(50),
    PlannedQty INT
);

CREATE TABLE SBO_M_ES_10.dbo.OITW (
    ItemCode NVARCHAR(50),
    WhsCode NVARCHAR(10),
    OnHand INT
);

CREATE TABLE SBO_M_ES_10.dbo.OIBQ (
    ItemCode NVARCHAR(50),
    WhsCode NVARCHAR(10),
    BinAbs INT,
    OnHandQty INT
);