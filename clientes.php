<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

#INICIO DE SESIÓN
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

#NAVBAR
$section = isset($_GET['section']) ? $_GET['section'] : 'clientes';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Clientes</title>";
echo "<link rel='stylesheet' href='style/inicio.css'>";
echo "<link rel='stylesheet' href='style/navbar.css'>";
echo "<link rel='icon' href='images/icon.png'>";
echo "<body>";

#Barra de navegación
echo "<div class='navbar'>";
echo "<a href='inicio.php?section=inicio'>Inicio</a>";
echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
echo "<a href='total.php?section=total'>Total de Pedidos</a>";
echo "<a href='clientes.php?section=clientes' class='active'>Clientes</a>";
echo "<a href='productos.php?section=productos'>Productos</a>";
echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";

echo "<div class='content'>";

#Contenedor de centrado para la búsqueda de clientes
echo "<div style='display: flex; justify-content: center; margin-bottom: 1rem;'>";

#Formulario de búsqueda de clientes
echo "<div class='busqueda-cliente' style='background-color: #f2f2f2; padding: 1rem; border-radius: 0.5rem; width: 500px;'>";
echo "<form method='GET' action='clientes.php' style='display: flex; align-items: center;'>";
echo "<label for='cliente' style='margin-right: 0.5rem;'>Buscar cliente:</label>";
echo "<input type='text' id='cliente' name='cliente' placeholder='Nombre del cliente' style='padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem; width: 200px; font-size: 0.9rem;'>";
echo "<button type='submit' style='padding: 0.5rem 1rem; background-color: #e64e08; color: #fff; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.9rem; margin-left: 0.5rem;'>Buscar</button>";

#Botón "Volver" que redirige a la misma página sin parámetros de búsqueda
echo "<button href='clientes.php' style='padding: 0.5rem 1rem; background-color: #333; color: #fff; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.9rem; margin-left: 0.5rem;'>Volver</button>";

echo "</form>";
echo "</div>";
echo "</div>";

#Verificar si se ingresó un nombre de cliente en el formulario de búsqueda
if (isset($_GET['cliente']) && !empty($_GET['cliente'])) {
    $cliente = "%" . $_GET['cliente'] . "%"; #Añadir comodines para búsqueda parcial
    $sql_clientes = "SELECT idClientes, Nombre, Apellido, Email, Direccion, Telefono, DNI FROM clientes WHERE Nombre LIKE ?";
    $stmt = $conn->prepare($sql_clientes);
    $stmt->bind_param("s", $cliente);
    $stmt->execute();
    $result_clientes = $stmt->get_result();
} else {
    #Mostrar todos los clientes si no se busca un cliente específico
    $sql_clientes = "SELECT idClientes, Nombre, Apellido, Email, Direccion, Telefono, DNI FROM clientes";
    $result_clientes = $conn->query($sql_clientes);
}

#Sección de Clientes
if ($section == 'clientes') {
    if ($result_clientes->num_rows > 0) {
        echo "<h2>Tabla de Clientes</h2>";
        echo "<table>";
        echo "<tr><th>Nombre</th><th>Apellido</th><th>Email</th><th>Dirección</th><th>Teléfono</th><th>DNI</th><th>Acciones</th></tr>";
        while($row = $result_clientes->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["Nombre"] . "</td>";
            echo "<td>" . $row["Apellido"] . "</td>";
            echo "<td>" . $row["Email"] . "</td>";
            echo "<td>" . $row["Direccion"] . "</td>";
            echo "<td>" . $row["Telefono"] . "</td>";
            echo "<td>" . $row["DNI"] . "</td>";
            echo "<td>";
            
            #Botón para editar el cliente
            echo "<a href='clientes.php?section=editar&cliente_id=" . $row["idClientes"] . "'><button type='button' name='editar_cliente'>Editar</button></a>";
            
            #Botón para eliminar el cliente
            echo "<form method='POST' style='display:inline-block;'>";
            echo "<input type='hidden' name='cliente_id' value='" . $row["idClientes"] . "'>";
            echo "<button type='submit' name='eliminar_cliente'>Eliminar</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay clientes disponibles.";
    }
}

#Acción de eliminar cliente
if (isset($_POST['eliminar_cliente'])) {
    $cliente_id = $_POST['cliente_id'];
    $sql_delete_cliente = "DELETE FROM clientes WHERE idClientes = ?";
    $stmt_delete_cliente = $conn->prepare($sql_delete_cliente);
    $stmt_delete_cliente->bind_param("i", $cliente_id);
    $stmt_delete_cliente->execute();
    $stmt_delete_cliente->close();

    #Recargar la página
    header("Location: clientes.php?section=clientes");
    exit();
}

#Acción de editar cliente
if ($section == 'editar' && isset($_GET['cliente_id'])) {
    $cliente_id = $_GET['cliente_id'];
    $sql_editar_cliente = "SELECT idClientes, Nombre, Apellido, Email, Direccion, Telefono, DNI FROM clientes WHERE idClientes = ?";
    $stmt_editar_cliente = $conn->prepare($sql_editar_cliente);
    $stmt_editar_cliente->bind_param("i", $cliente_id);
    $stmt_editar_cliente->execute();
    $result_editar_cliente = $stmt_editar_cliente->get_result();

    if ($result_editar_cliente->num_rows == 1) {
        $row = $result_editar_cliente->fetch_assoc();
    
        echo "<div class='edit-client-form'>";
        echo "<h2>Editar Cliente</h2>";
        echo "<form method='POST'>";
        
        echo "<div class='form-row'>";
        echo "<div class='form-group'>";
        echo "<label for='Nombre'>Nombre</label>";
        echo "<input type='text' id='Nombre' name='Nombre' value='" . htmlspecialchars($row['Nombre']) . "' required>";
        echo "</div>";
        
        echo "<div class='form-group'>";
        echo "<label for='Apellido'>Apellido</label>";
        echo "<input type='text' id='Apellido' name='Apellido' value='" . htmlspecialchars($row['Apellido']) . "' required>";
        echo "</div>";
        echo "</div>";
        
        echo "<div class='form-row'>";
        echo "<div class='form-group'>";
        echo "<label for='Email'>Email</label>";
        echo "<input type='email' id='Email' name='Email' value='" . htmlspecialchars($row['Email']) . "'>";
        echo "</div>";
        
        echo "<div class='form-group'>";
        echo "<label for='DNI'>DNI</label>";
        echo "<input type='text' id='DNI' name='DNI' value='" . htmlspecialchars($row['DNI']) . "'>";
        echo "</div>";
        echo "</div>";
        
        echo "<div class='form-row'>";
        echo "<div class='form-group'>";
        echo "<label for='Telefono'>Teléfono</label>";
        echo "<input type='tel' id='Telefono' name='Telefono' value='" . htmlspecialchars($row['Telefono']) . "'>";
        echo "</div>";
        
        echo "<div class='form-group'>";
        echo "<label for='Direccion'>Dirección</label>";
        echo "<input type='text' id='Direccion' name='Direccion' value='" . htmlspecialchars($row['Direccion']) . "'>";
        echo "</div>";
        echo "</div>";
        
        echo "<input type='hidden' name='cliente_id' value='" . $row['idClientes'] . "'>";
        
        echo "<div class='button-container'>";

        echo "<button type='submit' name='actualizar_cliente'>Actualizar Cliente</button>";
        echo "<button type='button' onclick='window.location.href=\"clientes.php?section=clientes\"'>Cancelar</button>";
        echo "</div>";
        
        echo "</form>";
        echo "</div>";
    } else {
        echo "<p class='error-message'>Cliente no encontrado.</p>";
    }
    $stmt_editar_cliente->close();
}

#Acción para actualizar cliente
if (isset($_POST['actualizar_cliente'])) {
    $cliente_id = $_POST['cliente_id'];
    $nombre = $_POST['Nombre'];
    $apellido = $_POST['Apellido'];
    $email = $_POST['Email'];
    $direccion = $_POST['Direccion'];
    $telefono = $_POST['Telefono'];
    $dni = $_POST['DNI'];

    $sql_update_cliente = "UPDATE clientes SET Nombre = ?, Apellido = ?, Email = ?, Direccion = ?, Telefono = ?, DNI = ? WHERE idClientes = ?";
    $stmt_update_cliente = $conn->prepare($sql_update_cliente);
    $stmt_update_cliente->bind_param("ssssssi", $nombre, $apellido, $email, $direccion, $telefono, $dni, $cliente_id);
    $stmt_update_cliente->execute();
    $stmt_update_cliente->close();

    #Recargar la página
    header("Location: clientes.php?section=clientes");
    exit();
}

#cerrar la conexión a la base de datos
$conn->close();

echo "</div>";
echo "</body>";
echo "</html>";
?>
