<?php
// index.php
$pageTitle = 'Discover Our Neighborhood History';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'photos' => $pdo->query("SELECT COUNT(*) FROM historical_photos WHERE status = 'approved'")->fetchColumn(),
    'stories' => $pdo->query("SELECT COUNT(*) FROM stories WHERE status = 'approved'")->fetchColumn(),
    'audio' => $pdo->query("SELECT COUNT(*) FROM oral_histories WHERE status = 'approved'")->fetchColumn(),
    'timeline' => $pdo->query("SELECT COUNT(*) FROM timeline_events")->fetchColumn(),
    'places' => $pdo->query("SELECT COUNT(*) FROM historical_places")->fetchColumn(),
];

$memoryOfMonth = $pdo->query("SELECT s.*, u.name as author_name, u.profile_image FROM stories s JOIN users u ON s.user_id = u.id WHERE s.is_memory_of_month = 1 AND s.status = 'approved' LIMIT 1")->fetch();
$featuredPhotos = $pdo->query("SELECT p.*, c.name as category_name FROM historical_photos p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved' ORDER BY p.views DESC, p.created_at DESC LIMIT 6")->fetchAll();
$timelineEvents = $pdo->query("SELECT * FROM timeline_events ORDER BY event_year ASC LIMIT 5")->fetchAll();
$places = $pdo->query("SELECT * FROM historical_places ORDER BY established_year ASC")->fetchAll();
$latestStories = $pdo->query("SELECT s.*, u.name as author_name FROM stories s JOIN users u ON s.user_id = u.id WHERE s.status = 'approved' ORDER BY s.created_at DESC LIMIT 3")->fetchAll();
?>

<!-- Hero Section with Soft Warm Gradient -->
<section class="hero-section" style="background: linear-gradient(135deg, #fef3c7 0%, #ffffff 50%, #f8fafc 100%);">
    <div class="container py-3 py-md-5">
        <div class="hero-badge shadow-sm mb-3">
            <i class="bi bi-clock-history"></i> DIGITAL NEIGHBORHOOD ARCHIVE
        </div>
        <h1 class="hero-title mb-3">
            Discover Our Neighborhood's<br>
            <span style="background: linear-gradient(135deg, #b45309, #d97706); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Living History</span>
        </h1>
        <p class="hero-subtitle mb-4">
            "Every street has a story, every brick has a memory." Digitally preserving old photographs, personal memoirs, oral histories, and historical landmarks for future generations.
        </p>

        <!-- Search Bar -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-7 col-md-9">
                <form action="<?php echo base_url('gallery.php'); ?>" method="GET" class="shadow-sm rounded-3 p-1 bg-white border border-light">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-search text-warning fs-4"></i></span>
                        <input type="text" name="q" class="form-control border-0 shadow-none ps-0" placeholder="Search places, events (e.g. 'Market', '1955', 'Tram')...">
                        <button type="submit" class="btn btn-heritage-primary px-4 rounded-3"><i class="bi bi-search me-1"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo base_url('timeline.php'); ?>" class="btn btn-heritage-primary btn-lg shadow-sm"><i class="bi bi-hourglass-split me-1"></i> Explore Timeline</a>
            <a href="<?php echo base_url('user/submit_story.php'); ?>" class="btn btn-heritage-outline btn-lg shadow-sm"><i class="bi bi-heart me-1"></i> Share Your Memory</a>
        </div>
    </div>
</section>

<!-- Live Impact Stats Section -->
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['photos']; ?></div>
                    <div class="stat-label">Photos</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['stories']; ?></div>
                    <div class="stat-label">Stories</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['audio']; ?></div>
                    <div class="stat-label">Audio Interviews</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['timeline']; ?></div>
                    <div class="stat-label">Milestones</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['places']; ?></div>
                    <div class="stat-label">Landmarks</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['users']; ?></div>
                    <div class="stat-label">Contributors</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Memory of the Month Spotlight -->
<?php if ($memoryOfMonth): ?>
<section class="py-5" style="background-color: #f8fafc;">
    <div class="container">
        <div class="memory-banner border border-warning-subtle shadow-sm bg-white p-4 p-md-5 rounded-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="memory-badge mb-3"><i class="bi bi-star-fill"></i> MEMORY OF THE MONTH</span>
                    <h2 class="mb-3 display-6 fw-bold text-dark"><?php echo e($memoryOfMonth['title']); ?></h2>
                    <p class="text-secondary leading-relaxed fs-5 mb-4">
                        "<?php echo e(mb_strimwidth($memoryOfMonth['story'], 0, 240, '...')); ?>"
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?php echo base_url('uploads/profiles/' . e($memoryOfMonth['profile_image'] ?? 'user_avatar.svg')); ?>" class="rounded-circle border border-warning" style="width: 46px; height: 46px; object-fit: cover;" alt="Author">
                        <div>
                            <div class="fw-bold text-dark fs-6"><?php echo e($memoryOfMonth['person_name'] ?: $memoryOfMonth['author_name']); ?></div>
                            <div class="text-muted small">Year: <?php echo e($memoryOfMonth['year']); ?> &bull; <?php echo e($memoryOfMonth['location']); ?></div>
                        </div>
                        <a href="<?php echo base_url('story-detail.php?id=' . $memoryOfMonth['id']); ?>" class="btn btn-heritage-primary ms-auto"><i class="bi bi-book me-1"></i> Read Full Story</a>
                    </div>
                </div>
                <?php if ($memoryOfMonth['image']): ?>
                <div class="col-lg-5">
                    <div class="overflow-hidden rounded-4 shadow border border-light">
                        <img src="<?php echo base_url('uploads/photos/' . e($memoryOfMonth['image'])); ?>" class="img-fluid w-100" style="max-height: 340px; object-fit: cover;" alt="<?php echo e($memoryOfMonth['title']); ?>">
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Historical Timeline Highlights Preview -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-warning font-weight-bold small text-uppercase fw-semibold" style="letter-spacing: 0.08em;">Chronological Milestones</span>
                <h2 class="h1 fw-bold text-dark mt-1 mb-0">Historical Timeline Highlights</h2>
            </div>
            <a href="<?php echo base_url('timeline.php'); ?>" class="btn btn-heritage-outline"><i class="bi bi-arrow-right-circle me-1"></i> View All Eras</a>
        </div>

        <div class="timeline-container">
            <?php foreach ($timelineEvents as $index => $event): ?>
                <div class="timeline-item" data-year="<?php echo $event['event_year']; ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-year"><?php echo $event['event_year']; ?></span>
                        <h4 class="timeline-title"><?php echo e($event['title']); ?></h4>
                        <p class="card-text-muted mb-2"><?php echo e(mb_strimwidth($event['description'], 0, 110, '...')); ?></p>
                        <?php if ($event['image']): ?>
                            <img src="<?php echo base_url('uploads/photos/' . e($event['image'])); ?>" class="timeline-img mb-3" alt="<?php echo e($event['title']); ?>">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Photographs Grid -->
<section class="py-5" style="background-color: #f8fafc;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-warning font-weight-bold small text-uppercase fw-semibold" style="letter-spacing: 0.08em;">Visual Archive</span>
                <h2 class="h1 fw-bold text-dark mt-1 mb-0">Featured Historical Photographs</h2>
            </div>
            <a href="<?php echo base_url('gallery.php'); ?>" class="btn btn-heritage-outline"><i class="bi bi-grid-3x3-gap me-1"></i> Explore Gallery</a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredPhotos as $photo): ?>
                <div class="col-md-4">
                    <div class="card-heritage h-100 shadow-sm">
                        <div class="card-img-wrapper">
                            <img src="<?php echo base_url('uploads/photos/' . e($photo['image'])); ?>" alt="<?php echo e($photo['title']); ?>">
                            <span class="badge-year"><?php echo e($photo['year']); ?></span>
                            <?php if ($photo['category_name']): ?>
                                <span class="badge-category"><?php echo e($photo['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body-heritage">
                            <h5 class="card-title-heritage"><?php echo e($photo['title']); ?></h5>
                            <p class="card-text-muted mb-3"><?php echo e(mb_strimwidth($photo['description'], 0, 90, '...')); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                                <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo e($photo['location']); ?></span>
                                <a href="<?php echo base_url('photo-detail.php?id=' . $photo['id']); ?>" class="btn btn-sm btn-heritage-outline"><i class="bi bi-eye me-1"></i> Inspect</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Interactive Landmark Map Preview Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <span class="text-warning font-weight-bold small text-uppercase fw-semibold" style="letter-spacing: 0.08em;">Geographic Heritage</span>
                <h2 class="h1 fw-bold text-dark mt-1 mb-2">Explore Neighborhood Landmarks</h2>
                <p class="text-muted fs-6 mb-0">Click markers on the interactive map below to discover original establishing dates, photos, and Then vs. Now Before &amp; After sliders.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?php echo base_url('places.php'); ?>" class="btn btn-heritage-primary btn-lg shadow-sm"><i class="bi bi-map me-1"></i> Full Interactive Map</a>
            </div>
        </div>

        <div id="mapContainer" class="shadow-sm"></div>
    </div>
</section>

<!-- Latest Community Stories -->
<section class="py-5" style="background-color: #f8fafc;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-warning font-weight-bold small text-uppercase fw-semibold" style="letter-spacing: 0.08em;">Personal Memories</span>
                <h2 class="h1 fw-bold text-dark mt-1 mb-0">Latest Community Stories</h2>
            </div>
            <a href="<?php echo base_url('stories.php'); ?>" class="btn btn-heritage-outline"><i class="bi bi-arrow-right-circle me-1"></i> View All Stories</a>
        </div>

        <div class="row g-4">
            <?php foreach ($latestStories as $story): ?>
                <div class="col-md-4">
                    <div class="card-heritage p-4 h-100 shadow-sm">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-warning text-white font-weight-bold px-2 py-1"><?php echo e($story['year']); ?> Era</span>
                            <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo e($story['location']); ?></span>
                        </div>
                        <h4 class="h5 text-dark fw-bold mb-3"><?php echo e($story['title']); ?></h4>
                        <p class="text-secondary small mb-4 flex-grow-1">
                            "<?php echo e(mb_strimwidth($story['story'], 0, 150, '...')); ?>"
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
                            <span class="text-dark small fw-semibold"><i class="bi bi-person me-1"></i> <?php echo e($story['person_name'] ?: $story['author_name']); ?></span>
                            <a href="<?php echo base_url('story-detail.php?id=' . $story['id']); ?>" class="text-warning small font-weight-bold">Read Story &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 text-center bg-white border-top">
    <div class="container py-3">
        <h2 class="display-6 font-weight-bold text-dark mb-3">Have an Old Photo or Story to Share?</h2>
        <p class="text-muted lead max-w-2xl mx-auto mb-4">
            Join hundreds of local residents preserving neighborhood heritage. Your family memories help complete our shared community time capsule.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo base_url('user/upload_photo.php'); ?>" class="btn btn-heritage-primary btn-lg shadow-sm"><i class="bi bi-upload me-1"></i> Upload Old Photograph</a>
            <a href="<?php echo base_url('user/submit_story.php'); ?>" class="btn btn-heritage-outline btn-lg shadow-sm"><i class="bi bi-pen me-1"></i> Submit Story</a>
        </div>
    </div>
</section>

<script src="<?php echo base_url('assets/js/map.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const placesData = <?php echo json_encode($places); ?>;
    initHeritageMap(placesData);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
