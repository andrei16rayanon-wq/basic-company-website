<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['employee_id'])) { exit("Unauthorized Access"); }
$userId = $_SESSION['employee_id'];

// 1. CAPTURE THE PREFERRED AMOUNT FROM THE URL
// This ensures the slip shows what you typed, not the entire balance.
$displayAmount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

try {
    // FIXED: Using prepared statement for security
    $stmt = $pdo->prepare("SELECT * FROM employee WHERE Id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) { exit("User not found"); }

    // If no amount was passed in URL, fallback to the total balance logic 
    // (though usually, $displayAmount will now be set by your modal)
    if ($displayAmount <= 0) {
        $stmtSum = $pdo->prepare("SELECT (SELECT IFNULL(SUM(total_earned), 0) FROM attendance WHERE employee_id = ?) - (SELECT IFNULL(SUM(amount), 0) FROM withdrawals WHERE employee_id = ?) as current_balance");
        $stmtSum->execute([$userId, $userId]);
        $sumData = $stmtSum->fetch();
        $displayAmount = $sumData['current_balance'] ?? 0;
    }
    
    $payslipNo = "PAY-" . strtoupper(substr(md5(time()), 0, 8));
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payslip_<?= htmlspecialchars($user['Username']) ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #333; padding: 40px; }
        #payslip-card { 
            background: white; width: 180mm; margin: auto; padding: 15mm; 
            border: 1px solid #eee; box-shadow: 0 0 20px rgba(0,0,0,0.5); position: relative;
        }
        .watermark { 
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); 
            font-size: 80px; color: rgba(0,0,0,0.05); font-weight: bold; pointer-events: none; text-transform: uppercase;
        }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0ecb81; padding-bottom: 10px; margin-bottom: 20px; }
        .comp-info h2 { margin: 0; color: #0ecb81; }
        .comp-info p { margin: 2px 0; font-size: 12px; color: #666; }
        
        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
        .summary-box h4 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; font-size: 14px; }
        .row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; }

        .net-amount-box { 
            background: #f9f9f9; padding: 20px; text-align: center; border: 1px dashed #0ecb81; margin-top: 20px;
        }
        .btn-container { text-align: center; margin-bottom: 20px; }
        .btn { padding: 10px 25px; background: #0ecb81; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; color: #000; }
    </style>
</head>
<body>

    <div class="btn-container">
        <button class="btn" onclick="downloadPayslip()">DOWNLOAD PAYSLIP (PDF)</button>
        <button class="btn" style="background:#666; color:white;" onclick="window.close()">CLOSE</button>
    </div>

    <div id="payslip-card">
        <div class="watermark">OFFICIAL RECEIPT</div>
        
        <div class="header">
            <div class="comp-info">
                <h2>TERMINAL.IO</h2>
                <p>Digital Asset & Labor Exchange</p>
                <p>Transaction ID: <?= $payslipNo ?></p>
            </div>
            <div style="text-align: right;">
                <h3 style="margin:0;">PAYSLIP</h3>
                <p style="font-size:12px; color:#666;">Date Issued: <?= date('M d, Y') ?></p>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-box">
                <h4>EMPLOYEE DETAILS</h4>
                <div class="row"><span>Name:</span> <strong><?= htmlspecialchars($user['FirstName'].' '.$user['LastName']) ?></strong></div>
                <div class="row"><span>ID:</span> <strong>#<?= $user['Id'] ?></strong></div>
                <div class="row"><span>Username:</span> <strong><?= htmlspecialchars($user['Username']) ?></strong></div>
                <div class="row"><span>Status:</span> <strong><?= htmlspecialchars($user['EmploymentStatus']) ?></strong></div>
            </div>
            <div class="summary-box">
                <h4>PAYMENT SUMMARY</h4>
                <div class="row"><span>Requested Amount:</span> <strong>$<?= number_format($displayAmount, 2) ?></strong></div>
                <div class="row"><span>Deductions:</span> <strong>$0.00</strong></div>
                <div class="row"><span>Tax (0%):</span> <strong>$0.00</strong></div>
            </div>
        </div>

        <div class="net-amount-box">
            <div style="font-size: 12px; color: #666; text-transform: uppercase;">Net Withdrawal Amount</div>
            <div style="font-size: 36px; font-weight: bold; color: #333;">$<?= number_format($displayAmount, 2) ?></div>
        </div>

        <div style="margin-top: 40px; font-size: 11px; color: #888; line-height: 1.5;">
            <strong>Note:</strong> This document serves as an official request for withdrawal of accumulated revenue. 
            By printing this payslip, the operator acknowledges that the listed amount of <strong>$<?= number_format($displayAmount, 2) ?></strong> is due for disbursement 
            according to the terminal's payment schedule.
        </div>

        <div style="margin-top: 50px; display: flex; justify-content: space-around;">
            <div style="text-align:center; border-top: 1px solid #000; width: 150px; padding-top: 5px; font-size: 12px;">Operator Signature</div>
            <div style="text-align:center; border-top: 1px solid #000; width: 150px; padding-top: 5px; font-size: 12px;">Finance Department</div>
        </div>
    </div>

    <script>
        function downloadPayslip() {
            const element = document.getElementById('payslip-card');
            html2pdf().from(element).set({
                margin: 10,
                filename: 'Payslip_<?= $user['Username'] ?>_<?= date('Y-m-d') ?>.pdf',
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            }).save();
        }
    </script>
</body>
</html>