<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['world'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$world_name = $_GET['world'];

$stmt = $conn->prepare("SELECT * FROM worlds WHERE user_id = ? AND name = ?");
$stmt->execute([$user_id, $world_name]);
$world = $stmt->fetch();

if (!$world) {
    header("Location: dashboard.php");
    exit();
}

// Esto para evitar que se entre a la página del mundo que ya se había eliminado
if ($world['status'] === 'deleted') {
    header("Location: dashboard.php");
    exit();
}

function call_api($action, $user_id, $world_name) {
    $data = [
        'user_id' => $user_id,
        'world_name' => $world_name
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    return file_get_contents("http://127.0.0.1:5000/$action", false, $context);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $response = call_api($action, $user_id, $world_name);
    $result = json_decode($response, true);

    if ($result['status'] === 'ok') {
	if ($action === 'delete') {
            header("Location: dashboard.php");
            exit();
        }
        $success = "Acción realizada: " . $result['message'];
    } else {
        $error = "Error: " . $result['message'];
    }
}

$status_response = call_api('status', $user_id, $world_name);
error_log("Status response: " . $status_response);
$status_data = json_decode($status_response, true);
$current_status = trim($status_data['container_status'] ?? 'unknown');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($world['name']) ?> - Daemon</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="/img/dashDaemon.png" />
    <script>
    function confirmAction(action) {
        const messages = {
            'stop': '¿Detener este mundo?',
            'delete': '¿Eliminar permanentemente este mundo?'
        };
        return confirm(messages[action] || '¿Continuar con esta acción?');
    }
    </script>
</head>
<body>
    <div class="world-detail">
        <h1>Mundo: <?= htmlspecialchars($world['name']) ?></h1>

        <?php if (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <div class="world-info">
            <p><strong>Plantilla:</strong> <?= htmlspecialchars($world['template']) ?></p>
            <p><strong>Puerto:</strong> <?= htmlspecialchars($world['port']) ?></p>
            <p><strong>Estado:</strong> <span class="status-<?= strtolower($current_status) ?>">
                <?= ucfirst($current_status) ?>
            </span></p>
        </div>

        <div class="actions">
            <?php if ($current_status === 'active'): ?>
                <form method="POST" onsubmit="return confirmAction('stop')">
                    <input type="hidden" name="action" value="stop">
                    <button type="submit" class="btn stop">Detener Mundo</button>
                </form>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="init">
                    <button type="submit" class="btn start">Iniciar Mundo</button>
                </form>
            <?php endif; ?>

            <form method="POST" onsubmit="return confirmAction('delete')">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn delete">Eliminar Mundo</button>
            </form>

            <a href="dashboard.php" class="btn back">Volver</a>
        </div>
    </div>
</body>
</html>
