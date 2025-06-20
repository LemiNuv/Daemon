<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Lista de mods disponibles, se iran agregando conforme los pruebe
$available_mods = [
    'unified_inventory' => 'Unified Inventory',
    'stamina' => 'Stamina',
    'animalia' => 'Animalia',
    'creatura' => 'Creatura',
    'draconis' => 'Draconis',
    'i3' => 'i3',
    'everness' => 'Everness',
    'bonemeal' => 'Bonemeal',
    'worldedit' => 'WorldEdit',
    'handle_schematics' => 'Handle Schematics',
    'edit_skin' => 'Edit Skin',
    'mg_villages' => 'Villages',
    'nether' => 'Nether',
    'stairs' => 'Stairs',
    '3d_armor' => '3D Armor',
    'awards' => 'Awards',
    'skinsdb' => 'SkinsDB',
    'hudbars' => 'Hud Bars'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $world_name = $_POST['world_name'];
    $user_id = $_SESSION['user_id'];
    $selected_mods = $_POST['mods'] ?? [];

    $stmt = $conn->prepare("SELECT id FROM worlds WHERE user_id = :user_id AND name = :world_name");
    $stmt->execute([
        ':user_id' => $user_id,
        ':world_name' => $world_name
    ]);

    if ($stmt->rowCount() > 0) {
        $error = "Ya existe un mundo con ese nombre. Por favor, elige otro.";
    } elseif (empty($selected_mods)) {
        $error = "Debes seleccionar al menos un mod.";
    } else {
        // Preparo datos para la API
        $data = array(
            'template' => 'minetest_game',
            'user_id' => $user_id,
            'world_name' => $world_name,
            'mods' => $selected_mods
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents('http://127.0.0.1:5000/add_mods', false, $context);

        if ($result === FALSE) {
            $error = "Error al contactar con la API";
        } else {
            $response = json_decode($result, true);
            if ($response['status'] === 'ok') {
                $success = "¡Mundo con mods creado correctamente!";
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

    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title>Añadir Mods - Daemon</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="/img/dashDaemon.png" />
</head>
<body>
    <div class="dashboard">
        <h1>Añadir Mods al Mundo</h1>

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
            <input type="text" name="world_name" placeholder="Ej: Mundo con Mods" required>

            <div class="form-group">
                <label>Selecciona los Mods:</label>
                <div class="mods-grid">
                    <?php foreach ($available_mods as $mod_id => $mod_name): ?>
                        <div class="mod-item">
                            <input type="checkbox" id="mod_<?php echo $mod_id; ?>" name="mods[]" value="<?php echo $mod_id; ?>">
                            <label for="mod_<?php echo $mod_id; ?>"><?php echo $mod_name; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn" id="createBtn">
                <span id="btnText">Añadir Mods</span>
                <span id="btnLoader" style="display:none;">
                    Añadiendo
                    <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
                </span>
	    </button>
            <a href="dashboard.php" class="cancel-link">Cancelar y volver</a>
        </form>
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
