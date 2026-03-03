<?php

require_once __DIR__ . '/../logic/login.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - Bubble</title>
    <link rel="stylesheet" href="../assets/styling/style.css">
    <link rel="stylesheet" href="../assets/styling/auth.css">
    <link rel="stylesheet" href="../assets/styling/home.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body>
    <div id="canvas-container"></div>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Bubble</h1>
                <p>Welkom terug!</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="error-box">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="je@email.com" required>
                    <?php if (!empty($errors['email'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <div class="password-input-group">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" id="password-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="auth-button">Inloggen</button>
            </form>

            <div class="auth-footer">
                <p>Nog geen account? <a href="register_view.php">Registreer hier</a><br>
                    Of <a href="../index.php">visit Bubble!</a></p>
            </div>
        </div>
    </div>

    <script type="module" src="../javascript/auth-ui.js"></script>
</body>

</html>