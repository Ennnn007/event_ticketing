<?php
require '../config.php';
require '../auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare('SELECT id, name, password_hash, failed_login_count, locked_until FROM users WHERE email = ? AND is_admin = 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
        $waitMinutes = ceil((strtotime($user['locked_until']) - time()) / 60);
        $error = "Too many failed attempts. Try again in {$waitMinutes} minute(s).";
    } elseif ($user && password_verify($password, $user['password_hash'])) {
        $reset = $conn->prepare('UPDATE users SET failed_login_count = 0, locked_until = NULL WHERE id = ?');
        $reset->bind_param('i', $user['id']);
        $reset->execute();
        $reset->close();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin'] = true;
        header('Location: events.php');
        exit;
    } else {
        if ($user) {
            $newCount = (int)$user['failed_login_count'] + 1;
            if ($newCount >= 5) {
                $upd = $conn->prepare('UPDATE users SET failed_login_count = ?, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?');
            } else {
                $upd = $conn->prepare('UPDATE users SET failed_login_count = ? WHERE id = ?');
            }
            $upd->bind_param('ii', $newCount, $user['id']);
            $upd->execute();
            $upd->close();
        }
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<link rel="stylesheet" href="../style.css?v=<?= @filemtime(__DIR__ . '/../style.css') ?>">
</head>
<body>
<main class="container">
<div class="auth-card">
<h1>Admin Login</h1>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<label>Email <span class="required-mark">*</span> <input type="email" name="email" required></label>
<label>Password <span class="required-mark">*</span> <input type="password" name="password" required></label>
<button type="submit">Login</button>
</form>
</div>
</main>
</body>
</html>
