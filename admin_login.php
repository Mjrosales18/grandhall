<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Default Fallback Authentication: Username: admin, Password: adminpassword
    if ($user == '@_mjadmin' && $pass == 'mjrosales') {
        $_SESSION['admin'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials!";
    }
}
include('header.php');
?>

<div class="card" style="max-width: 400px; margin: 0 auto;">
    <h2>🔒 Admin Login</h2>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form action="admin_login.php" method="POST">
        <label>Username:</label>
        <input type="text" name="username" required style="width:100%; padding:8px; margin:8px 0;">
        <label>Password:</label>
        <input type="password" name="password" required style="width:100%; padding:8px; margin:8px 0;">
        <button type="submit" class="btn" style="width:100%;">Login</button>
    </form>
</div>