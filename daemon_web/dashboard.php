<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Mundos - Daemon</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="/img/dashDaemon.png" />
</head>
<body>
    <div class="dashboard">
        <h1>Bienvenido, <?= $username ?>!</h1>
        <div class="section-header">
            <h2>Tus Mundos</h2>
            <a href="where.php" class='btn create'>+ Crear un mundo</a>
        </div>

        <?php
        $stmt = $conn->prepare("SELECT * FROM worlds WHERE user_id = ? AND status != 'deleted'");
        $stmt->execute([$user_id]);
        $worlds = $stmt->fetchAll();

        if (empty($worlds)) {
            echo "<p>Aún no tienes mundos creados.</p>";
        } else {
            echo "<ul class='worlds-list'>";
            foreach ($worlds as $world) {
                echo "<li class='world-item' data-status='{$world['status']}'>
                    <a href='world_detail.php?world=" . urlencode($world['name']) . "' class='world-link'>
                        <strong class='nameW'>{$world['name']}</strong><br>
                        Template: {$world['template']} |
                        Puerto: {$world['port']} |
                        Estado: <span class='status-{$world['status']}'>" . ucfirst($world['status']) . "</span>
                    </a>
                </li>";
            }
            echo "</ul>";
        }
        ?>
        <div class="footer-links">
            <a href="logout.php">Cerrar Sesión</a>
            <a href="index.php">Regresar a la página principal</a>
        </div>
    </div>
</body>
</html>
