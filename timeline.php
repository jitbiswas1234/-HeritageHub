<?php
// timeline.php
$pageTitle = 'Interactive Neighborhood Timeline';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT t.*, c.name as category_name FROM timeline_events t LEFT JOIN categories c ON t.category_id = c.id ORDER BY t.event_year ASC");
$events = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <span class="hero-badge"><i class="bi bi-hourglass-split"></i> CHRONOLOGICAL HERITAGE</span>
        <h1 class="display-4 font-weight-bold mb-3">Interactive Neighborhood Timeline</h1>
        <p class="text-muted lead max-w-2xl mx-auto">
            Travel through time to explore key milestones, architectural breakthroughs, transportation expansions, and community events that shaped our neighborhood.
        </p>
    </div>

    <!-- Era Filters Bar -->
    <div class="era-filter-bar mb-5">
        <button type="button" class="era-btn active" data-era="all">All Eras (1890 - Present)</button>
        <button type="button" class="era-btn" data-era="1890-1940">1890 &ndash; 1940 (Early Era)</button>
        <button type="button" class="era-btn" data-era="1941-1970">1941 &ndash; 1970 (Mid Century)</button>
        <button type="button" class="era-btn" data-era="1971-2000">1971 &ndash; 2000 (Modern Growth)</button>
        <button type="button" class="era-btn" data-era="2001-2026">2001 &ndash; Present (Digital Age)</button>
    </div>

    <!-- Timeline Container -->
    <div class="timeline-container">
        <?php foreach ($events as $event): ?>
            <div class="timeline-item" id="event-<?php echo $event['id']; ?>" data-year="<?php echo $event['event_year']; ?>">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="timeline-year"><?php echo $event['event_year']; ?></span>
                        <?php if ($event['category_name']): ?>
                            <span class="badge bg-secondary"><?php echo e($event['category_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="timeline-title"><?php echo e($event['title']); ?></h3>
                    
                    <?php if ($event['event_date']): ?>
                        <div class="text-warning small mb-2"><i class="bi bi-calendar-event"></i> <?php echo e($event['event_date']); ?></div>
                    <?php endif; ?>

                    <p class="card-text-muted mb-3"><?php echo e($event['description']); ?></p>

                    <?php if (!empty($event['image'])): ?>
                        <img src="<?php echo base_url('uploads/photos/' . e($event['image'])); ?>" class="timeline-img mb-3" alt="<?php echo e($event['title']); ?>">
                    <?php endif; ?>

                    <?php if (!empty($event['location'])): ?>
                        <div class="text-muted small"><i class="bi bi-geo-alt-fill text-danger"></i> Location: <?php echo e($event['location']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="<?php echo base_url('assets/js/timeline.js'); ?>"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
