<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Tracker - Home</title>
    <?php require_once __DIR__ . '/../../config/config.php'; ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }
        .login-form {
            display: flex;
            flex-direction: column;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .button {
            display: block;
            width: 100%;
            background-color: #3498db;
            color: #fff;
            padding: 0.75rem;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .success {
            color: #2ecc71;
            text-align: center;
            margin-top: 1rem;
            padding: 0.5rem;
            background-color: #d5f5e3;
            border-radius: 4px;
        }
        .error {
            background-color: #fbe9e7;
            padding: 0.5rem;
            border-radius: 4px;
            color: #e74c3c;
            text-align: center;
            margin-top: 1rem;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }
    </style>
    <script>
        // Clear URL parameters without refreshing the page
        if (window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</head>
<body>
<div class="container">
        <h1>Welcome to Bug Tracker</h1>
        <?php 
        if (isset($_GET['message'])) {
            if ($_GET['message'] === 'loggedout') {
                echo '<p class="success">Logged out successfully!</p>';
            } elseif ($_GET['message'] === 'error' && isset($_GET['error'])) {
                echo '<p class="error">' . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
        }
        ?>
        <form action="<?php echo url('login'); ?>" method="post" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" id="password-toggle" class="password-toggle">👓</button>
                </div>
            </div>
            <button type="submit" class="button">Login</button>
        </form>
    </div>

    <script>
        document.getElementById('password-toggle').addEventListener('click', function() {
            var passwordInput = document.getElementById('password');
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👓' : '🕶️';
        });
    </script>
</body>
</html>