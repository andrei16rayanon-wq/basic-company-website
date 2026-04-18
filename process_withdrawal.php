<?php
require_once 'db_connect.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['employee_id'])) {
        echo json_encode(['success' => false, 'message' => 'Session expired.']);
        exit;
    }

    $userId = $_SESSION['employee_id'];
    // This grabs the "Preferred Amount" you typed into the input field
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $submittedUser = trim($_POST['username'] ?? '');

    try {
        // 1. Calculate current balance to see if the PREFERRED amount is available
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT IFNULL(SUM(total_earned), 0) FROM attendance WHERE employee_id = ?) - 
                (SELECT IFNULL(SUM(amount), 0) FROM withdrawals WHERE employee_id = ?) as current_balance,
                Username
            FROM employee WHERE Id = ?
        ");
        
        $stmt->execute([$userId, $userId, $userId]);
        $data = $stmt->fetch();

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Account not found.']);
            exit;
        }

        // 2. Validate Username
        if (strtolower($submittedUser) !== strtolower($data['Username'])) {
            echo json_encode(['success' => false, 'message' => 'Authorization failed: Username mismatch.']);
            exit;
        }

        // 3. Compare Preferred Amount vs Available Balance
        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid amount.']);
            exit;
        }

        if ($amount > $data['current_balance']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Insufficient Funds. You tried to withdraw $' . number_format($amount, 2) . ' but only have $' . number_format($data['current_balance'], 2) . ' available.'
            ]);
            exit;
        }

        // 4. If all checks pass, record the specific preferred amount
        $insert = $pdo->prepare("INSERT INTO withdrawals (employee_id, amount, created_at) VALUES (?, ?, NOW())");
        
        if ($insert->execute([$userId, $amount])) {
            echo json_encode(['success' => true]);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
    }
}