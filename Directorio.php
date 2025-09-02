<?php
// Puedes agregar cualquier configuración PHP si la necesitas aquí.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página en Desarrollo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            text-align: center;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            font-size: 2.5em;
            color: #ff6347;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.2em;
            color: #555;
        }

        .icon {
            font-size: 4em;
            color: #ff6347;
            margin-bottom: 20px;
        }

        .animate {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .btn-volver {
            display: inline-block;
            background-color: #0056b3;
            color: white;
            font-size: 1.2em;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .btn-volver:hover {
            transform: translateY(-2px);
            transition: all 0.3s;
        }

        footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="icon animate">⚒️</div>
    <h1>Página en Desarrollo</h1>
    <p>Estamos trabajando en algo increíble. ¡Vuelve pronto!</p>
    
    <!-- Botón Volver -->
    <a href="index_MDA.php" class="btn-volver">Volver</a>
    
    <footer>&copy; <?php echo date("Y"); ?> Sunarp :'v - Todos los derechos reservados</footer>
</div>

</body>
</html>
