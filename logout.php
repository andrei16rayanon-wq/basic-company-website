<?php
session_start();
require_once 'db_connect.php';

$hourly_rate = 50;
$target_id = null;

// 1. Identify which attendance row to update
if (isset($_SESSION['attendance_id'])) {
    $target_id = $_SESSION['attendance_id'];
} else if (isset($_SESSION['employee_id'])) {
    // Fallback: Find the latest 'Online' session for this user
    $stmtFind = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND status = 'Online' ORDER BY log_in_time DESC LIMIT 1");
    $stmtFind->execute([$_SESSION['employee_id']]);
    $row = $stmtFind->fetch();
    $target_id = $row['id'] ?? null;
}

if ($target_id) {
    try {
        // Calculate pay: (Seconds Logged In / 3600) * 50
        $sql = "UPDATE attendance 
                SET log_out_time = NOW(), 
                    status = 'Offline',
                    total_earned = (TIMESTAMPDIFF(SECOND, log_in_time, NOW()) / 3600) * ? 
                WHERE id = ?";
            
        $stmtOut = $pdo->prepare($sql);
        $stmtOut->execute([$hourly_rate, $target_id]);
    } catch (PDOException $e) {
        // Fail silently to avoid white screen
    }
}

// 2. Clear Session and Redirect
$_SESSION = [];
session_destroy();

header("Location: /registerform_paul/index.php?status=logged_out");
exit();