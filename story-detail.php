<?php
// story-detail.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$id = intval($_GET['id'] ?? 0);
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT s.*, u.name as author_name, u.profile_image FROM stories s JOIN users u ON s.user_id = u.id WHERE s.id = ? AND s.status = 'approved'");
$stmt->execute([$id]);
$story = $stmt->fetch();

if (!$story) {
    echo "<div class='container py-5 text-center'><h2>Story Not Found</h2><p class='text-muted'>The requested story does not exist or is pending review.</p><a href='" . base_url('stories.php') . "' class='btn btn-heritage-primary'>Back to Stories</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($story['title']);

// Likes
$likesCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE content_type = 'story' AND content_id = ?");
$likesCount->execute([$id]);
$totalLikes = $likesCount->fetchColumn();

$isLiked = false;
if (is_logged_in()) {
    $u = current_user();
    $lk = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND content_type = 'story' AND content_id = ?");
    $lk->execute([$u['id'], $id]);
    if ($lk->fetch()) $isLiked = true;
}

// Comments
$commentsStmt = $pdo->prepare("SELECT c.*, u.name as commenter_name, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.content_type = 'story' AND c.content_id = ? AND c.status = 'approved' ORDER BY c.created_at DESC");
$commentsStmt->execute([$id]);
$comments = $commentsStmt->fetchAll();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <a href="<?php echo base_url('stories.php'); ?>" class="btn btn-heritage-outline btn-sm mb-4"><i class="bi bi-arrow-left"></i> Back to Stories</a>

            <div class="card-heritage p-4 p-md-5 mb-5">
                <?php if ($story['is_memory_of_month']): ?>
                    <span class="memory-badge mb-3"><i class="bi bi-star-fill"></i> MEMORY OF THE MONTH</span>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-warning text-dark font-weight-bold">Year: <?php echo e($story['year']); ?></span>
                    <button type="button" class="btn btn-heritage-outline btn-like-toggle <?php echo $isLiked ? 'active text-warning' : ''; ?>" data-type="story" data-id="<?php echo $story['id']; ?>">
                        <i class="bi bi-heart-fill"></i> Like (<span class="like-count"><?php echo $totalLikes; ?></span>)
                    </button>
                </div>

                <h1 class="h2 text-white mb-3"><?php echo e($story['title']); ?></h1>

                <div class="d-flex align-items-center gap-3 border-bottom border-secondary pb-3 mb-4">
                    <img src="<?php echo base_url('uploads/profiles/' . e($story['profile_image'] ?? 'user_avatar.svg')); ?>" class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover;">
                    <div>
                        <div class="text-white fw-bold"><?php echo e($story['person_name'] ?: $story['author_name']); ?></div>
                        <div class="text-muted small"><i class="bi bi-geo-alt"></i> Location: <?php echo e($story['location']); ?></div>
                    </div>
                </div>

                <?php if ($story['image']): ?>
                    <div class="mb-4 text-center">
                        <img src="<?php echo base_url('uploads/photos/' . e($story['image'])); ?>" class="img-fluid rounded-3 border border-secondary" alt="<?php echo e($story['title']); ?>">
                    </div>
                <?php endif; ?>

                <div class="text-light leading-relaxed fs-5 mb-4" style="white-space: pre-line;">
                    <?php echo e($story['story']); ?>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card-heritage p-4">
                <h3 class="h4 text-white mb-4"><i class="bi bi-chat-left-dots"></i> Reader Responses &amp; Comments</h3>

                <?php if (is_logged_in()): ?>
                    <form id="commentForm" class="mb-4">
                        <input type="hidden" name="content_type" value="story">
                        <input type="hidden" name="content_id" value="<?php echo $story['id']; ?>">
                        <div class="mb-3">
                            <textarea name="comment" class="form-control form-control-heritage" rows="3" placeholder="Share your thoughts on this story..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-heritage-primary"><i class="bi bi-send"></i> Submit Response</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-dark border-secondary mb-4 text-center">
                        <a href="<?php echo base_url('login.php'); ?>" class="text-warning fw-bold">Login</a> or <a href="<?php echo base_url('register.php'); ?>" class="text-warning fw-bold">Register</a> to leave a response.
                    </div>
                <?php endif; ?>

                <div id="commentsList">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted italic" id="noCommentsText">No responses yet. Be the first to share your thoughts!</p>
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
