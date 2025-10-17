<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Resource Management System</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <span class="navbar-text ms-auto">
                Logged in as: <?php echo $_SESSION['role']; ?> | 
                <a href="logout.php" class="text-light">Logout</a>
            </span>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-4"></div>