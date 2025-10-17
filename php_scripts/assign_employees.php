<?php
// assign_employees.php - HR assigns employees to projects
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr_admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $employees = $_POST['employees'];
    
    // Update project request status
    $sql = "UPDATE project_requests SET status = 'approved' WHERE id = $request_id";
    $conn->query($sql);
    
    // Assign selected employees
    foreach ($employees as $employee_id) {
        $sql = "INSERT INTO project_assignments (request_id, employee_id) VALUES ($request_id, $employee_id)";
        $conn->query($sql);
    }
    
    $_SESSION['message'] = "Employees assigned successfully!";
    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
?>