<?php
// gallery.php
$pageTitle = 'Historical Photo Gallery & Before/After Comparison';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$photos = $pdo->query("SELECT p.*, c.name as category_name, u.name as uploader_name FROM historical_photos p LEFT JOIN categories c ON p.category_id = c.id JOIN users u ON p.user_id = u.id WHERE p.status = 'approved' ORDER BY p.year ASC, p.created_at DESC")->fetchAll();

$beforeAfterPhotos = array_filter($photos, function($p) {
    return $p['is_before_after'] == 1 && !empty($p['current_image']);
});
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <span class="hero-badge"><i class="bi bi-images"></i> VISUAL TIME CAPSULE</span>
        <h1 class="display-4 font-weight-bold mb-3">Historical Photograph Archive</h1>
        <p class="text-muted lead max-w-2xl mx-auto">
            Browse authentic historical photographs submitted by community members. Use the tab below to switch into interactive Then vs. Now Before &amp; After comparison mode!
        </p>
    </div>

    <!-- Gallery View Mode Tabs -->
    <ul class="nav nav-pills justify-content-center mb-4" id="galleryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active btn-heritage-outline me-2" id="grid-tab" data-bs-toggle="tab" data-bs-target="#grid-pane" type="button" role="tab" aria-controls="grid-pane" aria-selected="true">
                <i class="bi bi-grid-3x3-gap"></i> All Photographs (<?php echo count($photos); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-heritage-outline" id="ba-tab" data-bs-toggle="tab" data-bs-target="#ba-pane" type="button" role="tab" aria-controls="ba-pane" aria-selected="false">
                <i class="bi bi-sliders"></i> Before &amp; After Comparisons (<?php echo count($beforeAfterPhotos); ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="galleryTabContent">
        
        <!-- Standard Photo Grid Pane -->
        <div class="tab-pane fade show active" id="grid-pane" role="tabpanel" aria-labelledby="grid-tab">
            <!-- Search & Filter Controls -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="gallerySearchInput" class="form-control form-control-heritage" placeholder="Filter by title, year, or location..." value="<?php echo e($_GET['q'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="galleryCategorySelect" class="form-select form-select-heritage">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo e($cat['name']); ?>"><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <a href="<?php echo base_url('user/upload_photo.php'); ?>" class="btn btn-heritage-primary w-100"><i class="bi bi-upload"></i> Add Photo</a>
                </div>
            </div>

            <!-- Gallery Cards Grid -->
            <div class="row g-4" id="galleryGrid">
                <?php foreach ($photos as $photo): ?>
                    <div class="col-md-4 gallery-card-item" data-title="<?php echo e($photo['title']); ?>" data-desc="<?php echo e($photo['description']); ?>" data-category="<?php echo e($photo['category_name']); ?>">
                        <div class="card-heritage h-100">
                            <div class="card-img-wrapper">
                                <img src="<?php echo base_url('uploads/photos/' . e($photo['image'])); ?>" alt="<?php echo e($photo['title']); ?>">
                                <span class="badge-year"><?php echo e($photo['year']); ?></span>
                                <?php if ($photo['category_name']): ?>
                                    <span class="badge-category"><?php echo e($photo['category_name']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body-heritage">
                                <h5 class="card-title-heritage"><?php echo e($photo['title']); ?></h5>
                                <p class="card-text-muted mb-3"><?php echo e(mb_strimwidth($photo['description'], 0, 100, '...')); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-secondary">
                                    <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo e($photo['location']); ?></span>
                                    <a href="<?php echo base_url('photo-detail.php?id=' . $photo['id']); ?>" class="btn btn-sm btn-heritage-outline">View &amp; Discuss</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Before & After Comparison Slider Pane -->
        <div class="tab-pane fade" id="ba-pane" role="tabpanel" aria-labelledby="ba-tab">
            <div class="text-center mb-4">
                <h3 class="h4">Then &amp; Now: Drag Slider to Compare Eras</h3>
                <p class="text-muted small">Move the vertical gold slider bar left or right to see how the neighborhood transformed.</p>
            </div>

            <div class="row g-5">
                <?php foreach ($beforeAfterPhotos as $ba): ?>
                    <div class="col-lg-12">
                        <div class="card-heritage p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h4 class="h5 mb-1"><?php echo e($ba['title']); ?></h4>
                                    <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo e($ba['location']); ?> &bull; Original Year: <strong><?php echo e($ba['year']); ?></strong> vs. Present</span>
                                </div>
                                <a href="<?php echo base_url('photo-detail.php?id=' . $ba['id']); ?>" class="btn btn-sm btn-heritage-outline">View Details</a>
                            </div>

                            <div class="before-after-wrapper">
                                <div class="ba-image-before">
                                    <img src="<?php echo base_url('uploads/photos/' . e($ba['image'])); ?>" alt="Historical View">
                                    <span class="ba-label ba-label-before">THEN (<?php echo e($ba['year']); ?>)</span>
                                </div>
                                <div class="ba-image-after">
                                    <img src="<?php echo base_url('uploads/photos/' . e($ba['current_image'])); ?>" alt="Current View">
                                    <span class="ba-label ba-label-after">NOW (2026)</span>
                                </div>
                                <div class="ba-handle">
                                    <i class="bi bi-arrows-collapse"></i>
                                </div>
                            </div>

                            <p class="text-muted mt-3 mb-0"><?php echo e($ba['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<script src="<?php echo base_url('assets/js/gallery.js'); ?>"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
