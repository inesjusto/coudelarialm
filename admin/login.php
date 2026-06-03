<?php
session_start();

$erro = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-body">
    <div class="login-page">
        <div class="login-box">
            <a href="../public/index.html" class="login-logo-link">
                <img src="assets/img/logo.png" alt="Logo da Coudelaria" class="login-logo">

                <div class="login-brand">
                    <h1>Coudelaria</h1>
                    <h2>Lima Monteiro</h2>
                </div>
            </a>

            <p class="login-subtitulo">Acesso reservado à administração</p>

            <?php if (!empty($erro)): ?>
                <p class="login-erro"><?php echo htmlspecialchars($erro); ?></p>
            <?php endif; ?>

            <form action="../backend/login-admin.php" method="POST" class="login-form">
                <div class="campo">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="campo">
                    <label for="password">Palavra-passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="login-btn">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>