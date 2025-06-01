<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title>Creación de Mundos - Daemon</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="/img/dashDaemon.png" />
</head>
<body>
    <div>
        <div class="page-header">
            <h1>Crear Nuevo Mundo</h1>
            <p>Selecciona el tipo de mundo que deseas crear. Puedes empezar con una plantilla prediseñada o construir tu propio mundo añadiendo mods específicos.</p>
        </div>

        <div class="creation-options">
            <a href="create_world.php" class="creation-card">
                <div class="card-image template-image"></div>
                <div class="card-content">
                    <h3 class="card-title">Plantilla Prediseñada</h3>
                    <p class="card-description">Crea un mundo basado en una plantilla preconfigurada. Ideal para empezar rápidamente con temáticas específicas como aventuras, survival o creativo.</p>
                    <div class="card-button">Seleccionar</div>
                </div>
            </a>

            <a href="create_modded_handler.php" class="creation-card">
                <div class="card-image mods-image"></div>
                <div class="card-content">
                    <h3 class="card-title">Mundo Personalizado con Mods</h3>
                    <p class="card-description">Construye tu mundo seleccionando mods específicos. Total libertad para personalizar la experiencia de juego con tus complementos favoritos.</p>
                    <div class="card-button">Seleccionar</div>
                </div>
            </a>
        </div>
    </div>
</body>
</html>
