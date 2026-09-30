<?php
// login.php
$pageTitle = 'Login to HeritageHub';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header("Location: " . base_url('index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in both email and password.';
    } else {
        if (login_user($email, $password)) {
            set_flash('success', 'Welcome back to HeritageHub!');
            if (is_admin()) {
                header("Location: " . base_url('admin/index.php'));
            } else {
                header("Location: " . base_url('user/dashboard.php'));
            }
            exit;
        } else {
            $error = 'Invalid email or password credentials.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card-heritage p-4">
                <div class="text-center mb-4">
                    <div class="fs-1">🏛️</div>
                    <h3 class="mt-2">Welcome Back</h3>
                    <p class="text-muted small">Access your community time capsule account</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mb-3"><?php echo e($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo base_url('login.php'); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label text-muted small font-weight-bold">EMAIL ADDRESS</label>
                        <input type="email" name="email" id="loginEmail" class="form-control form-control-heritage" placeholder="user@heritagehub.org" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small font-weight-bold">PASSWORD</label>
                        <input type="password" name="password" id="loginPassword" class="form-control form-control-heritage" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-heritage-primary w-100 mb-3">Sign In</button>
                </form>

                <div class="card p-3 bg-dark border-secondary mb-3 text-center">
                    <span class="text-warning small font-weight-bold mb-2"><i class="bi bi-key-fill"></i> DEMO QUICK ACCESS</span>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="fillDemo('admin@heritagehub.org', 'Admin@123')">Admin Credentials</button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="fillDemo('user@heritagehub.org', 'User@123')">User Credentials</button>
                    </div>
                </div>

                <div class="text-center text-muted small">
                    Don't have an account? <a href="<?php echo base_url('register.php'); ?>">Register as a contributor</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pass;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
