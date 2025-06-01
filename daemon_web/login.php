<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Daemon</title>
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
        <h2>Iniciar Sesión</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn">Entrar</button>
    	    <div class="footer-links">
		<p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
	    <div>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];

	    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
	    $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
		exit();
            } else {
                echo "<p class='alert error'>Usuario o contraseña incorrectos</p>";
            }
        }
        ?>
    </div>
</body>
</html>
