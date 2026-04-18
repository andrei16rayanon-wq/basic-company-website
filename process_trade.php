<?php
// 1. DATABASE CONNECTION & SESSION START
require_once 'db_connect.php';
session_start();

/**
 * SECURITY CHECK
 * Prevents logged-out users or direct URL access from executing trades.
 */
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit();
}

/**
 * TRANSACTION PROCESSING
 * Only runs if the request comes from the BUY/SELL modal via POST.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capture data from the modal form
    $emp_id = $_SESSION['employee_id'];
    $asset = $_POST['asset'] ?? 'UNKNOWN';
    $price = $_POST['price'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $type   = $_POST['type'] ?? 'BUY'; // Value will be 'BUY' or 'SELL' from the button clicked

    try {
        // 2. PREPARE THE SQL STATEMENT
        // This inserts the trade record into your 'transactions' table
        $stmt = $pdo->prepare("INSERT INTO transactions (employee_id, asset_name, transaction_type, amount, price_at_time) 
                               VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $emp_id, 
            $asset, 
            $type, 
            $amount, 
            $price
        ]);

        /**
         * 3. THE REDIRECT (THE "BACK" LOGIC)
         * This sends the user back to home.php immediately.
         * We add '?trade=executed' to the URL so home.php knows to show a success message.
         */
        header("Location: home.php?trade=executed");
        exit();

    } catch (PDOException $e) {
        // If the table 'transactions' is missing or database is down
        die("<div style='background:#211315; color:#f6465d; padding:20px; font-family:sans-serif;'>
                <strong>MARKET EXECUTION ERROR:</strong><br>" . htmlspecialchars($e->getMessage()) . "
             </div>");
    }

} else {
    /**
     * CATCH-ALL REDIRECT
     * If someone tries to visit process_trade.php by typing it in the browser,
     * it just sends them back to the market dashboard.
     */
    header("Location: home.php");
    exit();
}