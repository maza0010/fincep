<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once('../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    // Prepare and execute the update query
    $stmt = $pdo->prepare("UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, status = ? WHERE id = ?");
    $stmt->execute([$first_name, $last_name, $email, $phone, $status, $id]);

    // Redirect back to the member list page
    header("Location: list_members.php?success=true");
    exit();
}
?>
