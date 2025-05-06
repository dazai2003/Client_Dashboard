<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Fetch available packages
$packages = mysqli_query($conn, "SELECT * FROM packages");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    $package_id = $_POST['package_id'];
    $check = mysqli_query($conn, "SELECT * FROM client_selections WHERE client_id = $client_id");

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE client_selections SET package_id = $package_id WHERE client_id = $client_id");
    } else {
        mysqli_query($conn, "INSERT INTO client_selections (client_id, trainer_id, package_id) VALUES ($client_id, NULL, $package_id)");
    }

    // Redirect to my selections page
    header("Location: index.php?selection=Package");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Package</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f7;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-left: 250px;
            max-width: calc(100% - 300px);
        }
        h1 {
            color: #007aff;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 1rem;
        }
        button {
            background-color: #007aff;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select a Package</h1>
        <form id="packageForm" action="select_package.php" method="POST">
            <div class="form-group">
                <label for="package_id">Choose a Package:</label>
                <select id="package_id" name="package_id" class="form-control" required>
                    <?php while ($p = mysqli_fetch_assoc($packages)) { ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> - $<?= htmlspecialchars($p['price']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Select Package</button>
        </form>
    </div>
</body>
</html> 