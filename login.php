<?php
session_start();
ob_start();

// ১. ডাটাবেস কানেকশন

require_once('config/db_connect.php');

$error_msg = "";

// ২. লগইন লজিক
if (isset($_POST['admin_login'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);

    // ডাটাবেসে ইউজার আইডি এবং পাসওয়ার্ড চেক করা
    $query = "SELECT * FROM users WHERE user_id = '$user_id' AND password = '$pass'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user_data = mysqli_fetch_assoc($result);
        
        // সেশনে ডাটা সেভ করা (এটি আপনার পুরো প্রজেক্টে ইউজারের পরিচয় বহন করবে)
        $_SESSION['user_id'] = $user_data['user_id'];
        $_SESSION['user_name'] = $user_data['name'];

        // লগইন সফল হলে ড্যাশবোর্ডে নিয়ে যাবে
        header("Location: admin/home.php"); 
        exit;
    } else {
        $error_msg = "ভুল বায়ার আইডি অথবা পাসওয়ার্ড!";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Sample Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --admin-theme: #6366f1; --admin-hover: #4f46e5; }
        body { background: #f0f2f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .login-card { background: white; border-radius: 15px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: 1px solid #e1e4e8; }
        .admin-icon { font-size: 55px; color: var(--admin-theme); margin-bottom: 15px; }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #dee2e6; font-size: 0.95rem; }
        .btn-admin { background: var(--admin-theme); color: white; border-radius: 10px; padding: 12px; font-weight: 600; width: 100%; transition: 0.3s ease; border: none; text-transform: uppercase; letter-spacing: 1px; }
        .btn-admin:hover { background: var(--admin-hover); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3); }
        .error-box { background: #fff5f5; color: #c53030; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; border: 1px solid #feb2b2; }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="admin-icon">
        <i class="fas fa-user-shield"></i>
    </div>
    <h4 class="fw-bold">Login</h4>
    <p class="text-muted small mb-4">ENTER YOUR CREDENTIALS TO CONTINUE.</p>
    
    <?php if(!empty($error_msg)): ?>
        <div class="error-box text-start">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="mb-3 text-start">
            <label class="form-label small fw-bold text-secondary">BUYER ID:</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                <input type="text" name="username" class="form-control border-start-0" placeholder="Ex: 2026" required>
            </div>
        </div>
        
        <div class="mb-4 text-start">
            <label class="form-label small fw-bold text-secondary">PASSWORD:</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
            </div>
        </div>
        
        <button type="submit" name="admin_login" class="btn-admin">
            Sign In <i class="fas fa-sign-in-alt ms-2"></i>
        </button>
    </form>
</div>

</body>
</html>