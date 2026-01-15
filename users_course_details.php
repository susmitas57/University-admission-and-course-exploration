<?php
session_start();
require_once __DIR__ . '/includes/connection.php';

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header("Location: explore_courses.php");
    exit;
}

// Fetch course info with University name
$stmt = $pdo->prepare("
    SELECT c.*, u.name as university_name 
    FROM courses c 
    LEFT JOIN universities u ON c.university_id = u.id 
    WHERE c.id = :id AND c.status='active'
");
$stmt->execute([':id' => $course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: explore_courses.php");
    exit;
}

// Fetch colleges offering this course
$stmt = $pdo->prepare("
    SELECT 
        cc.id AS college_course_id,
        col.id AS college_id,
        col.name AS college_name,
        col.location,
        cc.fee,
        cc.seats,
        cc.mode
    FROM college_courses cc
    JOIN colleges col ON cc.college_id = col.id
    WHERE cc.course_id = :course_id
      AND cc.status = 'active'
      AND col.is_approved = 1
");
$stmt->execute([':course_id' => $course_id]);
$colleges = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/includes/partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($course['title']) ?> | Details</title>
    <link rel="stylesheet" href="resources/css/users/users_course_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<main class="split-view-container">
    <div class="split-wrapper">
        
        <section class="pane course-pane">
            <div class="pane-content">
                <a href="explore_courses.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Exploration</a>
                
                <div class="detail-header">
                    <span class="category-chip"><?= htmlspecialchars($course['category']) ?></span>
                    <h1><?= htmlspecialchars($course['title']) ?></h1>
                    <p class="uni-name"><?= htmlspecialchars($course['university_name'] ?? 'University Partner') ?></p>
                </div>

                <div class="quick-metrics">
                    <div class="metric"><i class="fa-regular fa-calendar"></i> <?= $course['duration'] ?></div>
                    <div class="metric"><i class="fa-solid fa-award"></i> <?= $course['credits'] ?> Credits</div>
                    <div class="metric deadline-alert"><i class="fa-solid fa-clock"></i> <?= $course['application_deadline'] ? date('M d, Y', strtotime($course['application_deadline'])) : 'Open' ?></div>
                </div>

                <div class="description-text">
                    <h3>Program Description</h3>
                    <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                </div>
            </div>
        </section>

        <section class="pane college-pane">
            <div class="pane-header">
                <h2>Institutions Offering This Program</h2>
                <div class="amber-divider"></div>
            </div>

            <div class="simple-square-grid">
                <?php if ($colleges): ?>
                    <?php foreach ($colleges as $col): ?>
                        <div class="simple-square">
                            <div class="square-top">
                                <h4><?= htmlspecialchars($col['college_name']) ?></h4>
                                <p><i class="fa-solid fa-location-dot"></i> <?= $col['location'] ?></p>
                            </div>
                            <div class="square-mid">
                                <div class="fee-label">Annual Fee</div>
                                <div class="fee-value">₹<?= number_format($col['fee']) ?></div>
                            </div>
                            <div class="square-bottom">
                                <a href="users_college_details.php?id=<?= $col['college_id'] ?>" class="btn-emerald-xs">View College</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-box">No institutions found for this course.</div>
                <?php endif; ?>
            </div>
        </section>

    </div>
</main>

<?php include_once __DIR__ . '/includes/partials/footer.php'; ?>

<?php include_once __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>