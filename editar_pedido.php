<?php
include("conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido</title>
    <link rel="stylesheet" href="style/inicio.css">
    <link rel="stylesheet" href="style/table-styles.css">
</head>
<body>

<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        .edit-form {
            display: flex;
            flex-direction: column;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #e4491a;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #bc3813;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-top: 20px;
        }
        .form-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: white;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            font-size: 1rem;
        }

        .form-select {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
        }

        .mitad-checkbox {
    margin-top: 10px; /* Ajusta este valor si deseas más separación */
        }

        .mitad-checkbox input[type="checkbox"] {
            transform: scale(1.5); /* Mantiene el tamaño */
            margin-left: -200px; /* Ajusta la posición horizontal del checkbox */
        }

        .mitad-checkbox label {
            display: block; /* Hace que el texto quede en la parte superior */
            margin-bottom: 5px; /* Espacio entre el texto y el checkbox */
        }


        

    </style>

    <div class="container">
        <h1>Editar Pedido</h1>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id'])) {
            $pedido_id = $_POST['pedido_id'];

            #Consultar datos actuales del pedido
            $sql_pedido = "SELECT p.idPedidos, c.Nombre AS Cliente, pr.idProductos, pr.Nombre AS Producto, 
                            pr.Precio, p.Cantidad, p.mitades 
                            FROM Pedidos p
                            JOIN Clientes c ON p.idClientes = c.idClientes
                            JOIN Productos pr ON p.idProductos = pr.idProductos
                            WHERE p.idPedidos = ?";
            $stmt = $conn->prepare($sql_pedido);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $pedido = $result->fetch_assoc();

                #Obtener todos los productos para la lista desplegable
                $productos_result = $conn->query("SELECT idProductos, Nombre, Precio FROM Productos");
                ?>

                <form method="POST" action="guardar_edicion_pedido.php" class="edit-form">
                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['idPedidos']; ?>">

                    <div class="form-group">
                        <label for="cliente">Cliente:</label>
                        <input type="text" id="cliente" name="cliente" value="<?php echo $pedido['Cliente']; ?>" required readonly>
                    </div>

                    <div class="form-group">
                        <label for="producto">Producto:</label>
                        <select id="producto" name="producto_id" class="form-select" required onchange="actualizarPrecio()">
                            <option value="">Seleccione un producto</option>
                            <?php while ($producto = $productos_result->fetch_assoc()) { ?>
                                <option value="<?php echo $producto['idProductos']; ?>" 
                                        data-precio="<?php echo $producto['Precio']; ?>" 
                                        <?php if ($producto['idProductos'] == $pedido['idProductos']) echo 'selected'; ?>>
                                    <?php echo $producto['Nombre']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" value="<?php echo $pedido['Cantidad']; ?>" required min="1" oninput="actualizarPrecio()">
                    </div>

                    <div class="form-group mitad-checkbox">
                        <label for="mitad">¿Es mitad?</label>
                        <input type="checkbox" id="mitad" name="mitad" value="1" <?php echo ($pedido['mitades'] == 1) ? 'checked' : ''; ?> onchange="actualizarPrecio()">
                    </div>

                    <div class="form-group">
                        <label for="total">Total:</label>
                        <input type="text" id="total" name="total" readonly>
                    </div>

                    <button type="submit">Guardar Cambios</button>
                    <a href="inicio.php" style="text-decoration: none;">
                        <button type="button" style="background-color: #888;">Cancelar</button>
                    </a>
                </form>

                <script>
                    function actualizarPrecio() {
                        let productoSelect = document.getElementById("producto");
                        let precio = parseFloat(productoSelect.options[productoSelect.selectedIndex].getAttribute("data-precio"));
                        let cantidad = parseFloat(document.getElementById("cantidad").value);
                        let esMitad = document.getElementById("mitad").checked;

                        //si es mitad, aplicar 50% de descuento
                        if (esMitad) {
                            precio = precio * 0.5;
                        }

                        let total = precio * cantidad;
                        document.getElementById("total").value = total.toFixed(2);
                    }

                    //calcular el precio inicial cuando se carga la página
                    window.onload = actualizarPrecio;
                </script>

                <?php
            } else {
                echo "<p class='error'>No se encontró el pedido.</p>";
            }

            $stmt->close();
        } else {
            echo "<p class='error'>No se recibió un ID de pedido válido.</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
