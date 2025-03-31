<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/mail-functions.php';

$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("SELECT id, username, email, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $error = "No account found with this email address.";
            } elseif ($user['is_verified']) {
                $error = "This account is already verified. You can <a href='login.php'>login</a> now.";
            } else {
                $verification_code = generate_verification_code(6);
                
                $stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
                $stmt->execute([$verification_code, $user['id']]);
                
                $mail_sent = send_verification_email($user['email'], $user['username'], $verification_code);
                
                if ($mail_sent) {
                    $success = true;
                    $_SESSION['pending_verification_email'] = $email;
                } else {
                    $error = "We couldn't send the verification email. Please try again later.";
                }
            }
        } catch(PDOException $e) {
            $error = "Database connection error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="resend-verification-container">
            <h2 class="form-title">Resend Verification Code</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <p>Verification code has been sent! Please check your inbox (and spam folder).</p>
                </div>
                <div class="text-center">
                    <a href="verify.php" class="btn btn-primary">Enter Verification Code</a>
                </div>
            <?php else: ?>
                <p>Enter your email address below to receive a new verification code.</p>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Send Verification Code</button>
                    </div>
                    <hr>
                    <div class="text-center">
                        <p><a href="login.php">Back to Login</a></p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
