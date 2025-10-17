<?php
// review_request.php - HR reviews project requests and gets AI recommendations
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr_admin') {
    header("Location: ../login.php");
    exit();
}

$request_id = $_GET['request_id'];
$sql = "SELECT * FROM project_requests WHERE id = $request_id";
$result = $conn->query($sql);
$request = $result->fetch_assoc();

// Get AI recommendations by calling Python script
$skills_required = escapeshellarg($request['required_skills']);
$python_script = "../python_scripts/get_recommendations.py";
$command = "python $python_script $skills_required 2>&1";
$output = shell_exec($command);
$recommendations = json_decode($output, true);

// If we got recommendations, display them
if (is_array($recommendations)) {
    // Save recommendations to database
    foreach ($recommendations as $index => $employee) {
        $employee_id = $employee['id'];
        $score = $employee['score'];
        $sql = "INSERT INTO ai_recommendations (request_id, employee_id, score, rank) 
                VALUES ($request_id, $employee_id, $score, $index+1) 
                ON DUPLICATE KEY UPDATE score = $score, rank = $index+1";
        $conn->query($sql);
    }
}
?>
<?php include '../header.php'; ?>

    <h2>Review Project Request: <?php echo $request['project_name']; ?></h2>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5>Project Details</h5>
        </div>
        <div class="card-body">
            <p><strong>Skills Required:</strong> <?php echo $request['required_skills']; ?></p>
            <p><strong>Employees Needed:</strong> <?php echo $request['employees_needed']; ?></p>
            <p><strong>Duration:</strong> <?php echo $request['duration']; ?> days</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>AI Recommendations</h5>
        </div>
        <div class="card-body">
            <?php if (is_array($recommendations) && count($recommendations) > 0): ?>
                <form action="assign_employees.php" method="POST">
                    <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                    
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Rank</th>
                                <th>Employee Name</th>
                                <th>Skills</th>
                                <th>Match Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recommendations as $index => $employee): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="employees[]" value="<?php echo $employee['id']; ?>" 
                                            <?php echo $index < $request['employees_needed'] ? 'checked' : ''; ?>>
                                    </td>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $employee['name']; ?></td>
                                    <td><?php echo $employee['skills']; ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $employee['score'] * 100; ?>%;" 
                                                 aria-valuenow="<?php echo $employee['score'] * 100; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo round($employee['score'] * 100, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <button type="submit" class="btn btn-success">Assign Selected Employees</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p>No recommendations available or there was an error processing the request.</p>
                    <p>Error: <?php echo $output; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include '../footer.php'; ?>