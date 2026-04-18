<?php
// 1. Error Reporting for Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['employee_id'])) {
    die("Error: No active session found. Please log in again.");
}

// 2. Check if file was sent via the form
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $userId = $_SESSION['employee_id'];
    $targetDir = "uploads/";
    
    // Create folder if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Generate unique name to prevent overwriting
    $fileName = time() . '_' . basename($_FILES["profile_image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // 3. Validation
    $allowTypes = array('jpg','png','jpeg','gif');
    if (in_array($fileType, $allowTypes)) {
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
            
            try {
                // UPDATED: Used 'Id' (PascalCase) to match your table
                $stmt = $pdo->prepare("UPDATE employee SET profile_pic = ? WHERE Id = ?");
                $stmt->execute([$targetFilePath, $userId]);
                
                header("Location: profile.php?upload=success");
                exit();
            } catch (PDOException $e) {
                die("Database Error: " . $e->getMessage());
            }

        } else {
            die("Error: Failed to move file. Ensure 'uploads' folder permissions are correct.");
        }
    } else {
        die("Error: Invalid file type. Only JPG, PNG, JPEG, and GIF are allowed.");
    }
} else {
    die("Error: No file uploaded or upload error code: " . ($_FILES['profile_image']['error'] ?? 'No file detected'));
}
?>