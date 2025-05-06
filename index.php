<?php
// client/index.php
// Client dashboard placeholder

session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit;
}

// Get client name
$id = $_SESSION['user_id'];
$res = mysqli_query($conn, "SELECT name FROM users WHERE id = $id");

if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    $name = $row['name'];
} else {
    // Handle the case where the user is not found
    $name = "Guest"; // or handle the error appropriately
}

// Fetch client selections
$query = "SELECT t.name AS trainer_name, 
                 p.name AS package_name, 
                 cl.name AS class_name,
                 p.price AS package_price
          FROM client_selections cs
          LEFT JOIN trainers t ON cs.trainer_id = t.id
          LEFT JOIN packages p ON cs.package_id = p.id
          LEFT JOIN client_class_selections ccs ON ccs.client_id = cs.client_id
          LEFT JOIN classes cl ON ccs.class_id = cl.id
          WHERE cs.client_id = ?
          ORDER BY ccs.id DESC
          LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$selections = $stmt->get_result();
$selection = mysqli_fetch_assoc($selections);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitZone Gym - Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #4b0082;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            height: 100vh;
            position: fixed;
            z-index: 100;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #3a006b;
        }
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            width: calc(100% - 250px);
        }
        .content {
            padding: 2rem;
            background: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin: 1rem;
            min-height: calc(100vh - 2rem);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            margin-bottom: 2rem;
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .logout {
            background-color: #ff3b30;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .logout:hover {
            background-color: #c1271d;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card h3 {
            color: #4b0082;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .dashboard-card p {
            color: #666;
            margin-bottom: 1.5rem;
        }
        .dashboard-card .btn {
            width: 100%;
        }
        @media (max-width: 992px) {
            .card-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                min-height: auto;
            }
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .content {
                margin: 0;
                min-height: auto;
            }
            .card-grid {
                grid-template-columns: 1fr;
            }
        }
        .selection-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.5rem 1rem;
            align-items: center;
        }
        .floating-dock {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 1.5rem;
            margin: 1rem 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }
        .floating-dock:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }
        .popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007aff;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }
        .sidebar h2 {
            background-color: #ffcc00;
            color: #4b0082;
            padding: 0.5rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1rem;
        }
        .sidebar {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar .logout {
            margin-top: auto;
            margin-bottom: 1in;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>FitZone Gym</h2>
        <a href="#" onclick="loadContent('select_class.php')"><i class="fas fa-dumbbell"></i> Select Class</a>
        <a href="#" onclick="loadContent('select_package.php')"><i class="fas fa-box"></i> Select Package</a>
        <a href="#" onclick="loadContent('select_trainer.php')"><i class="fas fa-user"></i> Select Trainer</a>
        <a href="#" onclick="loadContent('my_selections.php')"><i class="fas fa-credit-card"></i> Make Payment</a>
        <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="content-wrapper">
        <div class="content">
            <div class="header">
                <h2>Welcome, <?= htmlspecialchars($name) ?></h2>
                <div><?= date('l, F j, Y') ?></div>
            </div>

            <div class="card-grid">
                <div class="dashboard-card floating-dock" onclick="selectClass()">
                    <h3><i class="fas fa-dumbbell"></i> Class</h3>
                    <p><?= isset($selection['class_name']) ? htmlspecialchars($selection['class_name']) : 'No class selected' ?></p>
                </div>
                <div class="dashboard-card floating-dock" onclick="selectPackage()">
                    <h3><i class="fas fa-box"></i> Package</h3>
                    <p><?= isset($selection['package_name']) ? htmlspecialchars($selection['package_name']) : 'No package selected' ?></p>
                </div>
                <div class="dashboard-card floating-dock" onclick="selectTrainer()">
                    <h3><i class="fas fa-user"></i> Trainer</h3>
                    <p><?= isset($selection['trainer_name']) ? htmlspecialchars($selection['trainer_name']) : 'No trainer selected' ?></p>
                </div>
            </div>

            <div id="main-content">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>

    <div id="popup" class="popup"></div>

    <script>
        function loadContent(page) {
            fetch(page)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.text();
                })
                .then(html => {
                    document.getElementById('main-content').innerHTML = html;
                })
                .catch(error => console.error('Error loading content:', error));
        }
        
        // Show dashboard content by default
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('main-content').innerHTML = '';
        });

        function showPopup(message) {
            const popup = document.getElementById('popup');
            popup.textContent = message;
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 3000);
        }

        function selectClass() {
            // Simulate class selection
            showPopup('Class selected: <?= htmlspecialchars($selection['class_name']) ?>');
        }

        function selectPackage() {
            // Simulate package selection
            showPopup('Package selected: <?= htmlspecialchars($selection['package_name']) ?>');
        }

        function selectTrainer() {
            // Simulate trainer selection
            showPopup('Trainer selected: <?= htmlspecialchars($selection['trainer_name']) ?>');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const selection = urlParams.get('selection');
            if (selection) {
                showPopup(selection + ' selected successfully!');
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
</body>
</html> 