<?php
session_start();
require_once __DIR__ . '/includes/connection.php';

$searchTerm = trim($_GET['q'] ?? '');
$searchWildcard = "%$searchTerm%";

$courses = [];
$college_courses = [];

if ($searchTerm) {
    // --- Search Active Courses (Left Side) ---
    $stmt = $pdo->prepare("
        SELECT c.id, c.title, c.code, c.category, c.duration, c.credits, 
               u.name AS university_name
        FROM courses c
        LEFT JOIN universities u ON c.university_id = u.id
        WHERE c.status = 'active'
          AND (c.title LIKE :q1 OR c.code LIKE :q2 OR c.category LIKE :q3)
        ORDER BY c.id DESC
    ");
    $stmt->execute(['q1' => $searchWildcard, 'q2' => $searchWildcard, 'q3' => $searchWildcard]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Search College-Specific Programs (Right Side) ---
    $stmt = $pdo->prepare("
        SELECT cc.id, cc.college_id, cc.course_id, cc.fee, 
               COALESCE(cc.duration, c.duration) AS duration,
               c.title AS course_title, c.category AS course_category,
               col.name AS college_name
        FROM college_courses cc
        LEFT JOIN courses c ON cc.course_id = c.id
        LEFT JOIN colleges col ON cc.college_id = col.id
        WHERE cc.status = 'active'
          AND (c.title LIKE :q4 OR c.code LIKE :q5 OR col.name LIKE :q6)
        ORDER BY cc.id DESC
    ");
    $stmt->execute(['q4' => $searchWildcard, 'q5' => $searchWildcard, 'q6' => $searchWildcard]);
    $college_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include_once __DIR__ . '/includes/partials/header.php';
?>

<link rel="stylesheet" href="resources/css/users/users_search_browse.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<main class="search-container">
    <header class="search-results-header">
        <h1><i class="fa-solid fa-magnifying-glass"></i> Search Results</h1>
        <p>Showing findings for: <span>"<?= htmlspecialchars($searchTerm) ?>"</span></p>
        <div class="header-accent"></div>
    </header>

    <div class="dual-search-layout">
        
        <section class="results-column">
            <div class="column-title">
                <i class="fa-solid fa-graduation-cap"></i>
                <h2>Degree Programs</h2>
            </div>
            
            <div class="results-list">
                <?php if ($courses): foreach ($courses as $course): ?>
                    <div class="search-item-card">
                        <div class="card-body">
                            <span class="category-tag"><?= htmlspecialchars($course['category']) ?></span>
                            <h3><?= htmlspecialchars($course['title']) ?></h3>
                            <p class="sub-text"><i class="fa-solid fa-building-columns"></i> <?= htmlspecialchars($course['university_name']) ?></p>
                        </div>
                        <div class="card-action">
                            <span class="duration-text"><i class="fa-regular fa-calendar"></i> <?= $course['duration'] ?></span>
                            <a href="users_course_details.php?id=<?= $course['id'] ?>" class="btn-view">Explore Program</a>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="empty-notif">No general degree programs found.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="results-column">
            <div class="column-title">
                <i class="fa-solid fa-building-user"></i>
                <h2>College-Specific Seats</h2>
            </div>

            <div class="results-list">
                <?php if ($college_courses): foreach ($college_courses as $cc): ?>
                    <div class="search-item-card seat-card">
                        <div class="card-body">
                            <span class="category-tag amber"><?= htmlspecialchars($cc['course_category']) ?></span>
                            <h3><?= htmlspecialchars($cc['course_title']) ?></h3>
                            <p class="sub-text"><i class="fa-solid fa-school"></i> <?= htmlspecialchars($cc['college_name']) ?></p>
                        </div>
                        <div class="card-action">
                            <span class="fee-text">₹<?= number_format($cc['fee']) ?>/yr</span>
                            <a href="users_course_details.php?id=<?= $cc['course_id'] ?>" class="btn-emerald">View Seat</a>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="empty-notif">No specific college seats found.</div>
                <?php endif; ?>
            </div>
        </section>

    </div>
</main>

<?php include_once __DIR__ . '/includes/partials/footer.php'; ?>