<?php
// places.php
$pageTitle = 'Historical Neighborhood Landmarks';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$places = $pdo->query("SELECT p.*, c.name as category_name FROM historical_places p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.established_year ASC")->fetchAll();
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <span class="hero-badge"><i class="bi bi-geo-alt"></i> GEOGRAPHIC ARCHIVE</span>
        <h1 class="display-4 font-weight-bold mb-3">Historical Places &amp; Landmarks</h1>
        <p class="text-muted lead max-w-2xl mx-auto">
            Explore famous buildings, schools, markets, stations, and public parks. Click markers on the map below or inspect individual landmarks to see Then vs. Now Before &amp; After sliders.
        </p>
    </div>

    <!-- Leaflet Map Container -->
    <div class="card-heritage p-3 mb-5">
        <div id="mapContainer"></div>
    </div>

    <!-- Landmark Cards Grid -->
    <h2 class="h3 font-weight-bold mb-4"><i class="bi bi-building"></i> Directory of Historical Locations</h2>

    <div class="row g-4">
        <?php foreach ($places as $place): ?>
            <div class="col-lg-6">
                <div class="card-heritage p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold">Est. <?php echo e($place['established_year']); ?></span>
                            <h3 class="h4 text-white mt-1 mb-1"><?php echo e($place['name']); ?></h3>
                            <div class="text-muted small"><i class="bi bi-geo-alt-fill text-danger"></i> <?php echo e($place['address']); ?></div>
                        </div>
                        <a href="<?php echo base_url('place-detail.php?id=' . $place['id']); ?>" class="btn btn-heritage-outline btn-sm">Full Landmark Story &rarr;</a>
                    </div>

                    <p class="text-muted mb-4"><?php echo e(mb_strimwidth($place['description'], 0, 160, '...')); ?></p>

                    <!-- Embedded Before/After Slider if both images present -->
                    <?php if (!empty($place['old_image']) && !empty($place['current_image'])): ?>
                        <div class="before-after-wrapper mb-2" style="height: 280px;">
                            <div class="ba-image-before">
                                <img src="<?php echo base_url('uploads/places/' . e($place['old_image'])); ?>" alt="<?php echo e($place['name']); ?> Old">
                                <span class="ba-label ba-label-before">THEN (<?php echo e($place['established_year']); ?>)</span>
                            </div>
                            <div class="ba-image-after">
                                <img src="<?php echo base_url('uploads/places/' . e($place['current_image'])); ?>" alt="<?php echo e($place['name']); ?> Present">
                                <span class="ba-label ba-label-after">NOW (2026)</span>
                            </div>
                            <div class="ba-handle">
                                <i class="bi bi-arrows-collapse"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="<?php echo base_url('assets/js/map.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const placesData = <?php echo json_encode($places); ?>;
    initHeritageMap(placesData);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
