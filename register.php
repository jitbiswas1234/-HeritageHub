<?php
// register.php
$pageTitle = 'Join HeritageHub Capsule';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header("Location: " . base_url('index.php'));
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $bio = trim($_POST['bio'] ?? '');

    if (empty($name)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $pdo = getDBConnection();
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Email address is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            
            $avatar = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $avatar = upload_media_file($_FILES['profile_image'], 'profiles', ['image/jpeg', 'image/png', 'image/webp'], 5242880);
                } catch (Exception $e) {
                    $errors[] = 'Avatar upload failed: ' . $e->getMessage();
                }
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, profile_image, bio, status) VALUES (?, ?, ?, 'user', ?, ?, 'active')");
                $stmt->execute([$name, $email, $hashed, $avatar, $bio]);

                login_user($email, $password);
                set_flash('success', 'Registration successful! Welcome to HeritageHub.');
                header("Location: " . base_url('user/dashboard.php'));
                exit;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card-heritage p-4">
                <div class="text-center mb-4">
                    <div class="fs-1">✍️</div>
                    <h3 class="mt-2">Join Community Archive</h3>
                    <p class="text-muted small">Help preserve our neighborhood history for future generations</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo base_url('register.php'); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label text-muted small font-weight-bold">FULL NAME *</label>
                        <input type="text" name="name" class="form-control form-control-heritage" placeholder="e.g. Arthur Pendelton" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small font-weight-bold">EMAIL ADDRESS *</label>
                        <input type="email" name="email" class="form-control form-control-heritage" placeholder="name@domain.com" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small font-weight-bold">PASSWORD *</label>
                            <input type="password" name="password" class="form-control form-control-heritage" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small font-weight-bold">CONFIRM PASSWORD *</label>
                            <input type="password" name="confirm_password" class="form-control form-control-heritage" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small font-weight-bold">PROFILE AVATAR (OPTIONAL)</label>
                        <input type="file" name="profile_image" class="form-control form-control-heritage" accept="image/*">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small font-weight-bold">SHORT BIO / COMMUNITY RELATION</label>
                        <textarea name="bio" class="form-control form-control-heritage" rows="2" placeholder="Tell us how long you've lived in the neighborhood or your connection to local history..."><?php echo e($_POST['bio'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-heritage-primary w-100 mb-3">Create Contributor Account</button>
                </form>

                <div class="text-center text-muted small">
                    Already have an account? <a href="<?php echo base_url('login.php'); ?>">Log in here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
