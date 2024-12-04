<?php ?>

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

<<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once('../../config/database.php');

if (isset($_GET['id'])) {
    $member_id = $_GET['id'];

    // Fetch member data
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get updated data
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $member_number = $_POST['member_number'];
        $status = $_POST['status'];
        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Update member info
        $stmt = $pdo->prepare("UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, member_number = ?, status = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $email, $phone, $member_number, $status, $password_hash, $member_id]);

        echo "Member updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Edit Member</h1>

        <form method="POST">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo $member['first_name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo $member['last_name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $member['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo $member['phone']; ?>">
            </div>
            <div class="form-group">
                <label for="member_number">Member Number</label>
                <input type="text" name="member_number" class="form-control" value="<?php echo $member['member_number']; ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?php if ($member['status'] == 'active') echo 'selected'; ?>>Active</option>
                    <option value="inactive" <?php if ($member['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Update Member</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
