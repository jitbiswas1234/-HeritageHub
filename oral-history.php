<?php
// oral-history.php
$pageTitle = 'Oral History Audio Library';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT o.*, u.name as uploader_name FROM oral_histories o JOIN users u ON o.user_id = u.id WHERE o.status = 'approved' ORDER BY o.created_at DESC");
$audios = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <span class="hero-badge"><i class="bi bi-mic"></i> VOICES OF THE PAST</span>
        <h1 class="display-4 font-weight-bold mb-3">Oral History Archive</h1>
        <p class="text-muted lead max-w-2xl mx-auto">
            Listen to first-person audio accounts from long-time residents sharing memories of childhood, neighborhood transformations, forgotten traditions, and personal stories.
        </p>
        <a href="<?php echo base_url('user/upload_audio.php'); ?>" class="btn btn-heritage-primary mt-2"><i class="bi bi-cloud-arrow-up"></i> Submit an Audio Recording</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php foreach ($audios as $audio): ?>
                <div class="audio-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="speaker-avatar">
                            🎙️
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold"><?php echo e($audio['year']); ?> Era</span>
                            <h3 class="h4 text-white mb-1 mt-1"><?php echo e($audio['title']); ?></h3>
                            <div class="text-muted small">
                                Speaker: <strong class="text-info"><?php echo e($audio['speaker_name']); ?></strong> &bull; Recorded on <?php echo formatDate($audio['recording_date']); ?>
                            </div>
                        </div>
                    </div>

                    <p class="text-light fs-6 mb-3"><?php echo e($audio['description']); ?></p>

                    <!-- HTML5 Audio Player -->
                    <div class="p-3 bg-secondary bg-opacity-20 rounded-3 border border-secondary mb-2">
                        <audio controls class="audio-custom-player">
                            <source src="<?php echo base_url('uploads/audio/' . e($audio['audio_file'])); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>

                    <?php if (!empty($audio['transcript'])): ?>
                        <button type="button" class="btn-transcript-toggle" data-target="transcript-<?php echo $audio['id']; ?>">
                            📜 View Interview Transcript
                        </button>
                        
                        <div id="transcript-<?php echo $audio['id']; ?>" class="transcript-box">
                            <strong>Official Interview Transcript:</strong>
                            <p class="mb-0 mt-2"><?php echo nl2br(e($audio['transcript'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
