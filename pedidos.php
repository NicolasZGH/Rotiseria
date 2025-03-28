<?php
#Conexión y preparación de datos
include("conexion.php");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$fechaActual = date("Y-m-d");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['productos']) && isset($_POST['nombreCliente']) && isset($_POST['apellidoCliente']) && isset($_POST['telefono']) && isset($_POST['direccion'])) {
    $productos = $_POST['productos'];
    $cantidades = $_POST['cantidades'];
    $mitades = isset($_POST['mitades']) ? $_POST['mitades'] : [];
    $nombreCliente = $_POST['nombreCliente'];
    $apellidoCliente = $_POST['apellidoCliente'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    $esReserva = isset($_POST['reserva']) && $_POST['reserva'] == '1' ? 1 : 0;
    $fechaEntrega = null;

    if ($esReserva && !empty($_POST['fecha_entrega']) && !empty($_POST['hora_entrega'])) {
        $fechaEntrega = $_POST['fecha_entrega'] . ' ' . $_POST['hora_entrega'] . ':00';
    }

    $sql_cliente = "SELECT idClientes FROM Clientes WHERE Nombre = ? AND Apellido = ? AND Telefono = ? AND Direccion = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("ssss", $nombreCliente, $apellidoCliente, $telefono, $direccion);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();

    if ($result_cliente->num_rows > 0) {
        $row_cliente = $result_cliente->fetch_assoc();
        $idCliente = $row_cliente['idClientes'];
    } else {
        $sql_insert_cliente = "INSERT INTO Clientes (Nombre, Apellido, Telefono, Direccion) VALUES (?, ?, ?, ?)";
        $stmt_insert_cliente = $conn->prepare($sql_insert_cliente);
        $stmt_insert_cliente->bind_param("ssss", $nombreCliente, $apellidoCliente, $telefono, $direccion);
        if ($stmt_insert_cliente->execute()) {
            $idCliente = $stmt_insert_cliente->insert_id;
        } else {
            die("Error al agregar el cliente: " . $conn->error);
        }
    }

    foreach ($productos as $idProducto) {
        $cantidad = intval($cantidades[$idProducto]);
    
        #Obtener el precio base del producto
        $sql_precio = "SELECT Precio FROM Productos WHERE idProductos = ?";
        $stmt_precio = $conn->prepare($sql_precio);
        $stmt_precio->bind_param("i", $idProducto);
        $stmt_precio->execute();
        $result_precio = $stmt_precio->get_result();
        $row_precio = $result_precio->fetch_assoc();
        $precio = $row_precio['Precio'];
    
        #Determinar si es mitad
        $esMitad = in_array($idProducto, $mitades) ? 1 : 0;
    
        #Aplicar descuento si es mitad
        if ($esMitad) {
            $precio = $precio * 0.5;
        }
    
        #Calcular el precio total
        $preciototal = $precio * $cantidad;
    
        #Insertar el pedido con el campo mitades
        $sql_insert_pedido = "INSERT INTO Pedidos (idProductos, idClientes, Cantidad, Reservado, Fecha_Entrega, PrecioTotal, Mitades) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_pedido = $conn->prepare($sql_insert_pedido);
        $stmt_insert_pedido->bind_param("iiiisdi", $idProducto, $idCliente, $cantidad, $esReserva, $fechaEntrega, $preciototal, $esMitad);
    
        if (!$stmt_insert_pedido->execute()) {
            die("Error al agregar el pedido: " . $conn->error);
        }
    }
    

    header("Location: inicio.php");
    exit();
}

$sql_productos = "SELECT * FROM Productos";
$result_productos = $conn->query($sql_productos);
$sql_clientes = "SELECT Nombre, Apellido, Telefono, Direccion FROM Clientes ORDER BY Nombre ASC";
$result_clientes = $conn->query($sql_clientes);

$productos = [];
if ($result_productos->num_rows > 0) {
    while ($row = $result_productos->fetch_assoc()) {
        $categoria = explode(' ', $row["Nombre"])[0];
        $productos[$categoria][] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="style/pedidos.css">
    <link rel="icon" href="images/icon.png">
    <link rel='stylesheet' href='style/navbar.css'>
    <style>
    #fecha-entrega-group {
        display: none;
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-top: 15px;
        border: 2px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    #reserva:checked ~ #fecha-entrega-group {
        display: block;
    }

    #fecha-entrega-group input[type="date"],
    #fecha-entrega-group input[type="time"] {
        width: 200px;
        padding: 10px;
        margin: 8px 0 15px 0;
        border: 2px solid #ced4da;
        border-radius: 6px;
        font-size: 15px;
        background-color: white;
    }

    #fecha-entrega-group label {
        display: block;
        margin-top: 12px;
        color: #2c3e50;
        font-weight: bold;
        font-size: 20px;
    }

    #fecha-entrega-group small {
        display: block;
        color: #6c757d;
        font-size: 13px;
        margin-top: -10px;
        margin-bottom: 12px;
    }

    .form-group label[for="reserva"] {
        font-weight: bold;
        color: #2c3e50;
        margin-right: 12px;
        font-size: 20px;
    }

    /*Checkbox más grande y personalizado */
    #reserva {
        transform: scale(1.8);
        margin-right: 8px;
        vertical-align: middle;
        cursor: pointer;
        accent-color: #0056b3;
    }

    /*efectos hover mejorados */
    #fecha-entrega-group input:hover {
        border-color: #90a4ae;
        transition: all 0.3s ease;
    }

    #fecha-entrega-group input:focus {
        outline: none;
        border-color: #0056b3;
        box-shadow: 0 0 0 3px rgba(0,86,179,0.25);
    }

    /*estilo para el contenedor del checkbox */
    .form-group {
        margin: 20px 0;
    }

    /*estilo para cuando el checkbox está marcado */
    #reserva:checked {
        background-color: #0056b3;
    }
    
    label[for="reserva"]:hover {
        cursor: pointer;
        color: #0056b3;
    }
</style>
</head>
<body>

<h2 id="realizarpedido">Realizar Pedido</h2>

<form method="POST" action="pedidos.php">
    <div class="form-group">
        <label for="nombreCliente">Nombre del Cliente:</label>
        <input type="text" id="nombreCliente" name="nombreCliente" required 
                pattern="[A-Za-z\s]+" title="Solo letras y espacios permitidos" 
                list="clientes_sugeridos"
                oninput="let [nombre, apellido, tel] = this.value.split(' | '); 
                        this.value = nombre || '';
                        document.getElementById('apellidoCliente').value = apellido || '';
                        document.getElementById('telefono').value = tel || '';"><br><br>
        
        <label for="apellidoCliente">Apellido del Cliente:</label>
        <input type="text" id="apellidoCliente" name="apellidoCliente" required 
                pattern="[A-Za-z\s]+" title="Solo letras y espacios permitidos"><br><br>
        
        <datalist id="clientes_sugeridos">
            <?php while ($row = $result_clientes->fetch_assoc()): ?>
                <option value="<?php echo $row['Nombre'] . ' | ' . $row['Apellido'] . ' | ' . $row['Telefono']; ?>">
            <?php endwhile; ?>
        </datalist>
        
        <label for="telefono">Teléfono del Cliente:</label>
        <input type="tel" id="telefono" name="telefono" required 
                title="Ingrese un número telefónico"><br><br>

        <label for="">Dirección del Cliente:</label>
        <input type="dir" id="direccion" name="direccion"
                title="Ingrese la dirección del cliente">
    </div>

    <div class="form-group">
        <h3>Seleccionar Producto y Cantidad</h3>
        <?php foreach ($productos as $categoria => $items): ?>
            <div class="categoria-producto">
                <img src="images/<?php echo strtolower($categoria); ?>.png" alt="<?php echo $categoria; ?>" class="categoria-imagen">
                <div class="categoria-items">
                    <?php foreach ($items as $producto): ?>
                        <div class="producto-item">
                            <label>
                                <input type="checkbox" name="productos[]" value="<?php echo $producto['idProductos']; ?>">
                                <?php echo $producto['Nombre']; ?>
                            </label>
                            <label for="cantidad_<?php echo $producto['idProductos']; ?>">Cantidad:</label>
                            <input type="number" id="cantidad_<?php echo $producto['idProductos']; ?>" 
                                    name="cantidades[<?php echo $producto['idProductos']; ?>]" value="1" min="1">
                            <label>
                                <input type="checkbox" name="mitades[]" value="<?php echo $producto['idProductos']; ?>">
                                Mitad
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


    
    <div class="form-group">
        <label for="reserva">¿Es una reserva?</label>
        <input type="checkbox" id="reserva" name="reserva" value="1">
        <div id="fecha-entrega-group">
            <label for="fecha_entrega">Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" min="<?php echo $fechaActual; ?>">
            <br>
            <label for="hora_entrega">Hora de Entrega:</label>
            <input type="time" id="hora_entrega" name="hora_entrega" min="18:00" max="01:00">
            <small>(Horario disponible: 18:00 a 01:00 Horas)</small>
        </div>
    </div>

    <input type="submit" value="Completar Pedido">
</form>

<div class='navbar'>
    <a href='inicio.php'>Inicio</a>
    <a href='pedidos.php' class='active'>Agregar Pedidos</a>
    <a href='total.php'>Total de Pedidos</a>
    <a href='clientes.php?section=clientes'>Clientes</a>
    <a href='productos.php?section=productos'>Productos</a>
    <a href='logout.php' style='float:right;'>Cerrar sesión</a>
</div>

</body>
</html>
