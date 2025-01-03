<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id'])) {
    $pedido_id = $_POST['pedido_id'];
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $es_mitad = isset($_POST['mitad']) ? 1 : 0;
    
    // Obtener el precio base del producto
    $sql_precio = "SELECT Precio FROM Productos WHERE idProductos = ?";
    $stmt_precio = $conn->prepare($sql_precio);
    $stmt_precio->bind_param("i", $producto_id);
    $stmt_precio->execute();
    $result_precio = $stmt_precio->get_result();
    $row_precio = $result_precio->fetch_assoc();
    $precio_base = $row_precio['Precio'];
    
    // Calcular el precio total considerando si es mitad
    $precio_final = $es_mitad ? ($precio_base * 0.5) : $precio_base;
    $precio_total = $precio_final * $cantidad;
    
    // Actualizar pedido con todos los campos necesarios
    $sql_update = "UPDATE Pedidos 
                   SET idProductos = ?, 
                       Cantidad = ?,
                       mitades = ?,
                       PrecioTotal = ?
                   WHERE idPedidos = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("iiidi", $producto_id, $cantidad, $es_mitad, $precio_total, $pedido_id);

    if ($stmt->execute()) {
        header("Location: inicio.php");
        exit();
    } else {
        echo "Error al actualizar el pedido: " . $conn->error;
    }

    $stmt->close();
}
$conn->close();
?>
