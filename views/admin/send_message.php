<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../config/database.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $member_id = $_POST['member_id'];
    $message = $_POST['message'];

    // Insert message into the notifications table
    $stmt = $pdo->prepare("INSERT INTO notifications (member_id, notification_text, status) VALUES (?, ?, 'unread')");
    $stmt->execute([$member_id, $message]);

    // Redirect back to the admin dashboard with a success message
    header('Location: admin_dashboard.php?message=Message Sent Successfully');
    exit;
}
?>
