<?php
// stories.php
$pageTitle = 'Community Stories & Personal Memories';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT s.*, u.name as author_name, u.profile_image FROM stories s JOIN users u ON s.user_id = u.id WHERE s.status = 'approved' ORDER BY s.is_memory_of_month DESC, s.created_at DESC");
$stories = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <span class="hero-badge"><i class="bi bi-journal-text"></i> MEMOIRS & TRADITIONS</span>
        <h1 class="display-4 font-weight-bold mb-3">Community Stories &amp; Memories</h1>
        <p class="text-muted lead max-w-2xl mx-auto">
            Personal recollections, family chronicles, local traditions, and stories about famous residents written by neighbors across generations.
        </p>
        <a href="<?php echo base_url('user/submit_story.php'); ?>" class="btn btn-heritage-primary mt-2"><i class="bi bi-pen"></i> Submit Your Story</a>
    </div>

    <div class="row g-4">
        <?php foreach ($stories as $story): ?>
            <div class="col-md-6">
                <div class="card-heritage p-4 h-100 position-relative">
                    <?php if ($story['is_memory_of_month']): ?>
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-warning text-dark font-weight-bold"><i class="bi bi-star-fill"></i> Memory of the Month</span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-secondary"><?php echo e($story['year']); ?> Era</span>
                        <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo e($story['location']); ?></span>
                    </div>

                    <h3 class="h4 text-white mb-3"><?php echo e($story['title']); ?></h3>

                    <p class="text-muted leading-relaxed mb-4 flex-grow-1">
                        "<?php echo e(mb_strimwidth($story['story'], 0, 220, '...')); ?>"
                    </p>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?php echo base_url('uploads/profiles/' . e($story['profile_image'] ?? 'user_avatar.svg')); ?>" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" alt="Author">
                            <span class="text-light small fw-bold"><?php echo e($story['person_name'] ?: $story['author_name']); ?></span>
                        </div>
                        <a href="<?php echo base_url('story-detail.php?id=' . $story['id']); ?>" class="btn btn-heritage-outline btn-sm">Read Full Memory &rarr;</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
