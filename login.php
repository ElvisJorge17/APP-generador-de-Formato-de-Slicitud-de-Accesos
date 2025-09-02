<?php
session_start();

// Verificar si ya hay una sesión activa
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index_MDA.php");
    exit;
}

$login_error = null;

// Verificar si se han enviado credenciales
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login_username"]) && isset($_POST["login_password"])) {
    $login_username = $_POST["login_username"];
    $login_password = $_POST["login_password"];
    
    // Intentar conexión a Oracle con las credenciales proporcionadas
    $conn = @oci_connect($login_username, $login_password, "//172.20.100.37:1531/OR0601", "AL32UTF8");
    
    if ($conn) {
        // Conexión exitosa, guardar en la sesión
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $login_username;
        $_SESSION['password'] = $login_password; // No es seguro guardar la contraseña en la sesión en producción
        
        // Cerrar la conexión
        oci_close($conn);
        
        // Redirigir a la página principal
        header("Location: index_MDA.php");
        exit;
    } else {
        $login_error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema para la generación del Formato de Solicitud de Accesoss</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #e0dcdc;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 15px;
            background-color: #1b2a85;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #436bba;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            background-color: #ffeeee;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ffcccc;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="bi bi-shield-lock">Sistema de Formato de Solicitud de Accesos</h1>
        </div>

        <!-- Contenedor para logo y formulario -->
        <div style="display: flex; justify-content: center; align-items: center;">

            <!-- Formulario de Login -->
            <div class="card">

                <!-- Logo -->
                <div style="">
                    <img src="IMG/logo.png" alt="Logo" style="max-width: 300px;">
                </div>
                <h2>Iniciar Sesión</h2>
                
                <?php if (isset($login_error)): ?>
                    <div class="error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="form-group">
                        <label for="login_username">Usuario Base de Datos:</label>
                        <input type="text" id="login_username" name="login_username" required>
                    </div>
                    <div class="form-group">
                        <label for="login_password">Contraseña:</label>
                        <input type="password" id="login_password" name="login_password" required>
                    </div>
                    <button type="submit">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>