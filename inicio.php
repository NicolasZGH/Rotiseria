<?php
include("conexion.php");
session_start();

# VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

# VERIFICACIÓN DE TIEMPO DE INACTIVIDAD (60 MINUTOS MÁXIMO)
$inactive = 3600; // 60 minutos en segundos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}

# VERIFICAR EL TIEMPO DE SESIÓN (8 HORAS)
$maxLifetime = 28800; // 8 horas en segundos
if (isset($_SESSION['created']) && (time() - $_SESSION['created'] > $maxLifetime)) {
    session_unset();
    session_destroy();
    header("Location: index.php?expired=1");
    exit();
}

# ACTUALIZACIÓN DEL TIEMPO DE ÚLTIMA ACTIVIDAD
$_SESSION['last_activity'] = time();

# PREVENIR CACHÉ
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Expires: Sun, 02 Jan 2023 00:00:00 GMT');

# HTML
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Bienvenido</title>";
echo "<link rel='icon' href='images/icon.png'>";
echo "<link rel='icon' type='image/x-icon' href='images/icono.ico'>";
echo "<link rel='stylesheet' href='style/inicio.css'>";
echo "<link rel='stylesheet' href='style/navbar.css'>";
echo "<link rel='stylesheet' href='style/ayuda.css'>";
echo "<title>Inicio</title>";
echo "<script>
    function confirmarEliminacion() {
    return confirm('¿Está seguro de que desea eliminar este pedido? Esta acción no se puede deshacer.');
}
</script>";
echo "</head>";
echo "<body>";

# NAVBAR
echo "<div class='navbar'>";
echo "<a href='inicio.php?section=inicio' class='active'>Inicio</a>";
echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
echo "<a href='total.php?section=total'>Total de Pedidos</a>";
echo "<a href='clientes.php?section=clientes'>Clientes</a>";
echo "<a href='productos.php?section=productos'>Productos</a>";
echo "<a href='logout.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";

echo "<div class='content'>";
echo "<h1>¡Bienvenido, " . htmlspecialchars($_SESSION['username']) . "! 🍕</h1>";

# CONSULTAR PEDIDOS
$sql_pedidos = "SELECT c.idClientes, c.Nombre AS Cliente, c.Apellido, c.Telefono, 
                        pr.Nombre AS Producto, p.Cantidad, pr.Precio, p.FechaPedido, 
                        p.Fecha_Entrega, p.idPedidos, p.mitades, p.Reservado,
                        CASE 
                            WHEN p.mitades = 1 THEN (p.Cantidad * pr.Precio * 0.5)
                            ELSE (p.Cantidad * pr.Precio)
                        END AS PrecioTotalProducto
                FROM Pedidos p
                JOIN Clientes c ON p.idClientes = c.idClientes
                JOIN Productos pr ON p.idProductos = pr.idProductos
                ORDER BY p.FechaPedido DESC";
$result_pedidos = $conn->query($sql_pedidos);

$pedidosNormales = [];
$pedidosReservados = [];
$totalPorClienteNormales = [];
$totalPorClienteReservados = [];

if ($result_pedidos->num_rows > 0) {
    while ($row = $result_pedidos->fetch_assoc()) {
        $clienteKey = htmlspecialchars($row['idClientes']); // Agrupación única por ID del cliente
        $clienteVisible = htmlspecialchars($row['Cliente'] . ' ' . $row['Apellido'] . ' (' . $row['Telefono'] . ')');

        if ($row['Reservado'] == 1) {
            $pedidosReservados[$clienteKey]['cliente'] = $clienteVisible;
            $pedidosReservados[$clienteKey]['pedidos'][] = $row;

            if (!isset($totalPorClienteReservados[$clienteKey])) {
                $totalPorClienteReservados[$clienteKey] = 0;
            }
            $totalPorClienteReservados[$clienteKey] += $row['PrecioTotalProducto'];
        } else {
            $pedidosNormales[$clienteKey]['cliente'] = $clienteVisible;
            $pedidosNormales[$clienteKey]['pedidos'][] = $row;

            if (!isset($totalPorClienteNormales[$clienteKey])) {
                $totalPorClienteNormales[$clienteKey] = 0;
            }
            $totalPorClienteNormales[$clienteKey] += $row['PrecioTotalProducto'];
        }
    }
}

# BUSCADOR DE PEDIDOS
echo "<div style='display: flex; justify-content: center; margin-bottom: 1rem;'>";
echo "<div class='busqueda-cliente' style='background-color: #f2f2f2; padding: 1rem; border-radius: 0.5rem; width: 500px;'>";
echo "<form method='GET' action='inicio.php' style='display: flex; align-items: center;'>";
echo "<label for='cliente_busqueda' style='margin-right: 0.5rem;'>Buscar cliente:</label>";
echo "<input type='text' id='cliente_busqueda' name='cliente_busqueda' placeholder='Nombre del cliente' style='padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem; width: 200px; font-size: 0.9rem;'>";
echo "<button type='submit' style='padding: 0.5rem 1rem; background-color: #e64e08; color: #fff; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.9rem; margin-left: 0.5rem;'>Buscar</button>";
echo "<a href='inicio.php' style='padding: 0.6rem 1rem; background-color: #333; color: #fff; text-decoration: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.9rem; margin-top: revert;'>Volver</a>";
echo "</form>";
echo "</div>";
echo "</div>";

# PROCESAR BÚSQUEDA
$filtro_cliente = isset($_GET['cliente_busqueda']) && !empty($_GET['cliente_busqueda']) ? $_GET['cliente_busqueda'] : null;

# CONSULTA DE PEDIDOS CON FILTRO
$sql_pedidos = "SELECT c.idClientes, c.Nombre AS Cliente, c.Apellido, c.Telefono, 
                        pr.Nombre AS Producto, p.Cantidad, pr.Precio, p.FechaPedido, 
                        p.Fecha_Entrega, p.idPedidos, p.mitades, p.Reservado,
                        CASE 
                            WHEN p.mitades = 1 THEN (p.Cantidad * pr.Precio * 0.5)
                            ELSE (p.Cantidad * pr.Precio)
                        END AS PrecioTotalProducto
                FROM Pedidos p
                JOIN Clientes c ON p.idClientes = c.idClientes
                JOIN Productos pr ON p.idProductos = pr.idProductos";

if ($filtro_cliente) {
    # Separar términos de búsqueda (por si es nombre y apellido)
    $terminos = explode(' ', $filtro_cliente);
    
    if (count($terminos) > 1) {
        # Buscar por nombre Y apellido
        $sql_pedidos .= " WHERE (c.Nombre LIKE ? AND c.Apellido LIKE ?)";
        $param1 = "%" . $terminos[0] . "%";
        $param2 = "%" . $terminos[1] . "%";
    } else {
        # Buscar por nombre O apellido
        $sql_pedidos .= " WHERE (c.Nombre LIKE ? OR c.Apellido LIKE ?)";
        $param1 = "%" . $filtro_cliente . "%";
        $param2 = "%" . $filtro_cliente . "%";
    }
}

$sql_pedidos .= " ORDER BY p.FechaPedido DESC";

$pedidosNormales = [];
$pedidosReservados = [];
$totalPorClienteNormales = [];
$totalPorClienteReservados = [];

$stmt = $conn->prepare($sql_pedidos);

if ($filtro_cliente) {
    if (count($terminos) > 1) {
        $stmt->bind_param("ss", $param1, $param2);
    } else {
        $stmt->bind_param("ss", $param1, $param2);
    }
}

$stmt->execute();
$result_pedidos = $stmt->get_result();

if ($result_pedidos->num_rows > 0) {
    while ($row = $result_pedidos->fetch_assoc()) {
        $clienteKey = htmlspecialchars($row['idClientes']); 
        $clienteVisible = htmlspecialchars($row['Cliente'] . ' ' . $row['Apellido'] . ' (' . $row['Telefono'] . ')');

        if ($row['Reservado'] == 1) {
            $pedidosReservados[$clienteKey]['cliente'] = $clienteVisible;
            $pedidosReservados[$clienteKey]['pedidos'][] = $row;

            if (!isset($totalPorClienteReservados[$clienteKey])) {
                $totalPorClienteReservados[$clienteKey] = 0;
            }
            $totalPorClienteReservados[$clienteKey] += $row['PrecioTotalProducto'];
        } else {
            $pedidosNormales[$clienteKey]['cliente'] = $clienteVisible;
            $pedidosNormales[$clienteKey]['pedidos'][] = $row;

            if (!isset($totalPorClienteNormales[$clienteKey])) {
                $totalPorClienteNormales[$clienteKey] = 0;
            }
            $totalPorClienteNormales[$clienteKey] += $row['PrecioTotalProducto'];
        }
    }
}

# MOSTRAR PEDIDOS NORMALES

echo "<h2>Pedidos Agrupados por Clientes</h2>";
if (count($pedidosNormales) > 0) {
    echo "<table class='tabla-pedidos' border='0'>";
    foreach ($pedidosNormales as $clienteKey => $data) {
        echo "<tr><th colspan='5' style='background-color:#473d38'>Cliente: " . htmlspecialchars($data['cliente']) . " | Total Acumulado: $" . number_format($totalPorClienteNormales[$clienteKey], 2) . "</th></tr>";
        echo "<tr><th>Producto</th><th>Cantidad</th><th>Precio Total</th><th>Fecha de Pedido</th><th>Acciones</th></tr>";
        foreach ($data['pedidos'] as $pedido) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($pedido['Producto']) . 
                (isset($pedido['mitades']) && $pedido['mitades'] == 1 ? " (Mitad)" : "") . "</td>";
            echo "<td>" . htmlspecialchars($pedido['Cantidad']) . "</td>";
            echo "<td>$" . number_format($pedido['PrecioTotalProducto'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($pedido['FechaPedido']) . "</td>";
            echo "<td>";
            echo "<form method='POST' action='completar.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . htmlspecialchars($pedido["idPedidos"]) . "'>";
            echo "<button type='submit' name='transferir_pedido'>Completar</button>";
            echo "</form>";
            echo "<form method='POST' action='editar_pedido.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . htmlspecialchars($pedido["idPedidos"]) . "'>";
            echo "<button type='submit' name='editar_pedido'>Editar</button>";
            echo "</form>";
            echo "<form method='POST' action='eliminar_pedido.php' style='display:inline-block;' onsubmit='return confirmarEliminacion();'>";
            echo "<input type='hidden' name='pedido_id' value='" . htmlspecialchars($pedido["idPedidos"]) . "'>";
            echo "<button type='submit' name='eliminar_pedido'>Eliminar</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "<h4>No hay pedidos disponibles. Agregue un pedido desde la página <a href='pedidos.php' style='color: #e4491a; font-weight: bold;'>agregar pedidos</a></h4>";
}


# MOSTRAR PEDIDOS RESERVADOS
echo "<h2>Pedidos Reservados</h2>";
if (count($pedidosReservados) > 0) {
    echo "<table class='tabla-pedidos' border='0'>";
    foreach ($pedidosReservados as $clienteKey => $data) {
        echo "<tr><th colspan='5' style='background-color:#b3180b'>Cliente: " . htmlspecialchars($data['cliente']) . " | Total Acumulado: $" . number_format($totalPorClienteReservados[$clienteKey], 2) . "</th></tr>";
        echo "<tr><th>Producto</th><th>Cantidad</th><th>Precio Total</th><th>Fecha y Hora de Entrega</th><th>Acciones</th></tr>";
        foreach ($data['pedidos'] as $pedido) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($pedido['Producto']) . 
                (isset($pedido['mitades']) && $pedido['mitades'] == 1 ? " (Mitad)" : "") . "</td>";
            echo "<td>" . htmlspecialchars($pedido['Cantidad']) . "</td>";
            echo "<td>$" . number_format($pedido['PrecioTotalProducto'], 2) . "</td>";
            $fechaEntrega = !empty($pedido['Fecha_Entrega']) ? date('d/m/Y H:i', strtotime($pedido['Fecha_Entrega'])) : 'No especificada';
            echo "<td>" . htmlspecialchars($fechaEntrega) . "</td>";
            echo "<td>";
            echo "<form method='POST' action='completar.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . htmlspecialchars($pedido["idPedidos"]) . "'>";
            echo "<button type='submit' name='transferir_pedido'>Completar</button>";
            echo "</form>";
            echo "<form method='POST' action='editar_pedido.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . htmlspecialchars($pedido["idPedidos"]) . "'>";
            echo "<button type='submit' name='editar_pedido'>Editar</button>";
            echo "</form>";
            echo "<form method='POST' action='eliminar_pedido.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . htmlspecialchars($pedido["idPedidos"]) . "'>";
            echo "<button type='submit' name='eliminar_pedido'>Eliminar</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "<h4>No hay pedidos disponibles. Agregue un pedido desde la página <a href='pedidos.php' style='color: #e4491a; font-weight: bold;'>agregar pedidos</a></h4>";
}


# BOTÓN DE AYUDA
echo "<button class='faq-button'>
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512'>
        <path d='M80 160c0-35.3 28.7-64 64-64h32c35.3 0 64 28.7 64 64v3.6c0 21.8-11.1 42.1-29.4 53.8l-42.2 27.1c-25.2 16.2-40.4 44.1-40.4 74V320c0 17.7 14.3 32 32 32s32-14.3 32-32v-1.4c0-8.2 4.2-15.8 11-20.2l42.2-27.1c36.6-23.6 58.8-64.1 58.8-107.7V160c0-70.7-57.3-128-128-128H144C73.3 32 16 89.3 16 160c0 17.7 14.3 32 32 32s32-14.3 32-32zm80 320a40 40 0 1 0 0-80 40 40 0 1 0 0 80z'></path>
    </svg>
    <span class='tooltip'>Contactanos por ayuda al: +543644612371</span>
</button>";

$conn->close();
?>
