<?php ?>

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
<?php
// login.php
ini_set('display_errors', 1); //to publicly show errors on the webpage
error_reporting(E_ALL);

session_start();
require_once('../../config/database.php'); // Include database connection

// Check if the admin is already logged in, if yes, redirect to the dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the query to check if the admin exists in the database
    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = :email AND status = 'active'");
    $stmt->execute(['email' => $email]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    var_dump($admin);  // To check if the admin data is fetched correctly
    var_dump($password);  // To check the entered password
    var_dump($admin['password_hash']);  // To check the hashed password from the DB

    
    //check if admin exists and verify password
    /*if ($admin && password_verify($password, $admin['password_hash'])) {
        // If credentials are correct, store the admin's ID in the session
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid login credentials buddy. keep trying.";
    } */
    
    if ($admin) {
    // Trim any extra spaces from the stored password hash
    $stored_hash = trim($admin['password_hash']);
    
    // Verify the entered password against the stored password hash
    if (password_verify($password, $stored_hash)) {
        // Password is correct, log the admin in
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // Password is incorrect
        $error = "Invalid login credentials.";
    }
} else {
    // Admin not found
    $error = "Invalid login credentials. admin not found";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Admin Login</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

</body>
</html>

