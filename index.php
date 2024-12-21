<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

#Encabezados para evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Expires: Sun, 02 Jan 2023 00:00:00 GMT');

session_start();

#Sesión activa redirigir a inicio
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    header("Location: inicio.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        $error = "El nombre de usuario solo debe contener letras.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ?");
        if ($stmt === false) {
            die("Error en prepare: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "El usuario no existe en la base de datos.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ? AND Clave = ?");
            if ($stmt === false) {
                die("Error en prepare: " . $conn->error);
            }
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                session_regenerate_id(true); 
                $_SESSION['username'] = $row['Usuario'];
                $_SESSION['last_activity'] = time();
                $_SESSION['created'] = time();
                header("Location: inicio.php");
                exit();
            } else {
                $error = "Usuario o clave incorrectos.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style/style.css">
    <link rel="icon" href="images/icon.png">
    <title>Buitre Delivery</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <form class="form_main" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <img src="images/logo.png" alt="El Buitre" class="form_image">
        <p class="heading">Iniciar sesión</p>
        <div class="inputContainer">
            <input placeholder="Usuario" id="username" name="username" class="inputField" type="text" required>
        </div>
        
        <div class="inputContainer">
            <input placeholder="Clave" id="password" name="password" class="inputField" type="password" required>
        </div>

        <input type="hidden" name="login" value="1">
        <button id="button" type="submit">Ingresar</button>
        <div class="signupContainer">
            <a href="contra.php">¿Olvidaste tu contraseña?</a>
        </div>
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
</body>
</html>
