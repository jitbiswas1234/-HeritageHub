<?php
// photo-detail.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$id = intval($_GET['id'] ?? 0);
$pdo = getDBConnection();

$pdo->prepare("UPDATE historical_photos SET views = views + 1 WHERE id = ?")->execute([$id]);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.name as uploader_name, u.profile_image FROM historical_photos p LEFT JOIN categories c ON p.category_id = c.id JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.status = 'approved'");
$stmt->execute([$id]);
$photo = $stmt->fetch();

if (!$photo) {
    echo "<div class='container py-5 text-center'><h2>Photo Not Found</h2><p class='text-muted'>The requested historical photograph does not exist or has not been approved yet.</p><a href='" . base_url('gallery.php') . "' class='btn btn-heritage-primary'>Back to Gallery</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($photo['title']);

$likesCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE content_type = 'photo' AND content_id = ?");
$likesCount->execute([$id]);
$totalLikes = $likesCount->fetchColumn();

$isLiked = false;
if (is_logged_in()) {
    $u = current_user();
    $lk = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND content_type = 'photo' AND content_id = ?");
    $lk->execute([$u['id'], $id]);
    if ($lk->fetch()) $isLiked = true;
}

$commentsStmt = $pdo->prepare("SELECT c.*, u.name as commenter_name, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.content_type = 'photo' AND c.content_id = ? AND c.status = 'approved' ORDER BY c.created_at DESC");
$commentsStmt->execute([$id]);
$comments = $commentsStmt->fetchAll();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <a href="<?php echo base_url('gallery.php'); ?>" class="btn btn-heritage-outline btn-sm mb-4"><i class="bi bi-arrow-left"></i> Back to Gallery</a>

            <div class="card-heritage p-4 mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-warning text-dark font-weight-bold fs-6">Year: <?php echo e($photo['year']); ?></span>
                        <?php if ($photo['category_name']): ?>
                            <span class="badge bg-indigo ms-2"><?php echo e($photo['category_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="btn btn-heritage-outline btn-like-toggle <?php echo $isLiked ? 'active text-warning' : ''; ?>" data-type="photo" data-id="<?php echo $photo['id']; ?>">
                        <i class="bi bi-heart-fill"></i> Like (<span class="like-count"><?php echo $totalLikes; ?></span>)
                    </button>
                </div>

                <h1 class="h2 text-white mb-3"><?php echo e($photo['title']); ?></h1>
                
                <div class="d-flex align-items-center gap-3 text-muted small mb-4">
                    <span><i class="bi bi-geo-alt"></i> Location: <strong><?php echo e($photo['location']); ?></strong></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-person"></i> Contributed by: <strong><?php echo e($photo['uploader_name']); ?></strong></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-eye"></i> Views: <strong><?php echo $photo['views']; ?></strong></span>
                </div>

                <?php if ($photo['is_before_after'] && !empty($photo['current_image'])): ?>
                    <div class="mb-4">
                        <div class="text-center mb-2 text-warning small fw-bold">Interactive Then &amp; Now Comparison (Drag Slider)</div>
                        <div class="before-after-wrapper" style="height: 480px;">
                            <div class="ba-image-before">
                                <img src="<?php echo base_url('uploads/photos/' . e($photo['image'])); ?>" alt="Historical View">
                                <span class="ba-label ba-label-before">THEN (<?php echo e($photo['year']); ?>)</span>
                            </div>
                            <div class="ba-image-after">
                                <img src="<?php echo base_url('uploads/photos/' . e($photo['current_image'])); ?>" alt="Current View">
                                <span class="ba-label ba-label-after">NOW (2026)</span>
                            </div>
                            <div class="ba-handle">
                                <i class="bi bi-arrows-collapse"></i>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center mb-4">
                        <img src="<?php echo base_url('uploads/photos/' . e($photo['image'])); ?>" class="img-fluid rounded-4 shadow-lg border border-secondary" style="max-height: 550px;" alt="<?php echo e($photo['title']); ?>">
                    </div>
                <?php endif; ?>

                <div class="p-3 bg-dark rounded-3 border border-secondary mb-4">
                    <h5 class="text-white mb-2">Historical Context &amp; Description</h5>
                    <p class="text-light leading-relaxed mb-0"><?php echo nl2br(e($photo['description'])); ?></p>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card-heritage p-4">
                <h3 class="h4 text-white mb-4"><i class="bi bi-chat-left-dots"></i> Community Discussion &amp; Memories</h3>

                <?php if (is_logged_in()): ?>
                    <form id="commentForm" class="mb-4">
                        <input type="hidden" name="content_type" value="photo">
                        <input type="hidden" name="content_id" value="<?php echo $photo['id']; ?>">
                        <div class="mb-3">
                            <textarea name="comment" class="form-control form-control-heritage" rows="3" placeholder="Share a memory or comment about this photograph..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-heritage-primary"><i class="bi bi-send"></i> Post Comment</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-dark border-secondary mb-4 text-center">
                        <a href="<?php echo base_url('login.php'); ?>" class="text-warning fw-bold">Login</a> or <a href="<?php echo base_url('register.php'); ?>" class="text-warning fw-bold">Register</a> to join the community discussion.
                    </div>
                <?php endif; ?>

                <div id="commentsList">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted italic" id="noCommentsText">No comments yet. Be the first to share a memory about this photo!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo base_url('uploads/profiles/' . e($c['profile_image'] ?? 'user_avatar.svg')); ?>" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                                        <strong class="text-white small"><?php echo e($c['commenter_name']); ?></strong>
                                    </div>
                                    <span class="text-muted small"><?php echo formatDate($c['created_at'], 'M j, Y g:i a'); ?></span>
                                </div>
                                <p class="text-light small mb-0"><?php echo nl2br(e($c['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(commentForm);
            const apiUrl = (window.HERITAGE_BASE_URL || '/').replace(/\/$/, '') + '/api/comment.php';
            
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    showToast('Comment posted successfully!', 'success');
                    commentForm.reset();
                    
                    const noCommentsText = document.getElementById('noCommentsText');
                    if (noCommentsText) noCommentsText.remove();

                    const avatarUrl = (window.HERITAGE_BASE_URL || '/').replace(/\/$/, '') + '/uploads/profiles/' + data.user_avatar;
                    
                    const commentHtml = `
                        <div class="p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="${avatarUrl}" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                                    <strong class="text-white small">${data.user_name}</strong>
                                </div>
                                <span class="text-muted small">${data.date}</span>
                            </div>
                            <p class="text-light small mb-0">${data.comment}</p>
                        </div>
                    `;
                    document.getElementById('commentsList').insertAdjacentHTML('afterbegin', commentHtml);
                } else {
                    showToast(data.message || 'Error posting comment', 'danger');
                }
            } catch (err) {
                showToast('Server communication error', 'danger');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
