<?php
session_start();
require_once 'includes/db.php';

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['Password'])) {
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['FullName'] = $user['FullName'];
        $_SESSION['user_role'] = $user['Role'];
        
        if($user['Role'] == 1) {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        $error = 'Email hoặc mật khẩu không đúng';
    }
}

require_once 'includes/header.php';
?>

<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="billing-details">
                    <div class="section-title">
                        <h3 class="title">Đăng nhập</h3>
                    </div>
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="form-group">
                            <input class="input" type="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <input class="input" type="password" name="password" placeholder="M���t khẩu" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="primary-btn">Đăng nhập</button>
                        </div>
                        <div class="text-center">
                            <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 