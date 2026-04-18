<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['employee_id'];
$update_status = "";

// --- PROFILE PICTURE UPLOAD LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pix'])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }
    $fileName = time() . '_' . basename($_FILES["profile_pix"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["profile_pix"]["tmp_name"], $targetFilePath)) {
        $update = $pdo->prepare("UPDATE employee SET ProfilePicture = ? WHERE Id = ?");
        $update->execute([$fileName, $userId]);
        $update_status = "AVATAR UPDATED";
    }
}

try {
    // 1. Fetch ALL user details
    $stmt = $pdo->prepare("SELECT * FROM employee WHERE Id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // 2. Calculate Balance (FIXED: Using two placeholders for the two subqueries)
    $stmtBal = $pdo->prepare("
        SELECT 
        (SELECT IFNULL(SUM(total_earned), 0) FROM attendance WHERE employee_id = ?) - 
        (SELECT IFNULL(SUM(amount), 0) FROM withdrawals WHERE employee_id = ?) as balance
    ");
    $stmtBal->execute([$userId, $userId]);
    $balData = $stmtBal->fetch();
    $rawBalance = $balData['balance'] ?? 0;

    // 3. Fetch Attendance History (FIXED: Converted to Prepared Statement)
    $stmtAtt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? ORDER BY log_in_time DESC LIMIT 15");
    $stmtAtt->execute([$userId]);
    $attendanceRecords = $stmtAtt->fetchAll();

    // 4. Fetch Earning History (FIXED: Converted to Prepared Statement)
    $stmtEarn = $pdo->prepare("SELECT log_in_time, total_earned FROM attendance WHERE employee_id = ? AND total_earned > 0 ORDER BY log_in_time DESC");
    $stmtEarn->execute([$userId]);
    $earningRecords = $stmtEarn->fetchAll();

} catch (PDOException $e) { 
    die("Database Error: " . $e->getMessage()); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile | Terminal.io</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #0b0e11; --card: #1e2329; --green: #0ecb81; --red: #f6465d; --border: #2b3139; --text: #eaecef; --gray: #848e9c; }
        body { font-family: 'Roboto', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding-bottom: 50px; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; border-bottom: 1px solid var(--border); }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .identity-card { background: var(--card); border: 1px solid var(--border); padding: 25px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .profile-pic-slot { width: 85px; height: 85px; background: #000; border: 2px solid var(--green); border-radius: 50%; overflow: hidden; cursor: pointer; position: relative; display: flex; align-items: center; justify-content: center; }
        .profile-pic-slot img { width: 100%; height: 100%; object-fit: cover; }
        .overlay { position: absolute; background: rgba(0,0,0,0.6); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; color: var(--green); }
        .profile-pic-slot:hover .overlay { opacity: 1; }
        .bar-wrapper { margin-bottom: 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--card); overflow: hidden; }
        .collapsible { background: transparent; color: var(--text); cursor: pointer; padding: 20px; width: 100%; border: none; text-align: left; font-weight: bold; display: flex; justify-content: space-between; align-items: center; outline: none; }
        .collapsible:hover { background: rgba(255,255,255,0.02); }
        .content-panel { padding: 0 20px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; background: #161a1e; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px 0; }
        .info-item label { display: block; font-size: 10px; color: var(--gray); text-transform: uppercase; margin-bottom: 4px; }
        .info-item span { font-size: 14px; color: var(--text); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; color: var(--gray); font-size: 10px; padding: 15px 10px; border-bottom: 1px solid var(--border); }
        td { padding: 12px 10px; font-size: 13px; border-bottom: 1px solid #2b3139; }
        .btn-green { background: var(--green); color: #000; padding: 12px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-green:hover { opacity: 0.8; }
        .btn-outline { background: transparent; border: 1px solid var(--green); color: var(--green); padding: 8px 15px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; }
        .modal-content { background: var(--card); padding: 30px; border-radius: 8px; border: 1px solid var(--green); width: 90%; max-width: 400px; text-align: center; }
        .modal-content input { width: 100%; padding: 12px; margin: 10px 0; background: var(--bg); border: 1px solid var(--border); color: var(--text); border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>

<header>
    <a href="home.php" style="color:var(--green); text-decoration:none; font-weight:bold;">TERMINAL.IO</a>
    <a href="logout.php" style="color:var(--red); text-decoration:none; font-size:12px; font-weight:bold;">TERMINATE SESSION</a>
</header>

<div class="container">
    <form id="uploadForm" method="POST" enctype="multipart/form-data" style="display:none;">
        <input type="file" name="profile_pix" id="fileInput" onchange="document.getElementById('uploadForm').submit();">
    </form>

    <div class="identity-card">
        <div style="display:flex; align-items:center; gap:20px;">
            <div class="profile-pic-slot" onclick="document.getElementById('fileInput').click();">
                <?php if (!empty($user['ProfilePicture'])): ?>
                    <img src="uploads/<?= htmlspecialchars($user['ProfilePicture']) ?>">
                <?php else: ?>
                    <i class="fa-solid fa-user-astronaut" style="font-size:35px; color:var(--gray)"></i>
                <?php endif; ?>
                <div class="overlay"><i class="fa-solid fa-camera"></i></div>
            </div>
            <div>
                <div style="font-size:26px; font-weight:bold;"><?= htmlspecialchars($user['Username'] ?? 'Operator') ?></div>
                <div style="color:var(--green); font-size:10px; font-weight:bold; border:1px solid var(--green); padding:2px 8px; border-radius:4px; display:inline-block; margin-top:5px;">
                    <?= strtoupper($user['EmploymentStatus'] ?? 'ACTIVE') ?> OPERATOR
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:10px; color:var(--gray); margin-bottom:5px;">CURRENT BALANCE</div>
            <div style="font-size:24px; color:var(--green); font-weight:bold;">$<?= number_format($rawBalance, 2) ?></div>
            <button class="btn-outline" style="margin-top:10px;" onclick="window.open('generate_dtr.php', '_blank')">PRINT DTR</button>
        </div>
    </div>

    <div class="bar-wrapper">
        <button class="collapsible">ACCOUNT'S INFORMATION <i class="fa-solid fa-chevron-down"></i></button>
        <div class="content-panel">
            <div class="info-grid">
                <div class="info-item"><label>Full Name</label><span><?= htmlspecialchars($user['FirstName'].' '.$user['LastName']) ?></span></div>
                <div class="info-item"><label>Email Address</label><span><?= htmlspecialchars($user['Email']) ?></span></div>
                <div class="info-item"><label>Contact Number</label><span><?= htmlspecialchars($user['ContactNo']) ?></span></div>
                <div class="info-item"><label>Date of Birth</label><span><?= htmlspecialchars($user['DateOfBirth']) ?></span></div>
                <div class="info-item"><label>Physical Address</label><span><?= htmlspecialchars($user['Address']) ?></span></div>
                <div class="info-item"><label>Education</label><span><?= htmlspecialchars($user['EducationalAttainment']) ?></span></div>
                <div class="info-item" style="grid-column: span 2;"><label>Skillset Registry</label><span><?= htmlspecialchars($user['Skills']) ?></span></div>
            </div>
        </div>
    </div>

    <div class="bar-wrapper">
        <button class="collapsible">ATTENDANCE LOGS <i class="fa-solid fa-chevron-down"></i></button>
        <div class="content-panel">
            <table>
                <thead><tr><th>DATE</th><th>LOG IN</th><th>LOG OUT</th><th>STATUS</th></tr></thead>
                <tbody>
                    <?php foreach($attendanceRecords as $att): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($att['log_in_time'])) ?></td>
                        <td><?= date('h:i A', strtotime($att['log_in_time'])) ?></td>
                        <td><?= $att['log_out_time'] ? date('h:i A', strtotime($att['log_out_time'])) : '--:--' ?></td>
                        <td style="color:var(--green); font-weight:bold;"><?= htmlspecialchars($att['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bar-wrapper">
        <button class="collapsible">REVENUE REGISTRY <i class="fa-solid fa-chevron-down"></i></button>
        <div class="content-panel">
            <div style="padding: 30px; text-align: center; border-bottom: 1px solid var(--border); margin-bottom: 20px;">
                <label style="font-size:10px; color:var(--gray);">WITHDRAWABLE FUNDS</label>
                <div style="font-size:36px; color:var(--green); font-weight:bold; margin: 10px 0;">$<?= number_format($rawBalance, 2) ?></div>
                <button class="btn-green" style="width: 100%; max-width: 300px;" onclick="openWithdrawModal()">WITHDRAW & PRINT PAYSLIP</button>
            </div>
            
            <label style="font-size:10px; color:var(--gray); padding-left:10px;">RECENT EARNINGS</label>
            <table>
                <thead><tr><th>TIMESTAMP</th><th>AMOUNT</th></tr></thead>
                <tbody>
                    <?php foreach($earningRecords as $earn): ?>
                    <tr>
                        <td><?= date('M d, Y - h:i A', strtotime($earn['log_in_time'])) ?></td>
                        <td style="color:var(--green); font-weight:bold;">+$<?= number_format($earn['total_earned'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="withdrawModal" class="modal">
    <div class="modal-content">
        <h3 style="color:var(--green); margin-top:0;">CONFIRM TRANSACTION</h3>
        <p style="font-size:12px; color:var(--gray);">Authorize withdrawal by confirming your username.</p>
        <input type="number" id="withdrawAmount" placeholder="Amount to Withdraw ($)" step="0.01">
        <input type="text" id="confirmUser" placeholder="Confirm Username">
        <button class="btn-green" style="width:100%;" onclick="processWithdrawal()">AUTHORIZE & PRINT</button>
        <button class="btn-green" style="width:100%; background:transparent; border:1px solid var(--gray); color:var(--gray); margin-top:10px;" onclick="closeWithdrawModal()">CANCEL</button>
    </div>
</div>

<script>
    // Accordion Logic
    var coll = document.getElementsByClassName("collapsible");
    for (let i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function() {
            var content = this.nextElementSibling;
            content.style.maxHeight = content.style.maxHeight ? null : content.scrollHeight + "px";
        });
    }

    // Modal Logic
    function openWithdrawModal() { document.getElementById('withdrawModal').style.display = 'flex'; }
    function closeWithdrawModal() { document.getElementById('withdrawModal').style.display = 'none'; }

    function processWithdrawal() {
        const amount = document.getElementById('withdrawAmount').value;
        const user = document.getElementById('confirmUser').value;

        if(!amount || !user) { alert("Please enter both amount and username."); return; }

        fetch('process_withdrawal.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `amount=${amount}&username=${user}`
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("Withdrawal authorized! Generating PDF...");
                window.open('generate_payslip.php?amount=' + amount, '_blank');
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        });
    }
</script>
</body>
</html>