<?php
require_once 'db_connect.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Securely hash the password
    $pass = password_hash($_POST['Password'], PASSWORD_DEFAULT);

    try {
        // Check if username already exists
        $check = $pdo->prepare("SELECT Id FROM employee WHERE Username = ?");
        $check->execute([$_POST['Username']]);

        if ($check->rowCount() > 0) {
            $error = "CRITICAL: USERNAME ALREADY EXISTS";
        } else {
            // Insert new employee record
            $sql = "INSERT INTO employee 
                (FirstName, LastName, DateOfBirth, ContactNo, Address, Email, Username, Password, Skills, EducationalAttainment, EmploymentStatus) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['FirstName'], 
                $_POST['LastName'], 
                $_POST['DateOfBirth'], 
                $_POST['ContactNo'], 
                $_POST['Address'], 
                $_POST['Email'], 
                $_POST['Username'], 
                $pass, 
                $_POST['Skills'], 
                $_POST['EducationalAttainment'], 
                $_POST['EmploymentStatus']
            ]);

            // Redirect back to login with success message
            header("Location: index.php?status=registered");
            exit();
        }
    } catch (PDOException $e) {
        $error = "SYSTEM ERROR: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment | Terminal.io</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #0b0e11; --card: #1e2329; --green: #0ecb81; --border: #2b3139; --text: #eaecef; --gray: #848e9c; }
        body { font-family: 'Roboto', sans-serif; background: var(--bg); color: var(--text); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 40px 0; margin: 0; }
        .reg-box { background: var(--card); padding: 40px; border-radius: 8px; border: 1px solid var(--border); width: 420px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .logo { color: var(--green); font-size: 24px; font-weight: bold; text-align: center; display: block; text-decoration: none; margin-bottom: 25px; }
        label { font-size: 10px; color: var(--gray); display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; background: #0b0e11; border: 1px solid var(--border); color: white; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; outline: none; font-family: inherit; }
        input:focus, select:focus { border-color: var(--green); }
        button { width: 100%; padding: 14px; background: var(--green); color: black; border: none; font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .error { color: #f6465d; font-size: 12px; text-align: center; margin-bottom: 15px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 13px; color: var(--gray); }
        .footer a { color: var(--green); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="reg-box">
        <a href="index.php" class="logo"><i class="fa-solid fa-user-plus"></i> ENROLLMENT</a>
        <?php if($error): ?> <div class="error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
        <form method="POST">
            <label>FIRST NAME</label>
            <input type="text" name="FirstName" required>

            <label>LAST NAME</label>
            <input type="text" name="LastName" required>

            <label>DATE OF BIRTH</label>
            <input type="date" name="DateOfBirth" required>

            <label>CONTACT NO</label>
            <input type="text" name="ContactNo">

            <label>ADDRESS</label>
            <input type="text" name="Address">

            <label>EMAIL</label>
            <input type="email" name="Email">

            <label>USERNAME</label>
            <input type="text" name="Username" required>

            <label>PASSWORD</label>
            <input type="password" name="Password" required>

            <label>SKILLS</label>
            <input type="text" name="Skills">

            <label>EDUCATIONAL ATTAINMENT</label>
            <input type="text" name="EducationalAttainment">

            <label>EMPLOYMENT STATUS</label>
            <select name="EmploymentStatus" required>
                <option value="Regular">Regular</option>
                <option value="Contractual">Contractual</option>
                <option value="Job Order">Job Order</option>
            </select>

            <button type="submit">INITIALIZE PROTOCOL</button>
        </form>
        <div class="footer">
            Already authorized? <a href="index.php">LOG IN</a>
        </div>
    </div>
</body>
</html>
