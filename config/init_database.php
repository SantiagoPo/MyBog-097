<?php
// Incluye el archivo de configuración y conexión a la base de datos
require_once 'config/conexion.php';

// Verifica si la inicialización ya se ha realizado
$sqlCheckInitialization = "SELECT * FROM control WHERE initialized = 1";
$resultCheckInitialization = $conexion->query($sqlCheckInitialization);

if ($resultCheckInitialization->num_rows === 0) {
    // Realiza la inicialización

    // Inserta aquí las consultas específicas de tu script de la base de datos
    $sqlScript = <<<SQL
    -- Pegue aquí el contenido de su script de base de datos

    

    -- Puedes omitir las líneas LOCK TABLES y UNLOCK TABLES si no son necesarias.

    SQL;

    $conexion->multi_query($sqlScript);

    // Marca la inicialización como completada
    $sqlUpdateInitializationStatus = "INSERT INTO control (initialized) VALUES (1)";
    $conexion->query($sqlUpdateInitializationStatus);
}
?>
