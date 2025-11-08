<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use League\OAuth2\Client\Provider\Google;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize Google OAuth provider
$provider = new Google([
    'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
    'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
    'redirectUri'  => $_ENV['GOOGLE_REDIRECT_URI'],
]);

// Step 1: If no code, get login URL
if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
}

// Step 2: Check state to prevent CSRF
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
}

// Step 3: Get access token + user details
try {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Get user info
    $ownerDetails = $provider->getResourceOwner($token);

    // Save user session
    $_SESSION['user_id'] = $ownerDetails->getId();
    $_SESSION['user_name'] = $ownerDetails->getName();
    $_SESSION['user_email'] = $ownerDetails->getEmail();

    // Redirect to dashboard
    header("Location: user_dashboard.php");
    exit();

} catch (Exception $e) {
    exit('Failed to get access token: ' . $e->getMessage());
}

// Default fallback
header("Location: templates/user_login.html");
exit();
