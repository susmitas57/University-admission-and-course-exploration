<?php
session_start();
require_once __DIR__ . '/includes/connection.php';

// Redirect logged-in users to their dashboards
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';

    if ($role === 'student') {
        header("Location: student/student_dashboard.php");
        exit;
    } elseif ($role === 'admin') {
        header("Location: admin/admin_dashboard.php");
        exit;
    }
}

// Fetch featured courses (latest 3 active courses)
$featured_courses = $pdo->query("
    SELECT 
        c.id,
        c.title,
        c.category,
        c.code,
        c.description,
        c.duration,
        c.credits,
        u.name AS university_name
    FROM courses c
    LEFT JOIN universities u ON c.university_id = u.id
    WHERE c.status = 'active'
    ORDER BY c.id DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch featured colleges (latest 3 approved colleges)
$featured_colleges = $pdo->query("
    SELECT id, name, location
    FROM colleges
    WHERE is_approved = 1
    ORDER BY id DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

// Include header
include_once __DIR__ . '/includes/partials/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="resources/css/index.css">
    <title>GradFinder | Find Your Perfect Path</title>
</head>
<body>

<header class="hero-section">
    <div class="hero-overlay">
        <h1>Find Your Perfect <span>Academic Path</span></h1>
        <p>Discover courses, compare colleges, and track your application journey in one place.</p>
    </div>
</header>

<main class="main-container">

    <!-- FEATURED COURSES -->
    <section id="featured-courses" class="section-block">
        <div class="section-header">
            <h2><i class="fa-solid fa-graduation-cap"></i> Featured Courses</h2>
        </div>

        <div class="card-grid">
            <?php if ($featured_courses): ?>
                <?php foreach ($featured_courses as $course): ?>
                    <div class="program-card">
                        <div class="card-badge">
                            <?= htmlspecialchars($course['category']) ?>
                        </div>

                        <h3><?= htmlspecialchars($course['title']) ?></h3>

                        <p class="uni-name">
                            <i class="fa-solid fa-university"></i>
                            <?= htmlspecialchars($course['university_name'] ?? 'N/A') ?>
                        </p>

                        <div class="card-specs">
                            <span>
                                <i class="fa-regular fa-clock"></i>
                                <?= htmlspecialchars($course['duration'] ?? 'N/A') ?>
                            </span>
                            <span>
                                <i class="fa-solid fa-award"></i>
                                <?= htmlspecialchars($course['credits'] ?? 'N/A') ?> Credits
                            </span>
                        </div>

                    
                        <a href="users_course_details.php?id=<?= $course['id'] ?>" class="btn-card">
                            View Program
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No featured courses available.</div>
            <?php endif; ?>
        </div>

        <div class="center-btn">
            <a href="explore_courses.php" class="btn-browse">
                Explore All Courses <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- FEATURED COLLEGES -->
    <section class="section-block bg-light">
        <div class="section-header">
            <h2><i class="fa-solid fa-building-columns"></i> Colleges</h2>
        </div>

        <div class="card-grid">
            <?php if ($featured_colleges): ?>
                <?php foreach ($featured_colleges as $college): ?>
                    <div class="college-card">
                        <div class="college-icon">
                            <i class="fa-solid fa-school"></i>
                        </div>

                        <h3><?= htmlspecialchars($college['name']) ?></h3>

                        <p class="location">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= htmlspecialchars($college['location']) ?>
                        </p>

                    
                        <a href="users_college_details.php?id=<?= $college['id'] ?>"
                           class="btn-card-outline">
                            View College Profile
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No colleges listed yet.</div>
            <?php endif; ?>
        </div>

        <div class="center-btn">
            <a href="user_discover_colleges.php" class="btn-browse">
                Discover More Colleges <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

</main>

<?php include_once __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>
