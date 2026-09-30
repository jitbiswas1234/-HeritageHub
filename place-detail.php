<?php
// place-detail.php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$id = intval($_GET['id'] ?? 0);
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM historical_places p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    echo "<div class='container py-5 text-center'><h2>Landmark Not Found</h2><p class='text-muted'>The requested historical landmark could not be located.</p><a href='" . base_url('places.php') . "' class='btn btn-heritage-primary'>Back to Landmarks</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($place['name']);

// Likes
$likesCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE content_type = 'place' AND content_id = ?");
$likesCount->execute([$id]);
$totalLikes = $likesCount->fetchColumn();

$isLiked = false;
if (is_logged_in()) {
    $u = current_user();
    $lk = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND content_type = 'place' AND content_id = ?");
    $lk->execute([$u['id'], $id]);
    if ($lk->fetch()) $isLiked = true;
}

// Comments
$commentsStmt = $pdo->prepare("SELECT c.*, u.name as commenter_name, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.content_type = 'place' AND c.content_id = ? AND c.status = 'approved' ORDER BY c.created_at DESC");
$commentsStmt->execute([$id]);
$comments = $commentsStmt->fetchAll();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <a href="<?php echo base_url('places.php'); ?>" class="btn btn-heritage-outline btn-sm mb-4"><i class="bi bi-arrow-left"></i> Back to Landmarks Directory</a>

            <div class="card-heritage p-4 p-md-5 mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-warning text-dark font-weight-bold fs-6">Est. <?php echo e($place['established_year']); ?></span>
                    <button type="button" class="btn btn-heritage-outline btn-like-toggle <?php echo $isLiked ? 'active text-warning' : ''; ?>" data-type="place" data-id="<?php echo $place['id']; ?>">
                        <i class="bi bi-heart-fill"></i> Like (<span class="like-count"><?php echo $totalLikes; ?></span>)
                    </button>
                </div>

                <h1 class="h2 text-white mb-2"><?php echo e($place['name']); ?></h1>
                <p class="text-muted mb-4"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo e($place['address']); ?></p>

                <!-- Before & After Comparison Slider -->
                <?php if (!empty($place['old_image']) && !empty($place['current_image'])): ?>
                    <div class="mb-4">
                        <div class="text-center mb-2 text-warning small fw-bold">Interactive Landmark Transformation (Drag Gold Slider)</div>
                        <div class="before-after-wrapper" style="height: 480px;">
                            <div class="ba-image-before">
                                <img src="<?php echo base_url('uploads/places/' . e($place['old_image'])); ?>" alt="Historical View">
                                <span class="ba-label ba-label-before">THEN (<?php echo e($place['established_year']); ?>)</span>
                            </div>
                            <div class="ba-image-after">
                                <img src="<?php echo base_url('uploads/places/' . e($place['current_image'])); ?>" alt="Current View">
                                <span class="ba-label ba-label-after">NOW (2026)</span>
                            </div>
                            <div class="ba-handle">
                                <i class="bi bi-arrows-collapse"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="p-4 bg-dark rounded-3 border border-secondary mb-4">
                    <h4 class="h5 text-white mb-3"><i class="bi bi-book me-2"></i> Landmark Overview &amp; Architectural History</h4>
                    <p class="text-light leading-relaxed mb-0"><?php echo nl2br(e($place['description'])); ?></p>
                </div>

                <!-- Single Landmark Map View -->
                <div class="mb-3">
                    <h5 class="text-white mb-2">Location Coordinates</h5>
                    <div id="singlePlaceMap" style="width: 100%; height: 300px; border-radius: var(--radius-sm);" class="border border-secondary"></div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card-heritage p-4">
                <h3 class="h4 text-white mb-4"><i class="bi bi-chat-left-dots"></i> Memories &amp; Local Anecdotes About This Landmark</h3>

                <?php if (is_logged_in()): ?>
                    <form id="commentForm" class="mb-4">
                        <input type="hidden" name="content_type" value="place">
                        <input type="hidden" name="content_id" value="<?php echo $place['id']; ?>">
                        <div class="mb-3">
                            <textarea name="comment" class="form-control form-control-heritage" rows="3" placeholder="Share a personal memory or story associated with <?php echo e($place['name']); ?>..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-heritage-primary"><i class="bi bi-send"></i> Post Comment</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-dark border-secondary mb-4 text-center">
                        <a href="<?php echo base_url('login.php'); ?>" class="text-warning fw-bold">Login</a> or <a href="<?php echo base_url('register.php'); ?>" class="text-warning fw-bold">Register</a> to post landmark memories.
                    </div>
                <?php endif; ?>

                <div id="commentsList">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted italic" id="noCommentsText">No comments yet. Share your memory about this landmark!</p>
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

<script src="<?php echo base_url('assets/js/map.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const place = <?php echo json_encode($place); ?>;
    const map = L.map('singlePlaceMap').setView([place.latitude, place.longitude], 15);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);
    L.marker([place.latitude, place.longitude]).addTo(map).bindPopup(`<b>${place.name}</b><br>${place.address}`).openPopup();

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
