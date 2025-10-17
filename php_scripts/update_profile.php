<?php
// update_profile.php - Handles employee profile updates
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'employee') {
    $user_id = $_SESSION['user_id'];
    $skills = $_POST['skills'];
    $education = $_POST['education'];
    
    // Update skills and education
    $sql = "UPDATE employees SET skills = '$skills', education = '$education' WHERE user_id = $user_id";
    $conn->query($sql);
    
    // Handle file uploads
    if (!empty($_FILES['certificates']['name'][0])) {
        $upload_dir = '../uploads/';
        
        foreach ($_FILES['certificates']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['certificates']['name'][$key];
            $file_tmp = $_FILES['certificates']['tmp_name'][$key];
            $file_path = $upload_dir . time() . '_' . $file_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Call Python OCR script to process the document
                $escaped_file_path = escapeshellarg($file_path);
                $escaped_user_id = escapeshellarg($user_id);
                $python_script = "../python_scripts/process_documents.py";
                $command = "python $python_script $escaped_user_id $escaped_file_path 2>&1";
                $output = shell_exec($command);
                
                error_log("Python OCR Output: " . $output);
            }
        }
    }
    
    $_SESSION['message'] = "Profile updated successfully!";
    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}
?>