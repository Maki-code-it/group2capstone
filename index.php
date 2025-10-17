<?php
// index.php - Main dashboard with role-based access
include 'config.php';

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
?>
<?php include 'header.php'; ?>

    <?php if ($user_role == 'project_manager'): ?>
        <!-- Project Manager Dashboard -->
        <h2>Project Manager Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Submit Project Request</h5>
                    </div>
                    <div class="card-body">
                        <form action="php_scripts/submit_request.php" method="POST">
                            <div class="mb-3">
                                <label for="project_name" class="form-label">Project Name</label>
                                <input type="text" class="form-control" id="project_name" name="project_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="skills" class="form-label">Required Skills (comma separated)</label>
                                <textarea class="form-control" id="skills" name="skills" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="employees_needed" class="form-label">Number of Employees Needed</label>
                                <input type="number" class="form-control" id="employees_needed" name="employees_needed" required>
                            </div>
                            <div class="mb-3">
                                <label for="duration" class="form-label">Project Duration (days)</label>
                                <input type="number" class="form-control" id="duration" name="duration" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Your Project Requests</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT * FROM project_requests WHERE manager_id = $user_id ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            echo "<ul class='list-group'>";
                            while($row = $result->fetch_assoc()) {
                                $status = $row['status'];
                                $badge_color = $status == 'pending' ? 'bg-warning' : ($status == 'approved' ? 'bg-success' : 'bg-secondary');
                                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                        {$row['project_name']} (Need: {$row['employees_needed']})
                                        <span class='badge $badge_color'>{$row['status']}</span>
                                    </li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>No project requests submitted yet.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($user_role == 'hr_admin'): ?>
        <!-- HR Admin Dashboard -->
        <h2>HR Admin Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Pending Project Requests</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT pr.*, u.name as manager_name 
                                FROM project_requests pr 
                                JOIN users u ON pr.manager_id = u.id 
                                WHERE pr.status = 'pending' 
                                ORDER BY pr.created_at DESC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped'>";
                            echo "<thead><tr>
                                    <th>Project Name</th>
                                    <th>Manager</th>
                                    <th>Skills Required</th>
                                    <th>Employees Needed</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr></thead><tbody>";
                            
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['project_name']}</td>
                                        <td>{$row['manager_name']}</td>
                                        <td>{$row['required_skills']}</td>
                                        <td>{$row['employees_needed']}</td>
                                        <td>{$row['duration']} days</td>
                                        <td>
                                            <a href='php_scripts/review_request.php?request_id={$row['id']}' class='btn btn-sm btn-primary'>Review</a>
                                        </td>
                                    </tr>";
                            }
                            echo "</tbody></table></div>";
                        } else {
                            echo "<p>No pending project requests.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($user_role == 'employee'): ?>
        <!-- Employee Dashboard -->
        <h2>Employee Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Update Your Profile</h5>
                    </div>
                    <div class="card-body">
                        <form action="php_scripts/update_profile.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="skills" class="form-label">Your Skills (comma separated)</label>
                                <textarea class="form-control" id="skills" name="skills" rows="3" required>
                                    <?php
                                    $sql = "SELECT skills FROM employees WHERE user_id = $user_id";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        echo $row['skills'];
                                    }
                                    ?>
                                </textarea>
                            </div>
                            <div class="mb-3">
                                <label for="education" class="form-label">Education</label>
                                <textarea class="form-control" id="education" name="education" rows="2">
                                    <?php
                                    $sql = "SELECT education FROM employees WHERE user_id = $user_id";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        echo $row['education'];
                                    }
                                    ?>
                                </textarea>
                            </div>
                            <div class="mb-3">
                                <label for="certificates" class="form-label">Upload Certificates (PDF/Image)</label>
                                <input type="file" class="form-control" id="certificates" name="certificates[]" multiple>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Your Project Assignments</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $sql = "SELECT pa.*, pr.project_name 
                                FROM project_assignments pa 
                                JOIN project_requests pr ON pa.request_id = pr.id 
                                WHERE pa.employee_id = $user_id 
                                ORDER BY pa.assigned_at DESC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            echo "<ul class='list-group'>";
                            while($row = $result->fetch_assoc()) {
                                echo "<li class='list-group-item'>
                                        <strong>{$row['project_name']}</strong><br>
                                        Assigned on: " . date('M j, Y', strtotime($row['assigned_at'])) . "
                                    </li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>No project assignments yet.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php include 'footer.php'; ?>