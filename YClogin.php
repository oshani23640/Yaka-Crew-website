<?php
session_start();

// Handle AJAX login requests from login-handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Set content type to JSON for API compatibility
    header('Content-Type: application/json');
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Default admin credentials
    $valid_username = 'admin';
    $valid_password = 'yakacrew2024';
    
    // Validate credentials
    if ($username === $valid_username && $password === $valid_password) {
        // Set session variables
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'data' => [
                'username' => $username,
                'login_time' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
    exit();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Unset all session variables
    $_SESSION = array();
    
    // Clear the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy all session data
    session_destroy();
    
    // Redirect to login page with logout message
    header("Location: YClogin.php?message=logged_out");
    exit();
}

// Check if already logged in (but allow access if force_login is set)
if (isset($_SESSION['admin']) && !isset($_GET['force_login'])) {
    header("Location: admin/YCGalleryadmin.php");
    exit();
}

// Handle login form submission
$error_message = '';
$success_message = '';

// Check for logout message
$show_logout_message = false;
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success_message = 'You have been successfully logged out.';
    $show_logout_message = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Database credentials (matching login-handler credentials)
    $valid_username = 'admin';
    $valid_password = 'yakacrew2024';
    
    // Trim any whitespace
    $username = trim($username);
    $password = trim($password);
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        
        header("Location: admin/YCGalleryadmin.php");
        exit();
    } else {
        $error_message = 'Invalid username or password. Please use: admin / yakacrew2024';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yaka Crew - Admin Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/YCGallerylogin-style.css">
</head>
<body>
  <div class="login-container">
    <div class="login-background">
      <img src="assets/images/Yaka Crew Logo.JPG" alt="Background" class="bg-image">
      <div class="overlay"></div>
    </div>
    
    <div class="login-form-container">
      <div class="login-form">
        <div class="login-header">
          <img src="assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo" class="logo">
          <h1>Admin Panel</h1>
          <p>Enter your credentials to access the admin dashboard</p>
        </div>

        <form method="POST" class="form">

          <?php if ($success_message): ?>
          <div class="success-message" id="success-message" style="margin-bottom: 18px;">
            <?php echo htmlspecialchars($success_message); ?>
          </div>
          <?php endif; ?>

          <?php if ($error_message): ?>
          <div class="error-message show" style="margin-bottom: 18px;">
            <?php echo htmlspecialchars($error_message); ?>
          </div>
          <?php endif; ?>

          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-container">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-container">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="password" name="password" placeholder="Enter your password" required>
              <button type="button" id="toggle-password" class="toggle-password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>

         <div class="form-group">
  <label class="checkbox-container">
    <input type="checkbox" id="remember-me" name="remember_me">
    <span class="checkmark">
      <i class="fas fa-check"></i>
    </span>
    <span class="checkbox-text">Remember me</span>
  </label>
</div>


          <button type="submit" class="login-btn">
            <span class="btn-text">Sign In</span>
          </button>
        </form>

        <div class="login-footer">
          <?php if (isset($_SESSION['admin'])): ?>
          <p class="force-login-link">
            <a href="login.php?force_login=1">
              Click here if you want to login with different credentials
            </a>
          </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Simple password toggle functionality
    document.getElementById('toggle-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });

    // Fade out error message after 1200ms
    setTimeout(function() {
      var errorMsg = document.querySelector('.error-message.show');
      if (errorMsg) {
        errorMsg.style.transition = 'opacity 0.5s';
        errorMsg.style.opacity = '0';
        setTimeout(function() {
          errorMsg.style.display = 'none';
        }, 500);
      }
    }, 1200);

    // Clear URL parameters after showing logout message
    <?php if ($show_logout_message): ?>
    setTimeout(function() {
        // Fade out the message
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            successMessage.style.opacity = '0';
            // Remove the element after fade out
            setTimeout(function() {
                successMessage.style.display = 'none';
                // Remove the message parameter from URL without refreshing
                const url = new URL(window.location);
                url.searchParams.delete('message');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }, 500); // Wait for fade out to complete
        }
    }, 3000); // Start fade out after 3 seconds
    <?php endif; ?>
  </script>
</body>
</html>
