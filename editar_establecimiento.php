<?php
include_once('config/conexion.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: main.php'); // Redirige a la página de inicio de sesión si el usuario no está conectado.
    exit;
}

if (isset($_GET['id'])) {
    $establecimientoId = $_GET['id'];


    // Consulta para obtener los detalles del establecimiento a editar
    $sql = "SELECT * FROM registro_de_establecimiento WHERE Id_registro = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $establecimientoId);
    $stmt->execute();
    $result = $stmt->get_result();


    // Dividir la dirección por espacios en blanco.


    $sqlImagenes = "SELECT * FROM imagenes_establecimiento WHERE id_establecimiento = ?";
    $stmtImagenes = $conexion->prepare($sqlImagenes);
    $stmtImagenes->bind_param("i", $establecimientoId);
    $stmtImagenes->execute();
    $resultImagenes = $stmtImagenes->get_result();


    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        $direccionCompleta = $row['Direccion_de_establecimiento'];

        $parts = explode('¬', $direccionCompleta);

        $parts = explode('¬', $direccionCompleta);

        // Inicializar variables para cada parte de la dirección.
        $tipoVia = $numeroVia = $letra1 = $bis = $direccionSurNorte = $numeral = $numero2 = $letra3 = $guion = $numero3 = $direccionEsteOeste = $infoAdicionalDireccion = '';

        // Recorrer las partes y asignarlas a las variables correspondientes.
        foreach ($parts as $part) {
            // Verificar si la parte es un número.
            if (is_numeric($part)) {
                // Asignar el número a la variable correspondiente.
                if ($numeroVia === '') {
                    $numeroVia = $part;
                } elseif ($numero2 === '') {
                    $numero2 = $part;
                } elseif ($numero3 === '') {
                    $numero3 = $part;
                }
            } else {
                // Asignar la parte a la variable correspondiente.
                if ($tipoVia === '') {
                    $tipoVia = $part;
                } elseif ($letra1 === '') {
                    $letra1 = $part;
                } elseif ($bis === '') {
                    $bis = $part;
                } elseif ($direccionSurNorte === '') {
                    $direccionSurNorte = $part;
                } elseif ($numeral === '') {
                    $numeral = $part;
                } elseif ($letra3 === '') {
                    $letra3 = $part;
                } elseif ($guion === '') {
                    $guion = $part;
                } elseif ($direccionEsteOeste === '') {
                    $direccionEsteOeste = $part;
                } elseif ($infoAdicionalDireccion === '') {
                    $infoAdicionalDireccion = $part;
                }
            }
        }
    } else {
        // Manejar el caso en que el establecimiento no se encuentre en la base de datos.
        echo "El establecimiento no se encuentra en la base de datos.";
        exit;
    }
} else {
    // Manejar el caso en que no se proporcionó un ID válido.
    echo "ID de establecimiento no válido.";
    exit;
}

// Procesa el formulario de edición si se envió.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_establecimiento'])) {
    $nombreEstablecimiento = $_POST['nombre_establecimiento'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $informacionAdicional = $_POST['informacion_adicional'];
    $nit = $_POST['nit'];
    $localidad = $_POST['localidad'];
    $tipoEstablecimiento = $_POST['tipo_establecimiento'];

    $sqlImagenes = "SELECT * FROM imagenes_establecimiento WHERE id_establecimiento = ?";
    $stmtImagenes = $conexion->prepare($sqlImagenes);
    $stmtImagenes->bind_param("i", $establecimientoId);
    $stmtImagenes->execute();
    $resultImagenes = $stmtImagenes->get_result();
    // Consulta para actualizar los detalles del establecimiento en la base de datos
    $sql = "UPDATE registro_de_establecimiento SET Nombre_del_establecimiento=?, Direccion_de_establecimiento=?, Telefono=?, Informacion_adicional=?, Nit=?, localidad=?, id_tipo_de_establecimiento=? WHERE Id_registro=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssssi", $nombreEstablecimiento, $direccion, $telefono, $informacionAdicional, $nit, $localidad, $tipoEstablecimiento, $establecimientoId);

    if ($stmt->execute()) {
        // Establecimiento actualizado con éxito, puedes redirigir a una página de éxito o mostrar un mensaje aquí.
        echo '<div class="alert alert-success" role="alert">
            Establecimiento registrado de forma exitosa
            </div>';
    } else {
        // Error al actualizar el establecimiento, muestra un mensaje de error.
        echo "Error al actualizar el establecimiento: " . $stmt->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_imagen'])) {
    $establecimientoId = $_POST['id_establecimiento'];
    $imagenId = $_POST['id_imagen'];

    // Deshabilita temporalmente la restricción de clave externa
    $conexion->query("SET foreign_key_checks = 0");

    // Obtiene el nombre del archivo
    $sqlNombreArchivo = "SELECT nombre_archivo FROM imagenes_establecimiento WHERE id_imagen = ?";
    $stmtNombreArchivo = $conexion->prepare($sqlNombreArchivo);
    $stmtNombreArchivo->bind_param("i", $imagenId);
    $stmtNombreArchivo->execute();
    $resultNombreArchivo = $stmtNombreArchivo->get_result();

    if ($resultNombreArchivo->num_rows == 1) {
        $rowNombreArchivo = $resultNombreArchivo->fetch_assoc();
        $nombreArchivo = $rowNombreArchivo['nombre_archivo'];

        // Aquí debes agregar la lógica para eliminar la imagen de la base de datos.
        // Por ejemplo, puedes ejecutar una consulta DELETE en la tabla de imágenes.
        $sqlEliminarImagen = "DELETE FROM imagenes_establecimiento WHERE id_imagen = ?";
        $stmtEliminarImagen = $conexion->prepare($sqlEliminarImagen);
        $stmtEliminarImagen->bind_param("i", $imagenId);

        if ($stmtEliminarImagen->execute()) {
            // Imagen eliminada con éxito de la base de datos, ahora elimínala del sistema de archivos

            // Ruta completa del archivo
            $rutaArchivo = 'php/Imagen_guardar/' . $nombreArchivo;

            // Intenta eliminar el archivo del sistema de archivos
            if (unlink($rutaArchivo)) {
                echo "Imagen y archivo eliminados con éxito.";
            } else {
                echo "Error al eliminar el archivo. La imagen se eliminó de la base de datos, pero el archivo no pudo ser eliminado del sistema de archivos.";
            }
        } else {
            // Maneja errores, si los hay
            echo "Error al eliminar la imagen de la base de datos: " . $stmtEliminarImagen->error;
        }
    } else {
        echo "Error al obtener el nombre del archivo.";
    }

    // Vuelve a habilitar la restricción de clave externa
    $conexion->query("SET foreign_key_checks = 1");
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Establecimiento</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./style/HeaderFooter.css">
    <!--<link rel="stylesheet" type="text/css" href="./style/editar_establecimiento.css"> /*-->
</head>

<body>
    <div class="wrapper">
        <nav id="custom-navbar" class="navbar navbar-expand-lg navbar-light navbar-dark-bg">
            <div class="container-fluid" id="header">
                <a class="navbar-brand Logo" href="./index.php"><img src="./Imagenes/Logo.png" alt="Logo"></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav justify-content-end">
                        <li class="nav-item">
                            <a class="nav-link rojo" id="mapa" href="./mapa.php">Mapa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link amarillo" id="calendario" href="./calendario.php">Calendario</a>
                        </li>
                        <?php
                        if (isset($_SESSION['user_id'])) {
                            echo '<li class="nav-item">
                                <a class="nav-link amarillo" id="calendario" href="./reg_establecimiento.php">Deseas registrar tu establecimiento</a>
                                </li>';
                        } else {
                            echo '';
                        }
                        include('modales_usuario.php');
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form method="post" action="" class="editar">
                        <h2 class="mb-4">Editar Establecimiento</h2>

                        <div class="form-group">
                            <label for="nombre_establecimiento">Nombre del Establecimiento:</label>
                            <input type="text" class="form-control" id="nombre_establecimiento"
                                name="nombre_establecimiento" value="<?php echo $row['Nombre_del_establecimiento']; ?>">
                        </div>
                        <div class="form-group" id="Direccion_de_establecimiento" name="Direccion_de_establecimiento">
                            <label for="Direccion_de_establecimiento">Dirección</label>
                            <div class="row justify-content-center align-items-center" id="direccion_inputs">
                                <div class="col-md-4 text-center" id="tipovia" name="tipovia">
                                    <label for="tipo_via">Tipo de vía</label>
                                    <select class="form-control" name="tipo_via" required>
                                        <option value="" disabled></option>
                                        <option value="calle" <?php echo ($tipoVia === 'calle') ? 'selected' : ''; ?>>
                                            Calle</option>
                                        <option value="carrera" <?php echo ($tipoVia === 'carrera') ? 'selected' : ''; ?>>
                                            Carrera</option>
                                        <option value="transversal" <?php echo ($tipoVia === 'transversal') ? 'selected' : ''; ?>>Transversal</option>
                                        <option value="diagonal" <?php echo ($tipoVia === 'diagonal') ? 'selected' : ''; ?>>Diagonal</option>
                                    </select>
                                </div>

                                <div class="col-md-2 text-center" id="n1" name="n1">
                                    <label for="numero">n° vía</label>
                                    <input type="text" class="form-control" name="numero" maxlength="3"
                                        value="<?php echo ($numeroVia); ?>" required>
                                </div>
                                <div class="col-md-2 text-center" id="letra1">
                                    <label for="letra_1">Letra</label>
                                    <select class="form-control" name="letra_1" required>
                                        <option value="~" <?php echo ($letra1 === '~') ? 'selected' : ''; ?>></option>
                                        <option value="a" <?php echo ($letra1 === 'a') ? 'selected' : ''; ?>>A</option>
                                        <option value="b" <?php echo ($letra1 === 'b') ? 'selected' : ''; ?>>B</option>
                                        <option value="c" <?php echo ($letra1 === 'c') ? 'selected' : ''; ?>>C</option>
                                        <option value="d" <?php echo ($letra1 === 'd') ? 'selected' : ''; ?>>D</option>
                                        <option value="e" <?php echo ($letra1 === 'e') ? 'selected' : ''; ?>>E</option>
                                        <option value="f" <?php echo ($letra1 === 'f') ? 'selected' : ''; ?>>F</option>
                                    </select>
                                </div>

                                <div class="col-md-2 text-center" id="bis1">
                                    <label for="bis">Bis</label>
                                    <select class="form-control" name="bis" required>
                                        <option value="bis" <?php echo ($bis === 'bis') ? 'selected' : ''; ?>>Bis</option>
                                        <option value="~" <?php echo ($bis === '~') ? 'selected' : ''; ?>></option>
                                    </select>
                                </div>

                                <div class="col-md-3 text-center" id="SON">
                                    <label for="direccion_sur_norte">Sur o Norte</label>
                                    <select class="form-control" name="direccion_sur_norte" required>
                                        <option value="~" <?php echo ($direccionSurNorte === '~') ? 'selected' : ''; ?>>
                                        </option>
                                        <option value="sur" <?php echo ($direccionSurNorte === 'sur') ? 'selected' : ''; ?>>Sur</option>
                                        <option value="norte" <?php echo ($direccionSurNorte === 'norte') ? 'selected' : ''; ?>>Norte</option>
                                    </select>
                                </div>
                                <label for="">#</label>
                                <div class="col-md-2 text-center" id="n2" name="n2">
                                    <label for="numero_2">n°1</label>
                                    <input type="text" class="form-control" name="numero_2" maxlength="3"
                                        value="<?php echo ($numero2); ?>" required>
                                </div>
                                <div class="col-md-2 text-center" id="letra3" name="letra3">
                                    <label for="letra_3">Letra</label>
                                    <select class="form-control" name="letra_3" required>
                                        <option value="~" <?php echo ($letra3 === '~') ? 'selected' : ''; ?>></option>
                                        <option value="a" <?php echo ($letra3 === 'a') ? 'selected' : ''; ?>>A</option>
                                        <option value="b" <?php echo ($letra3 === 'b') ? 'selected' : ''; ?>>B</option>
                                        <option value="c" <?php echo ($letra3 === 'c') ? 'selected' : ''; ?>>C</option>
                                        <option value="d" <?php echo ($letra3 === 'd') ? 'selected' : ''; ?>>D</option>
                                        <option value="e" <?php echo ($letra3 === 'e') ? 'selected' : ''; ?>>E</option>
                                        <option value="f" <?php echo ($letra3 === 'f') ? 'selected' : ''; ?>>F</option>
                                    </select>
                                </div>
                                <label for="">-</label>
                                <div class="col-md-2 text-center" id="n3" name="n3">
                                    <label for="numero_3">n°2</label>
                                    <input type="text" class="form-control" name="numero_3" maxlength="3"
                                        value="<?php echo ($numero3); ?>" required>
                                </div>
                                <div class="col-md-3 text-center" id="EOO">
                                    <label for="direccion_este_oeste">Este - Oeste</label>
                                    <select class="form-control" name="direccion_este_oeste" required>
                                        <option value="~" <?php echo ($direccionEsteOeste === '~') ? 'selected' : ''; ?>>
                                        </option>
                                        <option value="este" <?php echo ($direccionEsteOeste === 'este') ? 'selected' : ''; ?>>Este</option>
                                        <option value="oeste" <?php echo ($direccionEsteOeste === 'oeste') ? 'selected' : ''; ?>>Oeste</option>
                                    </select>
                                </div>

                                <div class="col-md-12" id="info" name="info">
                                    <label for="info_adicional">Información Adicional de Dirección</label>
                                    <textarea type="text" class="form-control" name="info_adicional" maxlength="30"
                                        required> <?php echo ($infoAdicionalDireccion); ?></textarea>
                                </div>;

                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono:</label>
                                <input type="text" class="form-control" id="telefono" name="telefono"
                                    value="<?php echo $row['Telefono']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="informacion_adicional">Información Adicional:</label>
                                <textarea class="form-control" id="informacion_adicional"
                                    name="informacion_adicional"><?php echo $row['Informacion_adicional']; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="nit">NIT:</label>
                                <input type="text" class="form-control" id="nit" name="nit"
                                    value="<?php echo $row['Nit']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="localidad">Localidad:</label>
                                <?php if ($row['localidad'] == 'Chapinero') {
                                    echo 'selected';
                                } ?>
                                <select class="form-control" name="localidad" required>
                                    <option value="" disabled selected>Seleccione La Localidad</option>
                                    <option value="Chapinero" <?php if ($row['localidad'] == 'Chapinero') {
                                        echo 'selected';
                                    } ?>>Chapinero</option>
                                    <option value="Santa Fe" <?php if ($row['localidad'] == 'Santa Fe') {
                                        echo 'selected';
                                    } ?>>Santa Fe</option>
                                    <option value="San Cristobal" <?php if ($row['localidad'] == 'San Cristobal') {
                                        echo 'selected';
                                    } ?>>San Cristobal</option>
                                    <option value="Usme" <?php if ($row['localidad'] == 'Usme') {
                                        echo 'selected';
                                    } ?>>Usmeo
                                    </option>
                                    <option value="Tunjuelito" <?php if ($row['localidad'] == 'Tunjuelito') {
                                        echo 'selected';
                                    } ?>>Tunjuelito</option>
                                    <option value="Bosa" <?php if ($row['localidad'] == 'Bosa') {
                                        echo 'selected';
                                    } ?>>Bosa
                                    </option>
                                    <option value="Kennedy" <?php if ($row['localidad'] == 'Kennedy') {
                                        echo 'selected';
                                    } ?>>
                                        Kennedy</option>
                                    <option value="Suba" <?php if ($row['localidad'] == 'Suba') {
                                        echo 'selected';
                                    } ?>>Suba
                                    </option>
                                    <option value="Usaquén" <?php if ($row['localidad'] == 'Usaquén') {
                                        echo 'selected';
                                    } ?>>
                                        Usaquén</option>
                                    <option value="Barrios Unidos" <?php if ($row['localidad'] == 'Barrios Unidos') {
                                        echo 'selected';
                                    } ?>>Barrios Unidos</option>
                                    <option value="Teusaquillo" <?php if ($row['localidad'] == 'Teusaquillo') {
                                        echo 'selected';
                                    } ?>>Teusaquillo</option>
                                    <option value="Los Mártires" <?php if ($row['localidad'] == 'Los Mártires') {
                                        echo 'selected';
                                    } ?>>Los Mártires</option>
                                    <option value="Puente Aranda" <?php if ($row['localidad'] == 'Puente Aranda') {
                                        echo 'selected';
                                    } ?>>Puente Aranda</option>
                                    <option value="La Candelaria" <?php if ($row['localidad'] == 'La Candelaria') {
                                        echo 'selected';
                                    } ?>>La Candelaria</option>
                                    <option value="Rafael Uribe Uribe" <?php if ($row['localidad'] == 'Rafael Uribe Uribe') {
                                        echo 'selected';
                                    } ?>>Rafael Uribe Uribe</option>
                                    <option value="Ciudad Bolívar" <?php if ($row['localidad'] == 'Ciudad Bolivar') {
                                        echo 'selected';
                                    } ?>>Ciudad Bolívar</option>
                                    <option value="Sumapaz" <?php if ($row['localidad'] == 'Sumapaz') {
                                        echo 'selected';
                                    } ?>>
                                        Sumapaz</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tipo_establecimiento">Tipo de Establecimiento:</label>
                                <select class="form-control" name="tipo_establecimiento" required>
                                    <option value="" disabled selected>Seleccione el Tipo de Establecimiento</option>
                                    <option value="restaurante" <?php if ($row['id_tipo_de_establecimiento'] == 'restaurante') {
                                        echo 'selected';
                                    } ?>>Restaurante</option>
                                    <option value="hotel" <?php if ($row['id_tipo_de_establecimiento'] == 'hotel') {
                                        echo 'selected';
                                    } ?>>Hotel</option>
                                    <option value="tienda" <?php if ($row['id_tipo_de_establecimiento'] == 'tienda') {
                                        echo 'selected';
                                    } ?>>Tienda</option>
                                </select>
                            </div>
                            <div class="form-group"><label class="labelfotos">
                                    Seleccionar Imagenes
                                </label>
                            </div>
                            <div class="input-group mb-3">
                                <label for="photos" class="custom-file-label">.</label>
                                <input type="file" class="custom-file-input" id="photos" name="photos[]"
                                    accept="image/*" multiple required onchange="handleFileSelect(event)">
                                <div id="image-preview" class="image-preview"></div>
                            </div>
                            <div class="thumbnail-container" id="thumbnail-container"></div>
                            <br>
                            <div class="form-group">
                                <label class="labelfotosdb" <?php echo ($resultImagenes->num_rows > 0) ? '' : 'style="display: none;"'; ?>>
                                    Imagenes del Establecimiento
                                </label>
                                <div id="thumbnail-container-bd" class="thumbnail-container-bd" <?php echo ($resultImagenes->num_rows > 0) ? '' : 'style="display: none;"'; ?>>
                                    <?php
                                    if ($resultImagenes->num_rows > 0) {
                                        $index = 0;
                                        while ($rowImagen = $resultImagenes->fetch_assoc()) {
                                            echo '<img class="thumbnail-bd" data-id="' . $rowImagen['id_imagen'] . '" data-file="' . $rowImagen['nombre_archivo'] . '" src="php/Imagen_guardar/' . $rowImagen['nombre_archivo'] . '" alt="Imagen del Establecimiento">';
                                            $index++;
                                        }
                                    } else {
                                        echo '';
                                    }
                                    ?>
                                </div>
                            </div>
                            <br>
                            <button type="submit" name="editar_establecimiento" class="btn btn-primary">Guardar
                                Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" id="imageModal1" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" class="img-fluid" src="" alt="Image Preview">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="imageModalbd" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" id="imageModal1" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalbdLabel">Image Preview</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="modalImagebd" class="img-fluid" src="" alt="Image Preview">
                </div>
                <div class="modal-footer">
                    <div class="modal-footer">
                        <button type="submit" id="btnEliminarImagen" data-id="<?php echo $establecimientoId; ?>"
                            class="btn btn-primary">Eliminar Imagen</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php
    include('modales_footer.php');
    ?>
    <footer class="footer">
        <nav>
            <ul>
                <li><a href="#" data-toggle="modal" data-target="#modalPoliticaPrivacidad">Política de
                        privacidad</a></li>
                <li><a href="#" data-toggle="modal" data-target="#modalTerminosCondiciones">Términos y
                        condiciones</a></li>
                <li><a href="#" data-toggle="modal" data-target="#modalContacto">Contacto</a></li>
                <?php
                if (isset($_SESSION['user_id'])) {
                    echo '';
                } else {
                    echo '<li><a data-toggle="modal" data-target="#myModal" href="#">¿Deseas registrar tu establecimiento?</a></li>';
                }
                ?>

            </ul>

            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Mensaje</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                            Debes estar logeado/Registrado para utilizar este servicio.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <p>©
                <?php echo date("Y"); ?> MyBog. Todos los derechos reservados.
            </p>
        </nav>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./Funcionamiento_por_js/editar_usuario.js"></script>
    <script src="./Funcionamiento_por_js/search_index.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            var thumbnails = document.querySelectorAll('.thumbnail');
            var modalImage = document.getElementById('modalImage');
            var modalTitle = document.getElementById('imageModalLabel');

            thumbnails.forEach(function (thumbnail) {
                thumbnail.addEventListener('click', function () {
                    var index = thumbnail.getAttribute('data-index');
                    var fileName = thumbnail.getAttribute('data-file');
                    modalImage.src = 'php/Imagen_guardar/' + fileName;
                    modalTitle.innerHTML = fileName;
                    $('#imageModal').modal('show');
                });
            });

            document.getElementById('photos').addEventListener('change', function (e) {
                var label = document.querySelector('.custom-file-label');
                var files = e.target.files;

                if (files.length > 1) {
                    label.textContent = files.length + ' archivos seleccionados';
                } else {
                    label.textContent = files[0].name;
                }

                handleFileSelect(files);
            });

            function handleFileSelect(files) {
                var container = document.getElementById('thumbnail-container');
                var imagePreview = document.getElementById('image-preview');
                // Limpiar el contenedor de miniaturas
                container.innerHTML = '';

                // Limpiar la imagen de la vista previa
                imagePreview.innerHTML = '';

                // Verificar si hay archivos seleccionados
                if (files.length > 0) {
                    // Mostrar el contenedor de miniaturas
                    container.style.display = 'flex';

                    // Crear miniaturas y agregar al contenedor
                    for (var i = 0; i < files.length; i++) {
                        var thumbnail = document.createElement('img');
                        thumbnail.className = 'thumbnail';
                        thumbnail.src = URL.createObjectURL(files[i]);
                        thumbnail.addEventListener('click', function (event) {
                            toggleThumbnailSelection(event, files);
                        });
                        container.appendChild(thumbnail);
                    }

                    // Mostrar la imagen seleccionada en la vista previa
                    var thumbnails = document.querySelectorAll('.thumbnail');
                    thumbnails.forEach(function (thumbnail, index) {
                        thumbnail.addEventListener('click', function () {
                            openImageModal(files, index);
                        });
                    });
                }
            }

            // Function to open the image modal
            function openImageModal(files, index) {
                var modalImage = document.getElementById('modalImage');
                var modalTitle = document.getElementById('imageModalLabel');

                modalImage.src = URL.createObjectURL(files[index]);

                // Set the modal title to the name of the image
                var imageName = files[index].name;
                modalTitle.innerHTML = imageName;

                $('#imageModal').modal('show');
            }
        });



    </script>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            var thumbnailsBd = document.querySelectorAll('.thumbnail-bd');
            var modalImageBd = document.getElementById('modalImagebd');
            var modalTitleBd = document.getElementById('imageModalbdLabel');
            var btnEliminarImagen = document.getElementById('btnEliminarImagen');

            thumbnailsBd.forEach(function (thumbnail) {
                thumbnail.addEventListener('click', function () {
                    var idImagen = thumbnail.getAttribute('data-id');
                    var fileNameBd = thumbnail.getAttribute('data-file');

                    modalImageBd.src = 'php/Imagen_guardar/' + fileNameBd;
                    modalTitleBd.innerHTML = fileNameBd;

                    // Asigna el data-id al botón de eliminar
                    btnEliminarImagen.setAttribute('data-id-imagen', idImagen);

                    $('#imageModalbd').modal('show');
                });
            });

            // Agrega un evento clic al botón Eliminar Imagen
            btnEliminarImagen.addEventListener('click', function () {
                var idImagen = this.getAttribute('data-id-imagen');

                // Realiza una solicitud AJAX para eliminar la imagen
                $.ajax({
                    type: 'POST',
                    url: window.location.href,
                    data: {
                        eliminar_imagen: true,
                        id_establecimiento: <?php echo $establecimientoId; ?>,
                        id_imagen: idImagen
                    },
                    success: function (response) {
                        // Maneja la respuesta del servidor, por ejemplo, muestra un mensaje o actualiza la interfaz de usuario.
                        alert(response);

                        // Encuentra la miniatura correspondiente por el data-id y elimínala del DOM
                        var thumbnailToRemove = document.querySelector('.thumbnail-bd[data-id="' + idImagen + '"]');
                        if (thumbnailToRemove) {
                            thumbnailToRemove.remove();
                        }

                        // Puedes cerrar el modal si lo deseas
                        $('#imageModalbd').modal('hide');
                    },
                    error: function (error) {
                        // Maneja errores, si los hay
                        console.log(error.responseText);
                    }
                });
            });
        });

    </script>

</body>

</html>
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: sans-serif;
        background-color: #ffffff;
    }

    .container {
        margin-top: 30px;

    }

    @media (max-width: 1200px) {
        .container {
            padding-bottom: 150px;
        }
    }

    .row {
        display: flex;
        flex-wrap: wrap;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
    }

    .editar h2 {
        margin: 0;
        padding: 0;
        color: #f2163e;
        text-align: center;
        margin-bottom: 30px;
    }

    .editar .form-group {
        position: relative;
    }

    .editar .form-control {
        width: 100%;
        padding: 8px;
        font-size: 16px;
        color: #000000;
        margin-bottom: 30px;
        border: none;
        border-radius: 10px;
        outline: none;
        background-color: #f5f5f5;
        transition: 0.3s;
    }

    .editar .form-control {
        border: 2px solid #cacaca;
    }

    .editar .form-control:focus {
        border: 2px solid #ff0000;
    }

    .editar .form-group label {
        color: #575757;
        font-size: 16px;
        transition: 0.3s;
        pointer-events: none;
    }

    .editar .form-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .editar .form-group {
        display: block;
        margin-bottom: 10px;
    }

    #direccion_inputs {
        margin-left: auto;
        margin-right: auto;
        width: 99%;
        padding: 8px;
        font-size: 16px;
        color: #000000;
        margin-bottom: 30px;
        border: 2px solid #cacaca;
        border-radius: 10px;
        outline: none;
        transition: 0.3s;
    }

    .editar button {
        margin: 20px auto;
        display: block;
        padding: 10px 20px;
        text-transform: uppercase;
        border-radius: 5px;
        color: #3c3c3c;
        text-decoration: none;
        overflow: hidden;
        transition: .5s;
        background-color: transparent;
        border: none;
        cursor: pointer;
        border-bottom: solid 1px rgba(255, 0, 0, 0.6);
        background-color: rgba(255, 0, 0, 0.1);
        box-shadow: 0 0 2px #ff0000, 0 0 2px #ff0000, 0 0 2px #ff0202, 0 0 0px #ff0000
    }

    .editar button:hover {
        text-decoration: none;
        background: rgba(255, 0, 0, 0.4);
        color: black;
        border-radius: 5px;
        box-shadow: 0 0 2px #ff0000, 0 0 4px #ff0000, 0 0 6px #ff0202, 0 0 8px #ff0000;
    }


    .labelfotos {
        display: block;
    }

    .btn {
        width: 30%;
        display: block;
        padding: 7px 10px;
        border-radius: 5px;
        color: #3c3c3c;
        text-decoration: none;
        overflow: hidden;
        transition: .5s;
        background-color: transparent;
        border: none;
        cursor: pointer;
        border-bottom: solid 1px rgba(255, 0, 0, 0.6);
        background-color: rgba(255, 0, 0, 0.1);
        box-shadow: 0 0 2px #ff0000, 0 0 2px #ff0000, 0 0 2px #ff0202, 0 0 0px #ff0000
    }

    .btn:hover {
        text-decoration: none;
        background: rgba(255, 0, 0, 0.4);
        color: black;
        border-radius: 5px;
        box-shadow: 0 0 2px #ff0000, 0 0 4px #ff0000, 0 0 6px #ff0202, 0 0 8px #ff0000;
    }

    .thumbnail-container {
        display: none;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        border: 2px solid #cacaca;
        padding: 10px 10px 0px 10px;
        border-radius: 10px;
        margin-top: 40px;
    }


    .thumbnail {
        border: 2px solid #cacaca;
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 10px;
        margin-right: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: border 0.3s ease;
    }

    .thumbnail:hover {
        border: 2px solid #FF0000;
    }

    .custom-file-input {
        cursor: pointer;
    }

    .custom-file-label {
        background-color: #f5f5f5;
        color: black;
        border: 2px solid #cacaca;
        padding: 8px 12px;
        border-radius: 10px;
        cursor: pointer;
        display: inline-block;
    }

    .labelfotos {
        display: block;
    }

    #image-preview {
        display: none;
    }

    #imageModal1 .modal-body {
        text-align: center;
    }

    #modalImage {
        max-height: 400px;
        height: auto;
        margin: 0 auto;
    }

    #imageModal1 .modal-content {
        background-color: white
    }

    #imageModal1 .modal-header {
        text-align: center;
        color: #3c3c3c;
    }

    #imageModal1 .modal-body {
        text-align: center;
        padding: 20px;
    }

    h2 {
        margin: 0;
        padding: 0;
        color: #f2163e;
        text-align: center;
        margin-bottom: 60px;
    }

    #thumbnail-container-bd {
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        border: 2px solid #cacaca;
        padding: 10px 10px 0px 10px;
        border-radius: 10px;
    }

    .thumbnail-bd {
        border: 2px solid #cacaca;
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 10px;
        margin-right: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: border 0.3s ease;
    }

    .thumbnail-bd:hover {
        border: 2px solid #ff0000;
    }

    .labelfotosdb {
        margin-top: 30px;
    }
</style>