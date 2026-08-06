<?php
// src/social_auth.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

session_start();

$config = require_once __DIR__ . '/../config/oauth.php';
$provider = $_GET['provider'] ?? '';

if (!$provider || !isset($config[$provider])) {
    redirect('../public/login.php');
}

// REAL WORLD OAUTH FLOW: STEP 1 (Redirection to Provider)
if (!isset($_GET['code']) && !isset($_GET['credential'])) {
    $is_placeholder = ($config[$provider]['client_id'] === 'YOUR_CLIENT_ID' || strpos($config[$provider]['client_id'], 'YOUR_') === 0);

    if ($is_placeholder) {
        // DEMO MODE: Skip redirection and use mock profiles immediately
        $_SESSION['demo_mode'] = true;
        // The code will naturally fall through to the mock profile section below
    } else {
        $auth_urls = [
            'google' => "https://accounts.google.com/o/oauth2/v2/auth?client_id={$config['google']['client_id']}&redirect_uri=" . urlencode($config['google']['redirect_uri']) . "&response_type=code&scope=email%20profile",
            'facebook' => "https://www.facebook.com/v12.0/dialog/oauth?client_id={$config['facebook']['client_id']}&redirect_uri=" . urlencode($config['facebook']['redirect_uri']) . "&scope=email",
            'linkedin' => "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$config['linkedin']['client_id']}&redirect_uri=" . urlencode($config['linkedin']['redirect_uri']) . "&scope=r_emailaddress%20r_liteprofile"
        ];

        header("Location: " . $auth_urls[$provider]);
        exit;
    }
}

// REAL WORLD OAUTH FLOW: STEP 2 (Handling Callback)
// In a production app, you would exchange the 'code' for an 'access_token' here.
// For now, if we get a code (or a Google 'credential'), we simulate the profile fetch.

$mockProfiles = [
    'google' => [
        'username' => 'Gaurab Google',
        'email' => 'gaurab.google@gmail.com',
        'role' => 'donor'
    ],
    'facebook' => [
        'username' => 'Gaurab FB',
        'email' => 'gaurab.fb@gmail.com',
        'role' => 'donor'
    ],
    'linkedin' => [
        'username' => 'Gaurab LinkedIn',
        'email' => 'gaurab.li@gmail.com',
        'role' => 'volunteer'
    ]
];

if (isset($mockProfiles[$provider])) {
    $profile = $mockProfiles[$provider];
    
    // Check if user exists in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$profile['email']]);
    $user = $stmt->fetch();

    if (!$user) {
        // Auto-register social user
        $sql = "INSERT INTO users (username, email, role, password_hash) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$profile['username'], $profile['email'], $profile['role'], 'SOCIAL_LOGIN']);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$profile['email']]);
        $user = $stmt->fetch();
    }

    // Set Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['username'] = $user['username'];

    $is_demo = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'];
    setFlash('success', "Welcome back via " . ucfirst($provider) . ($is_demo ? " (Demo Mode)" : "") . "!");
    unset($_SESSION['demo_mode']); // Clear for next time
    
    // Redirect based on role
    if ($user['role'] === 'donor') redirect('../public/donate.php');
    if ($user['role'] === 'volunteer') redirect('../public/dashboard_volunteer.php');
    redirect('../public/dashboard.php');
} else {
    setFlash('error', 'Social login failed');
    redirect('../public/login.php');
}
