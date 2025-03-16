<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

#INICIO DE SESIÓN
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        echo "El nombre de usuario solo debe contener letras.";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ?");
    if ($stmt === false) {
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "El usuario no existe en la base de datos.";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ? AND Clave = ?");
    if ($stmt === false) {
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['Usuario'];
        header("Location: inicio.php");
        exit();
    } else {
        echo "Usuario o clave incorrectos.";
        exit();
    }

    $stmt->close();

} else {
    if (!isset($_SESSION['username'])) {
        header("Location: index.php");
        exit();
    }

#NAVBAR
$section = isset($_GET['section']) ? $_GET['section'] : 'productos';

    echo "<!DOCTYPE html>";
    echo "<head>";
    echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Bienvenido</title>";
    echo "<link rel='stylesheet' href='style/inicio.css'>";
    echo "<link rel='stylesheet' href='style/navbar.css'>";
    echo "<link rel='icon' href='images/icon.png'>";
    echo "<style>
            .edit-product-form {
                max-width: 600px;
                margin: 20px auto;
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .form-group input[type='text'],
            .form-group input[type='number'],
            .form-group textarea {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .button-container {
                display: flex;
                justify-content: center; 
                margin-top: 20px; 
            }
            .button-container button {
                background-color: #e4491a;
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                margin: 0 5px;
            }
            .button-container button:hover {
                background-color: #bc3813;
            }

            .tabla-productos {
                width: 80%;
                margin: 0 auto;
                font-size: 0.9em;
                border-collapse: collapse;
                box-shadow: 0 0 10px rgba(0,0,0,0.1)
            }
            .tabla-productos th, .tabla-productos td {
                padding: 6px;
                text-align: center;
                border: 1px solid #ddd;
            }
            .boton-agregar {
                display: block;
                margin: 10px auto;
                padding: 10px 20px;
                background-color: #e45404;
                color: #fff;
                text-align: center;
                font-weight: bold;
                border-radius: 5px;
                text-decoration: none;
                width: fit-content;
            }
            .boton-agregar:hover {
                background-color: #003d82;
            }
            table {
                width: 80%;
                margin: 20px auto;
                border-collapse: separate;
                border-spacing: 0;
                border-radius: 8px;
                overflow: hidden;
            }
            th {
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            th, td {
                padding: 10px 20px;
                text-align: left;
            }
            td button {
                padding: 8px 12px;
                margin: 2px;
                font-size: 0.9em;
            }
    </style>";
    echo "</head>";
    echo "<body>";


    #barra de navegación
    echo "<div class='navbar'>";
    echo "<a href='inicio.php?section=inicio'>Inicio</a>";
    echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
    echo "<a href='total.php?section=total'>Total de Pedidos</a>";
    echo "<a href='clientes.php?section=clientes'>Clientes</a>";
    echo "<a href='productos.php?section=productos' class='active'>Productos</a>";
    echo "<a href='logout.php' style='float:right;'>Cerrar sesión</a>";
    echo "</div>";
    
    echo "<div class='content'>";

    #Contenedor de centrado para la búsqueda de productos
    echo "<div style='display: flex; justify-content: center; margin-bottom: 1rem;'>";

    #Formulario de búsqueda de productos
    echo "<div class='busqueda-producto' style='background-color: #f2f2f2; padding: 1rem; border-radius: 0.5rem; width: 500px;'>";
    echo "<form method='GET' action='productos.php' style='display: flex; align-items: center;'>";
    echo "<label for='producto' style='margin-right: 0.5rem;'>Buscar producto:</label>";
    echo "<input type='text' id='producto' name='producto' placeholder='Nombre del producto' style='padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem; width: 200px; font-size: 0.9rem;'>";
    echo "<button type='submit' style='padding: 0.5rem 1rem; background-color: #e64e08; color: #fff; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.9rem; margin-left: 0.5rem;'>Buscar</button>";

    #Botón "Volver" que redirige a la misma página sin parámetros de búsqueda
    echo "<button href='productos.php' style='padding: 0.5rem 1rem; background-color: #333; color: #fff; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.9rem; margin-left: 0.5rem;'>Volver</button>";

    echo "</form>";
    echo "</div>";
    echo "</div>";

    #Sección de Productos
    if ($section == 'productos') {

        #Verificar si se ingresó un nombre de producto en el formulario de búsqueda
        if (isset($_GET['producto']) && !empty($_GET['producto'])) {
            $producto = "%" . $_GET['producto'] . "%"; #Añadir comodines para búsqueda parcial
            $sql_productos = "SELECT idProductos, Nombre, Precio, Descripcion FROM productos WHERE Nombre LIKE ?";
            $stmt = $conn->prepare($sql_productos);
            $stmt->bind_param("s", $producto);
            $stmt->execute();
            $result_productos = $stmt->get_result();
        } else {
            #Mostrar todos los productos si no se busca un producto específico
            $sql_productos = "SELECT idProductos, Nombre, Precio, Descripcion FROM productos";
            $result_productos = $conn->query($sql_productos);
        }

        #Mostrar productos de la tabla
        if ($result_productos->num_rows > 0) {
            echo "<h2 style='margin-bottom: 10px;'>Tabla de Productos</h2>"; // Reducir el margen inferior del título
    
            // Botón con margen izquierdo
            echo "<div style='margin-left: 10%; margin-bottom: 20px;'>";
            echo "<a href='productos.php?section=agregar' class='add-product-button' style='font-size: 16px'>Agregar Producto</a>";
            echo "</div>";
            echo "<table>";
            echo "<tr><th>Producto</th><th>Precio</th><th>Descripcion</th><th>Acciones</th></tr>";
            while($row = $result_productos->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["Nombre"] . "</td>";
                echo "<td>" . $row["Precio"] . "</td>";
                echo "<td>" . $row["Descripcion"] . "</td>";
                echo "<td>";
                #Botón para editar el producto
                echo "<a href='productos.php?section=editar&producto_id=" . $row["idProductos"] . "'><button type='submit' name='editar_producto'>Editar</button></a>";
                #Botón para eliminar el producto
                echo "<form method='POST' style='display:inline-block;'>";
                echo "<input type='hidden' name='producto_id' value='" . $row["idProductos"] . "'>";
                echo "<button type='submit' name='eliminar_producto'>Eliminar</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No hay productos disponibles.";
        }
    }
    echo "<div class='content'>";

#Sección formulario para agregar un nuevo producto
if ($section == 'agregar') {

    echo "<form method='POST' action='productos.php?section=productos'>";
    
    echo "<div class='form-group'>";
    echo "<label for='Nombre'>Nombre del Producto</label>";
    echo "<input type='text' id='Nombre' name='Nombre' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='Precio'>Precio</label>";
    echo "<input type='number' step='0.01' id='Precio' name='Precio' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='Descripcion'>Descripción</label>";
    echo "<textarea id='Descripcion' name='Descripcion'></textarea>";
    echo "</div>";
    
    echo "<div class='button-container'>";
    echo "<button type='submit' name='agregar_producto' class='add-product-button'>Agregar Producto</button>";
    echo "<button type='button' onclick='window.location.href=\"productos.php?section=productos\"'>Cancelar</button>";
    echo "</div>";
    
    echo "</form>";
}
#Acción para agregar un nuevo producto
if (isset($_POST['agregar_producto'])) {
    $nombre = $_POST['Nombre'];
    $precio = $_POST['Precio'];
    $descripcion = $_POST['Descripcion'];

    #Insertar el nuevo producto en la base de datos
    $sql_insertar = "INSERT INTO productos (Nombre, Precio, Descripcion) VALUES (?, ?, ?)";
    $stmt_insertar = $conn->prepare($sql_insertar);
    $stmt_insertar->bind_param("sds", $nombre, $precio, $descripcion);
    $stmt_insertar->execute();
    $stmt_insertar->close();

    #Redirigir al usuario a la página de productos después de agregarlo
    header("Location: productos.php?section=productos");
    exit();
}

    #Acción de eliminar producto
    if (isset($_POST['eliminar_producto'])) {
        $producto_id = $_POST['producto_id'];

        #Eliminar de la tabla de productos
        $sql_delete = "DELETE FROM productos WHERE idProductos = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $producto_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        #Recargar la página
        header("Location: productos.php?section=productos");
        exit();
    }

    #Acción de editar producto
    if ($section == 'editar' && isset($_GET['producto_id'])) {
        $producto_id = $_GET['producto_id'];

        #Obtener los datos actuales del producto
        $sql_editar = "SELECT idProductos, Nombre, Precio, Descripcion FROM productos WHERE idProductos = ?";
        $stmt_editar = $conn->prepare($sql_editar);
        $stmt_editar->bind_param("i", $producto_id);
        $stmt_editar->execute();
        $result_editar = $stmt_editar->get_result();

        if ($result_editar->num_rows == 1) {
            $row = $result_editar->fetch_assoc();
        
            #Mostrar formulario para editar producto
            echo "<div class='edit-product-form'>";
            echo "<h2>Editar Producto</h2>";
            echo "<form method='POST'>";
            
            echo "<div class='form-group'>";
            echo "<label for='Nombre'>Nombre del Producto</label>";
            echo "<input type='text' id='Nombre' name='Nombre' value='" . htmlspecialchars($row['Nombre']) . "' required>";
            echo "</div>";
            
            echo "<div class='form-group'>";
            echo "<label for='Precio'>Precio</label>";
            echo "<input type='number' step='0.01' id='Precio' name='Precio' value='" . htmlspecialchars($row['Precio']) . "' required>";
            echo "</div>";
            
            echo "<div class='form-group'>";
            echo "<label for='Descripcion'>Descripción</label>";
            echo "<textarea id='Descripcion' name='Descripcion' required>" . htmlspecialchars($row['Descripcion']) . "</textarea>";
            echo "</div>";
            
            echo "<input type='hidden' name='producto_id' value='" . $row['idProductos'] . "'>";
            
            echo "<div class='button-container'>";
            echo "<button type='submit' name='actualizar_producto'>Actualizar Producto</button>";
            echo "<button type='button' onclick='window.location.href=\"productos.php?section=productos\"'>Cancelar</button>";
            echo "</div>";
            
            echo "</form>";
            echo "</div>";
        } else {
            echo "<p class='error-message'>Producto no encontrado.</p>";
        }
        $stmt_editar->close();
    }

    #Acción para actualizar producto
    if (isset($_POST['actualizar_producto'])) {
        $producto_id = $_POST['producto_id'];
        $nombre = $_POST['Nombre'];
        $precio = $_POST['Precio'];
        $descripcion = $_POST['Descripcion'];

        #Actualizar el producto en la base de datos
        $sql_actualizar = "UPDATE productos SET Nombre = ?, Precio = ?, Descripcion = ? WHERE idProductos = ?";
        $stmt_actualizar = $conn->prepare($sql_actualizar);
        $stmt_actualizar->bind_param("sdsi", $nombre, $precio, $descripcion, $producto_id);
        $stmt_actualizar->execute();
        $stmt_actualizar->close();

        #Recargar la página después de actualizar
        header("Location: productos.php?section=productos");
        exit();
    }

    echo "</div>"; #Cierre del div content
    echo "</body>";
    echo "</html>";
}
?>
