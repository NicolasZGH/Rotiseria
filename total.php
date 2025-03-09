<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

#Verificar si se ha enviado una solicitud para eliminar un pedido
if (isset($_POST['eliminar_total'])) {
    $idTotal = $_POST['idTotal'];

    #Eliminar el pedido de la tabla Total
    $sql_delete_total = "DELETE FROM Total WHERE idTotal = ?";
    $stmt_delete = $conn->prepare($sql_delete_total);
    $stmt_delete->bind_param("i", $idTotal);

    if ($stmt_delete->execute()) {
        echo "Pedido eliminado correctamente.";
    } else {
        echo "Error al eliminar el pedido: " . $conn->error;
    }

    #Recargar la página después de la eliminación
    header("Location: total.php");
    exit();
}

#NAVBAR
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Total de Pedidos</title>";
echo "<link rel='stylesheet' href='style/inicio.css'>";
echo "<link rel='stylesheet' href='style/navbar.css'>";
echo "</head>";
echo "<body>";
echo "<div class='navbar'>";
echo "<a href='inicio.php?section=inicio'>Inicio</a>";
echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
echo "<a href='total.php?section=total' class='active'>Total de Pedidos</a>";
echo "<a href='clientes.php?section=clientes'>Clientes</a>";
echo "<a href='productos.php?section=productos'>Productos</a>";
echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";

echo "<h1>Total de Pedidos</h1>";

#formulario de búsqueda de pedidos por cliente
echo "<div class='busqueda-cliente' style='background-color: #f2f2f2; padding: 1rem; border-radius: 0.5rem;'>";
echo "<form method='GET' action='total.php'>";
echo "<label for='cliente' style='margin-right: 0.5rem;'>Buscar</label><br>";
echo "<label for='cliente' style='margin-right: 0.5rem;'>pedidos por cliente:</label>";
echo "<input type='text' id='cliente' name='cliente' placeholder='Nombre del cliente' style='padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem; width: 200px;'>";
echo "<button type='submit' style='padding: 0.5rem 1rem; background-color: #e64e08; color: #fff; border: none; border-radius: 0.25rem; cursor: pointer; margin-left: 0.5rem;'>Buscar</button>";
echo "<button href='total.php' style='padding: 0.5rem 1rem; background-color: #333; color: #fff; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.9rem; margin-left: 0.5rem;'>Volver</button>";
echo "</form>";
echo "</div>";

#todos los totales necesarios
#Total general
$sql_suma_total = "SELECT SUM(
    CASE 
        WHEN t.mitades = 1 THEN (t.Cantidad * (prod.Precio / 2))
        ELSE (t.Cantidad * prod.Precio)
    END
    ) AS SumaTotal
    FROM Total t
    INNER JOIN Productos prod ON t.idProductos = prod.idProductos";
$result_suma_total = $conn->query($sql_suma_total);

#Total por mes (mes actual)
$sql_mes_actual = "SELECT SUM(
    CASE 
        WHEN t.mitades = 1 THEN (t.Cantidad * (prod.Precio / 2))
        ELSE (t.Cantidad * prod.Precio)
    END
    ) AS SumaMes
    FROM Total t
    INNER JOIN Productos prod ON t.idProductos = prod.idProductos
    WHERE MONTH(t.FechaPedido) = MONTH(CURRENT_DATE()) 
    AND YEAR(t.FechaPedido) = YEAR(CURRENT_DATE())";
$result_mes = $conn->query($sql_mes_actual);

#Total por semana (semana actual)
$sql_semana_actual = "SELECT SUM(
    CASE 
        WHEN t.mitades = 1 THEN (t.Cantidad * (prod.Precio / 2))
        ELSE (t.Cantidad * prod.Precio)
    END
    ) AS SumaSemana
    FROM Total t
    INNER JOIN Productos prod ON t.idProductos = prod.idProductos
    WHERE YEARWEEK(t.FechaPedido, 1) = YEARWEEK(CURRENT_DATE(), 1)";
$result_semana = $conn->query($sql_semana_actual);

#Total por día (día actual)
$sql_dia_actual = "SELECT SUM(
    CASE 
        WHEN t.mitades = 1 THEN (t.Cantidad * (prod.Precio / 2))
        ELSE (t.Cantidad * prod.Precio)
    END
    ) AS SumaDia
    FROM Total t
    INNER JOIN Productos prod ON t.idProductos = prod.idProductos
    WHERE DATE(t.FechaPedido) = CURRENT_DATE()";
$result_dia = $conn->query($sql_dia_actual);

#Preparar los valores
$total_general = 0;
$total_mes = 0;
$total_semana = 0;
$total_dia = 0;

if ($result_suma_total) {
    $row_suma_total = $result_suma_total->fetch_assoc();
    $total_general = $row_suma_total['SumaTotal'] ?: 0;
    $result_suma_total->free();
}

if ($result_mes) {
    $row_mes = $result_mes->fetch_assoc();
    $total_mes = $row_mes['SumaMes'] ?: 0;
    $result_mes->free();
}

if ($result_semana) {
    $row_semana = $result_semana->fetch_assoc();
    $total_semana = $row_semana['SumaSemana'] ?: 0;
    $result_semana->free();
}

if ($result_dia) {
    $row_dia = $result_dia->fetch_assoc();
    $total_dia = $row_dia['SumaDia'] ?: 0;
    $result_dia->free();
}

#Contenedor para las cajas de estadísticas 
echo "<div style='display: flex; flex-wrap: unset; max-width: 500px; gap: 9px; margin: 20px 0;'>";

#Caja para el total general
echo "<div style='background-color: #f2f2f2; padding: 10px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: calc(50% - 5px);'>";
echo "<h4 style='margin: 0 0 5px 0; color: #333; font-size: 0.9em;'>Total General</h4>";
echo "<p style='font-size: 1.2em; font-weight: bold; margin: 0; color: #e64e08;'>$" . number_format($total_general, 2) . "</p>";
echo "</div>";

#Caja para el total del mes
echo "<div style='background-color: #f2f2f2; padding: 10px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: calc(50% - 5px);'>";
echo "<h4 style='margin: 0 0 5px 0; color: #333; font-size: 0.9em;'>Total del Mes</h4>";
echo "<p style='font-size: 1.2em; font-weight: bold; margin: 0; color: #e64e08;'>$" . number_format($total_mes, 2) . "</p>";
echo "</div>";

#Caja para el total de la semana
echo "<div style='background-color: #f2f2f2; padding: 10px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: calc(50% - 5px);'>";
echo "<h4 style='margin: 0 0 5px 0; color: #333; font-size: 0.9em;'>Total de Semana</h4>";
echo "<p style='font-size: 1.2em; font-weight: bold; margin: 0; color: #e64e08;'>$" . number_format($total_semana, 2) . "</p>";
echo "</div>";

#Caja para el total del día
echo "<div style='background-color: #f2f2f2; padding: 10px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: calc(50% - 5px);'>";
echo "<h4 style='margin: 0 0 5px 0; color: #333; font-size: 0.9em;'>Total del Día</h4>";
echo "<p style='font-size: 1.2em; font-weight: bold; margin: 0; color: #e64e08;'>$" . number_format($total_dia, 2) . "</p>";
echo "</div>";

echo "</div>"; 

#Calcular y mostrar el total general de todos los pedidos
$sql_suma_total = "SELECT SUM(
    CASE 
        WHEN t.mitades = 1 THEN (t.Cantidad * (prod.Precio / 2))
        ELSE (t.Cantidad * prod.Precio)
    END
    ) AS SumaTotal
    FROM Total t
    INNER JOIN Productos prod ON t.idProductos = prod.idProductos";
$result_suma_total = $conn->query($sql_suma_total);

if ($result_suma_total) {
$row_suma_total = $result_suma_total->fetch_assoc();
$total_general = $row_suma_total['SumaTotal'];
$result_suma_total->free();
} else {
echo "Error al calcular el total general: " . $conn->error . "<br>";
}

# Construir la consulta SQL base
$sql_base = "SELECT 
    DATE(t.FechaPedido) AS Fecha, 
    t.idTotal, 
    t.FechaPedido, 
    c.Nombre AS Cliente, 
    c.DNI AS DNICliente, 
    prod.Nombre AS Producto, 
    t.Cantidad, 
    t.mitades,
    CASE 
        WHEN t.mitades = 1 THEN prod.Precio / 2
        ELSE prod.Precio 
    END AS PrecioUnitario,
    CASE 
        WHEN t.mitades = 1 THEN (t.Cantidad * (prod.Precio / 2))
        ELSE (t.Cantidad * prod.Precio)
    END AS PrecioTotal
FROM Total t 
INNER JOIN Clientes c ON t.idClientes = c.idClientes
INNER JOIN Productos prod ON t.idProductos = prod.idProductos";

#verificar si se ingresó un nombre de cliente en el formulario de búsqueda
if (isset($_GET['cliente']) && !empty($_GET['cliente'])) {
    $cliente = "%" . $_GET['cliente'] . "%";
    $sql_total = $sql_base . " WHERE c.Nombre LIKE ? ORDER BY t.FechaPedido DESC";
    $stmt = $conn->prepare($sql_total);
    $stmt->bind_param("s", $cliente);
    $stmt->execute();
    $result_total = $stmt->get_result();
} else {
    $sql_total = $sql_base . " ORDER BY t.FechaPedido DESC";
    $result_total = $conn->query($sql_total);
}

#continuar mostrando la tabla de pedidos
if ($result_total) {
    $current_date = null;
    $daily_total = 0;
    if ($result_total->num_rows > 0) {
        while ($row = $result_total->fetch_assoc()) {
            if ($current_date !== date("d/m/Y", strtotime($row["Fecha"]))) {
                if ($current_date !== null) {
                    echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total del Día:</td><td>$" . number_format($daily_total, 2) . "</td></tr>";
                    echo "</table><br>";
                }
                $current_date = date("d/m/Y", strtotime($row["Fecha"]));
                echo "<h3>Pedidos del " . $current_date . "</h3>";
                echo "<table style='width: 60%; font-size: 1.0em;'>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>DNI del Cliente</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Precio Total</th>
                        <th>Acciones</th>
                    </tr>";
                $daily_total = 0;
            }
            
            $daily_total += $row["PrecioTotal"];
            
            $producto_nombre = $row["Producto"];
            if ($row["mitades"] == 1) {
                $producto_nombre .= " (Mitad)";
            }
            
            echo "<tr>
                    <td>" . date("d/m/Y", strtotime($row["FechaPedido"])) . "</td>
                    <td>" . $row["Cliente"] . "</td>
                    <td>" . $row["DNICliente"] . "</td>
                    <td>" . $producto_nombre . "</td>
                    <td>" . $row["Cantidad"] . "</td>
                    <td>$" . number_format($row["PrecioUnitario"], 2) . "</td>
                    <td>$" . number_format($row["PrecioTotal"], 2) . "</td>
                    <td>
                        <form method='POST' action='total.php' style='display:inline-block;'>
                            <input type='hidden' name='idTotal' value='" . $row["idTotal"] . "'>
                            <button type='submit' name='eliminar_total'>Eliminar</button>
                        </form>
                    </td>
                </tr>";
        }
        echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total del Día:</td><td>$" . number_format($daily_total, 2) . "</td></tr>";
        echo "</table><br>";
    } else {
        echo "";
    }
    $result_total->free();
} else {
    echo "Error en la consulta de total de pedidos: " . $conn->error . "<br>";
}

echo "<div style='height: 50px;'></div>";
echo "</body>";
echo "</html>";
?>
