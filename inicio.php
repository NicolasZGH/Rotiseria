<?php
include("conexion.php");
session_start();

#INICIO DE SESIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    #verificación del formato del nombre de usuario
    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        echo "El nombre de usuario solo debe contener letras.";
        exit();
    }

    #verificar si el usuario existe en la base de datos
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

    #verificar si el usuario y la contraseña coinciden
    $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ? AND Clave = ?");
    if ($stmt === false) {
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        #iniciar sesión si los datos son correctos
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
    #Redirigir al index.php si no hay una sesión iniciada
    if (!isset($_SESSION['username'])) {
        header("Location: index.php");
        exit();
    }
}

if (isset($_POST['eliminar_pedido'])) {
    $pedido_id = $_POST['pedido_id'];
    
    // Eliminar solo de la tabla pedidos
    $stmt = $conn->prepare("DELETE FROM pedidos WHERE idPedidos = ?");
    $stmt->bind_param("i", $pedido_id);
    
    if ($stmt->execute()) {
        echo "Pedido eliminado correctamente.";
    } else {
        echo "Error al eliminar el pedido: " . $conn->error;
    }
    
    // Recargar la página después de la eliminación
    header("Location: inicio.php");
    exit();
}




#navbar y bienvenida
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Bienvenido</title>";
echo "<link rel='stylesheet' href='style/inicio.css'>";
echo "<link rel='stylesheet' href='style/navbar.css'>"; 
echo "</head>";
echo "<body>";

#navbar
echo "<div class='navbar'>";
echo "<a href='inicio.php?section=inicio' class='active'>Inicio</a>";
echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
echo "<a href='total.php?section=total'>Total de Pedidos</a>";
echo "<a href='clientes.php?section=clientes'>Clientes</a>";
echo "<a href='productos.php?section=productos'>Productos</a>";
echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";

echo "<div class='content'>";
echo "<h1>¡Bienvenido, " . $_SESSION['username'] . "!</h1>";  


# Mostrar pedidos con precio total (cantidad * precio unitario)
$sql_pedidos = "SELECT p.idPedidos, c.Nombre AS Cliente, pr.Nombre AS Producto, p.Cantidad, pr.Precio, p.FechaPedido 
FROM Pedidos p
JOIN Clientes c ON p.idClientes = c.idClientes
JOIN Productos pr ON p.idProductos = pr.idProductos
ORDER BY p.FechaPedido DESC";
$result_pedidos = $conn->query($sql_pedidos);

if ($result_pedidos->num_rows > 0) {
    echo "<h2>Tabla de Pedidos</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Cliente</th><th>Producto</th><th>Cantidad</th><th>Precio Total</th><th>Fecha de Pedido</th><th>Acciones</th></tr>";
    while ($row = $result_pedidos->fetch_assoc()) {
        $precio_total = $row["Cantidad"] * $row["Precio"];  # Calcular el precio total
        echo "<tr>";
        echo "<td>" . $row["Cliente"] . "</td>";
        echo "<td>" . $row["Producto"] . "</td>";
        echo "<td>" . $row["Cantidad"] . "</td>";
        echo "<td>" . number_format($precio_total, 2) . "</td>";  # Mostrar precio total (formateado a dos decimales)
        echo "<td>" . $row["FechaPedido"] . "</td>";
        echo "<td>";
        # Botón para completar el pedido
        echo "<form method='POST' action='completar.php' style='display:inline-block;'>";
        echo "<input type='hidden' name='pedido_id' value='" . $row["idPedidos"] . "'>";
        echo "<button type='submit' name='transferir_pedido'>Completar</button>";
        echo "</form>";

        # Botón para editar el pedido
        echo "<form method='POST' action='editar_pedido.php' style='display:inline-block;'>";
        echo "<input type='hidden' name='pedido_id' value='" . $row["idPedidos"] . "'>";
        echo "<button type='submit' name='editar_pedido'>Editar</button>";
        echo "</form>";

        # Botón para eliminar el pedido
        echo "<form method='POST' action='eliminar_pedido.php' style='display:inline-block;'>";
        echo "<input type='hidden' name='pedido_id' value='" . $row["idPedidos"] . "'>";
        echo "<button type='submit' name='eliminar_pedido'>Eliminar</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No hay pedidos disponibles.";
}

echo "</div>"; 
echo "</body>";
echo "</html>";
?>
