<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$success = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $template = $_POST['template'];
    $world_name = $_POST['world_name'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id FROM worlds WHERE user_id = :user_id AND name = :world_name");
    $stmt->execute([
        ':user_id' => $user_id,
        ':world_name' => $world_name
    ]);

    if ($stmt->rowCount() > 0) {
        $error = "Ya existe un mundo con ese nombre. Por favor, elige otro.";
    } else {
        // Enviar datos a la API
        $data = array(
            'template' => $template,
            'user_id' => $user_id,
            'world_name' => $world_name
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents('http://127.0.0.1:5000/start', false, $context);

        if ($result === FALSE) {
            $error = "Error al contactar con la API";
        } else {
            $response = json_decode($result, true);
            if ($response['status'] === 'ok') {
                $success = "¡Mundo creado correctamente!";
            } else {
                $error = "Error: " . htmlspecialchars($response['message']);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="/img/dashDaemon.png" />
    <title>Crear Mundo - Daemon</title>
</head>
<body>
    <div class="dashboard">
        <h1>Crear Nuevo Mundo</h1>

        <?php if (isset($success)): ?>
            <div class="alert success">
                <?php echo $success; ?> <a href="dashboard.php">Volver al panel</a>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="world_name">Nombre del Mundo:</label>
            <input type="text" name="world_name" placeholder="Ej: Mundo VoxeLibre" required>
            <select name="template" required>
                <option value="minetest_game" selected>Minetest Game</option>
                <option value="mineclone2">VoxeLibre (formerly Mineclone2)</option>
                <option value="mineclonia">Mineclonia</option>
                <option value="backroomtest">Backroom Test</option>
                <option value="nodecore">NodeCore</option>
                <option value="capturetheflag">Capture the flag</option>
                <option value="shadow_forest">Shadow Forest</option>
            </select>
	    <button type="submit" class="btn" id="createBtn">
    	        <span id="btnText">Crear</span>
	        <span id="btnLoader" style="display:none;">
		    Construyendo
	            <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
	        </span>
	    </button>

        </form>
	<div class="footer-links">
	    <p><a href="dashboard.php">Cancelar</a></p>
	</div>
    </div>
    <script>
	document.querySelector("form").addEventListener("submit", function () {
	    const button = document.getElementById("createBtn");
            const btnText = document.getElementById("btnText");
            const btnLoader = document.getElementById("btnLoader");

            btnText.style.display = "none";
            btnLoader.style.display = "inline-block";
            button.disabled = true;
        });
    </script>

</body>
</html>
