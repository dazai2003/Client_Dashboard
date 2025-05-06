<?php
session_start();
include '../includes/db.php';

// Assuming client ID is stored in session
$client_id = $_SESSION['user_id'];

// Fetch package price
$stmt = $conn->prepare("SELECT p.name, p.price 
                       FROM packages p 
                       JOIN client_selections cs ON p.id = cs.package_id 
                       WHERE cs.client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$stmt->bind_result($package_name, $package_price);
$has_package = $stmt->fetch();
$stmt->close();

// Initialize payment status
$payment_status = "";
$payment_class = "";

// Handle payment submission
if (isset($_POST['make_payment'])) {
    // In a real application, you would integrate with a payment gateway here
    // For now, just simulate a successful payment
    $payment_status = "Payment successful! Your membership is now active.";
    $payment_class = "alert-success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment</title>
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
        }
        h1 {
            color: #007aff;
            margin-bottom: 1.5rem;
        }
        .payment-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            background-color: #f9f9f9;
        }
        .payment-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .payment-form {
            margin-top: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        input {
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
        .no-package {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 2rem 0;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Payments</h1>
        
        <?php if ($payment_status): ?>
        <div class="alert <?= $payment_class ?>" role="alert">
            <?= $payment_status ?>
        </div>
        <?php endif; ?>
        
        <?php if ($has_package): ?>
        <div class="payment-card">
            <h3>Payment Summary</h3>
            <div class="payment-details">
                <span>Package:</span>
                <span><?= htmlspecialchars($package_name) ?></span>
            </div>
            <div class="payment-details">
                <span>Amount:</span>
                <span>$<?= htmlspecialchars($package_price) ?></span>
            </div>
            <div class="payment-details">
                <span>Billing Period:</span>
                <span>Monthly</span>
            </div>
        </div>
        
        <form class="payment-form" method="post">
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" placeholder="1234 5678 9012 3456" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" placeholder="MM/YY" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" placeholder="123" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="name">Name on Card</label>
                <input type="text" id="name" placeholder="John Doe" required>
            </div>
            <button type="submit" name="make_payment" class="btn btn-primary">Make Payment</button>
        </form>
        <?php else: ?>
        <div class="no-package">
            <h3>No Package Selected</h3>
            <p>Please select a package before making a payment.</p>
            <a href="#" onclick="window.parent.loadContent('select_package.php')" class="btn btn-primary">Select a Package</a>
        </div>
        <?php endif; ?>
    </div>

    <div id="popup" class="popup"></div>

    <script>
        function showPopup(message) {
            const popup = document.getElementById('popup');
            popup.textContent = message;
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($payment_status): ?>
                showPopup('<?= $payment_status ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html> 