<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['employee_id'])) { exit("Unauthorized Access"); }
$userId = $_SESSION['employee_id'];

try {
    $user = $pdo->query("SELECT * FROM employee WHERE Id = $userId")->fetch();
    $logs = $pdo->query("SELECT * FROM attendance WHERE employee_id = $userId ORDER BY log_in_time ASC")->fetchAll();
    $grandTotal = 0;
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>DTR_<?= $user['Username'] ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #555; padding: 20px; }
        #pdf-area { background: white; width: 210mm; margin: auto; padding: 20mm; box-sizing: border-box; color: #000; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 10px; text-align: center; font-size: 12px; }
        th { background: #eee; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig { border-top: 1px solid #000; width: 200px; text-align: center; padding-top: 5px; font-size: 12px; font-weight: bold; }
        .controls { text-align: center; margin-bottom: 20px; }
        .btn { padding: 10px 20px; background: #0ecb81; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="controls">
        <button class="btn" onclick="downloadPDF()">DOWNLOAD DTR (PDF)</button>
        <button class="btn" style="background:#333; color:white;" onclick="window.close()">CLOSE</button>
    </div>

    <div id="pdf-area">
        <div class="header">
            <h1 style="margin:0;">DAILY TIME RECORD</h1>
            <p style="margin:5px 0;">Official Operating Registry | Terminal.io</p>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:13px;">
            <div>
                <strong>NAME:</strong> <?= $user['FirstName'].' '.$user['LastName'] ?><br>
                <strong>POSITION:</strong> <?= strtoupper($user['EmploymentStatus']) ?>
            </div>
            <div style="text-align:right;">
                <strong>ID:</strong> #<?= $user['Id'] ?><br>
                <strong>DATE:</strong> <?= date('Y-m-d') ?>
            </div>
        </div>
        <table>
            <thead><tr><th>DATE</th><th>LOG IN</th><th>LOG OUT</th><th>EARNED</th></tr></thead>
            <tbody>
                <?php foreach($logs as $l): ?>
                <tr>
                    <td><?= date('Y-m-d', strtotime($l['log_in_time'])) ?></td>
                    <td><?= date('h:i A', strtotime($l['log_in_time'])) ?></td>
                    <td><?= $l['log_out_time'] ? date('h:i A', strtotime($l['log_out_time'])) : '--' ?></td>
                    <td>$<?= number_format($l['total_earned'], 2) ?></td>
                </tr>
                <?php $grandTotal += $l['total_earned']; endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#eee;">
                    <td colspan="3" align="right"><strong>GRAND TOTAL REVENUE:</strong></td>
                    <td><strong>$<?= number_format($grandTotal, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <div class="footer">
            <div class="sig">Operator Signature</div>
            <div class="sig">Administrator</div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('pdf-area');
            html2pdf().from(element).set({
                margin: 5,
                filename: 'DTR_<?= $user['Username'] ?>.pdf',
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            }).save();
        }
    </script>
</body>
</html>