<?php
require_once '../src/functions.php';
session_start();

// Access Control: Only for logged-in donors
if (!isLoggedIn() || $_SESSION['role'] !== 'donor') {
    setFlash('error', 'Please login as a donor to access this page.');
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .split-container {
            display: flex;
            min-height: 80vh;
            gap: 30px;
            padding: 50px 20px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        .donate-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            width: 100%;
            max-width: 400px;
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 400px;
            justify-content: center;
        }
        .donate-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        .icon-wrapper {
            font-size: 5rem;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(0,255,136,0.3));
        }
        .donate-btn {
            margin-top: auto;
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-money {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: white;
            border: none;
        }
        .btn-food {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 class="text-gradient" style="text-align: center; margin-top: 40px;">How would you like to help?</h1>
        <p style="text-align: center; color: var(--text-muted);">Choose an option below to make a difference today.</p>

        <div class="split-container">
            <!-- Donate Money Option -->
            <div class="donate-card" onclick="window.location.href='donate_money.php'">
                <div class="icon-wrapper">💰</div>
                <h2 style="color: var(--text-main); margin-bottom: 15px;">Donate Money</h2>
                <p style="color: var(--text-muted); margin-bottom: 30px;">
                    Support our operations and logistics. Every penny helps us reach more people in need.
                </p>
                <button class="donate-btn btn-money">Give Funds &rarr;</button>
            </div>

            <!-- Donate Food Option -->
            <div class="donate-card" onclick="window.location.href='donate_food.php'">
                <div class="icon-wrapper">🍲</div>
                <h2 style="color: var(--text-main); margin-bottom: 15px;">Donate Food</h2>
                <p style="color: var(--text-muted); margin-bottom: 30px;">
                    Share your surplus food. Connect directly with NGOs to feed the hungry and reduce waste.
                </p>
                <button class="donate-btn btn-food">Give Food &rarr;</button>
            </div>
        </div>
    </div>
</body>
</html>
