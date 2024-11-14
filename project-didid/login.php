<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");  
        exit();
    } else {
        $error_message = "Invalid login credentials!";
    }
}
?>

<head>
    <link rel="stylesheet" href="css/login.css">
</head>


<form method="POST">
    <h2>Login</h2>
    <?php if (isset($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" id="password" placeholder="Password" required>
    
    <div class="show-password">
        <input type="checkbox" id="showPassword"> <label for="showPassword">Show Password</label>
    </div>

    <button type="submit">Login</button>
    <p>Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>
</form>

<script>
    // Toggle password visibility
    document.getElementById('showPassword').addEventListener('change', function() {
        var passwordField = document.getElementById('password');
        if (this.checked) {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    });
</script>




