<?php
// submit_request.php - Handles project request submission
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'project_manager') {
    $project_name = $_POST['project_name'];
    $skills = $_POST['skills'];
    $employees_needed = $_POST['employees_needed'];
    $duration = $_POST['duration'];
    $manager_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO project_requests (project_name, manager_id, required_skills, employees_needed, duration) 
            VALUES ('$project_name', $manager_id, '$skills', $employees_needed, $duration)";
    
    if ($conn->query($sql)) {
        $_SESSION['message'] = "Project request submitted successfully!";
    } else {
        $_SESSION['error'] = "Error submitting request: " . $conn->error;
    }
    
    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}
?>