<?php
session_start();
require_once __DIR__ . '/includes/connection.php';

$college_id = $_GET['id'] ?? null;
if (!$college_id) {
    header("Location: explore_courses.php");
    exit;
}

// Fetch college info
$stmt = $pdo->prepare("SELECT * FROM colleges WHERE id = :id AND is_approved = 1");
$stmt->execute([':id' => $college_id]);
$college = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$college) {
    echo "<h2>College not found.</h2>";
    exit;
}

// Fetch courses offered
$stmt = $pdo->prepare("
    SELECT c.id AS course_id, c.title, c.category, c.application_deadline
    FROM college_courses cc
    JOIN courses c ON cc.course_id = c.id
    WHERE cc.college_id = :college_id AND cc.status = 'active' AND c.status = 'active'
    ORDER BY c.title ASC
");
$stmt->execute([':college_id' => $college_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/includes/partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($college['name']) ?> | GradFinder</title>
    <link rel="stylesheet" href="resources/css/users/users_college_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<main class="college-container">
    <div class="college-layout">
        
        <div class="main-info">
            <header class="college-header">
                <div class="breadcrumb">
                    <a href="explore_courses.php"><i class="fa-solid fa-house"></i> Explore</a> / <span>College Details</span>
                </div>
                <h1><?= htmlspecialchars($college['name']) ?></h1>
                <p class="location-tag"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($college['location']) ?></p>
            </header>

            <section class="about-card">
                <h3><i class="fa-solid fa-circle-info"></i> About the Institution</h3>
                <p><?= nl2br(htmlspecialchars($college['description'])) ?></p>
                
                <?php if (!empty($college['website'])): ?>
                    <a href="<?= htmlspecialchars($college['website']) ?>" target="_blank" class="btn-outline-amber">
                        <i class="fa-solid fa-globe"></i> Visit Official Website
                    </a>
                <?php endif; ?>
            </section>

            <div class="action-footer">
                <button onclick="history.back()" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Return to Program Details
                </button>
            </div>
        </div>

        <aside class="course-sidebar">
            <div class="sidebar-header">
                <h3>Available Courses</h3>
                <span class="badge-count"><?= count($courses) ?></span>
            </div>
            
            <div class="sidebar-scroll">
                <?php if ($courses): ?>
                    <?php foreach ($courses as $c): ?>
                        <a href="users_course_details.php?id=<?= $c['course_id'] ?>" class="mini-course-card">
                            <div class="card-content">
                                <h4><?= htmlspecialchars($c['title']) ?></h4>
                                <span class="cat-text"><?= htmlspecialchars($c['category']) ?></span>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-text">No courses currently listed.</p>
                <?php endif; ?>
            </div>
        </aside>

    </div>
</main>

<?php include_once __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>