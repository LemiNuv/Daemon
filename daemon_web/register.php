<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Daemon</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="/img/logoDaemonHead2.png" />
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <a href="index.php">
                <img src="/img/logoDaemonHead2.png" alt="Logo Daemon">
            </a>
	</div>
        <h2>Registro</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn">Registrarse</button>
            <div class="footer-links">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
	    <div>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            try {
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $password]);
                echo "<p class='alert success'>🡐 ¡Usuario registrado! Inicia sesión.</p>";
            } catch (PDOException $e) {
                echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>
