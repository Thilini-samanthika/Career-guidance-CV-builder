<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use League\OAuth2\Client\Provider\Google;

// ✅ Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ✅ Google OAuth provider
$provider = new Google([
    'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
    'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
    'redirectUri'  => $_ENV['GOOGLE_REDIRECT_URI'],
]);

// If user already logged in → redirect based on role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'company') {
        header("Location: company_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Career Guidance CV Builder</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      color: #fff;
      overflow-x: hidden;
    }
    
    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      position: relative;
      padding: 2rem;
    }
    
    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>');
      opacity: 0.3;
      z-index: 1;
    }
    
    .hero-content {
      position: relative;
      z-index: 2;
      text-align: center;
      max-width: 800px;
    }
    
    .hero h1 {
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 700;
      margin-bottom: 1.5rem;
      background: linear-gradient(45deg, #fff, #e0e7ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInUp 1s ease-out;
    }
    
    .hero p {
      font-size: 1.25rem;
      margin-bottom: 3rem;
      opacity: 0.9;
      animation: fadeInUp 1s ease-out 0.2s both;
    }
    
    .btn-container {
      display: flex;
      gap: 1.5rem;
      justify-content: center;
      flex-wrap: wrap;
      animation: fadeInUp 1s ease-out 0.4s both;
    }
    
    .btn-custom {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      font-weight: 600;
      padding: 1rem 2rem;
      border-radius: 50px;
      text-decoration: none;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      min-width: 160px;
    }
    
    .btn-custom::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .btn-custom:hover::before {
      left: 100%;
    }
    
    .btn-custom:hover {
      background: rgba(255, 255, 255, 0.2);
      border-color: rgba(255, 255, 255, 0.4);
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .btn-primary-custom {
      background: linear-gradient(45deg, #ff6b6b, #ee5a24);
      border-color: transparent;
    }
    
    .btn-primary-custom:hover {
      background: linear-gradient(45deg, #ee5a24, #ff6b6b);
    }
    
    .floating-elements {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
    }
    
    .floating-element {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }
    
    .floating-element:nth-child(1) {
      width: 80px;
      height: 80px;
      top: 20%;
      left: 10%;
      animation-delay: 0s;
    }
    
    .floating-element:nth-child(2) {
      width: 120px;
      height: 120px;
      top: 60%;
      right: 15%;
      animation-delay: 2s;
    }
    
    .floating-element:nth-child(3) {
      width: 60px;
      height: 60px;
      bottom: 20%;
      left: 20%;
      animation-delay: 4s;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes float {
      0%, 100% {
        transform: translateY(0px) rotate(0deg);
      }
      50% {
        transform: translateY(-20px) rotate(180deg);
      }
    }
    
    @media (max-width: 768px) {
      .btn-container {
        flex-direction: column;
        align-items: center;
      }
      
      .btn-custom {
        width: 100%;
        max-width: 280px;
      }
    }
  </style>
</head>
<body>
  <div class="hero">
    <div class="floating-elements">
      <div class="floating-element"></div>
      <div class="floating-element"></div>
      <div class="floating-element"></div>
    </div>
    
    <div class="hero-content">
      <h1><i class="fas fa-briefcase"></i> Career Guidance CV Builder</h1>
      <p>Transform your professional journey with our intelligent CV builder and career guidance platform</p>
      
      <div class="btn-container">
        <a href="templates/user_login.html" class="btn-custom btn-primary-custom">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </a>
        <a href="templates/user_register.html" class="btn-custom">
          <i class="fas fa-user-plus"></i> Sign Up
        </a>
      </div>
    </div>
  </div>
</body>
</html>
