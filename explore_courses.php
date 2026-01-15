<?php
session_start();
require_once __DIR__ . '/includes/connection.php';

/* --- 1. PAGINATION LOGIC --- */
$limit = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

/* --- 2. FETCH FILTER DATA --- */
$categories = $pdo->query("SELECT DISTINCT category FROM courses WHERE status='active' AND category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$universities = $pdo->query("SELECT id, name FROM universities ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

/* --- 3. BUILD FILTER QUERY --- */
$where = ["c.status = 'active'"];
$params = [];

if (!empty($_GET['category'])) {
    $where[] = "c.category = :category";
    $params[':category'] = $_GET['category'];
}
if (!empty($_GET['university'])) {
    $where[] = "c.university_id = :university";
    $params[':university'] = $_GET['university'];
}

$whereSQL = implode(' AND ', $where);

/* --- 4. COUNT TOTAL FOR PAGINATION --- */
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM courses c WHERE $whereSQL");
$countStmt->execute($params);
$total_courses = $countStmt->fetchColumn();
$total_pages = ceil($total_courses / $limit);

/* --- 5. FETCH PAGINATED COURSES --- */
$stmt = $pdo->prepare("
    SELECT c.id, c.title, c.category, c.duration, c.credits, u.name AS university_name
    FROM courses c
    LEFT JOIN universities u ON c.university_id = u.id
    WHERE $whereSQL
    ORDER BY c.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* --- 6. FETCH DEADLINES --- */
$deadlines = $pdo->query("SELECT title, application_deadline FROM courses WHERE status='active' AND application_deadline >= CURDATE() ORDER BY application_deadline ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/includes/partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Programs | GradFinder</title>
    <link rel="stylesheet" href="resources/css/users/users_explore_courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<main class="explore-container">
    <div class="main-layout">
        
        <aside class="left-sidebar">
            <div class="sidebar-box filter-card">
                <h3><i class="fa-solid fa-sliders"></i> Refine Search</h3>
                <form method="GET" action="explore_courses.php">
                    <div class="filter-item">
                        <label>Field of Study</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= ($_GET['category'] ?? '') == $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label>University</label>
                        <select name="university">
                            <option value="">All Institutions</option>
                            <?php foreach ($universities as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($_GET['university'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-search">Apply Filters</button>
                    <?php if(!empty($_GET)): ?>
                        <a href="explore_courses.php" class="clear-link">Reset All</a>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        <section class="center-content">
            <div class="center-header">
                <h2>Available Bachelor Programs</h2>
                <div class="header-underline"></div>
            </div>

            <div class="course-grid">
                <?php if($courses): ?>
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="card-top">
                            <span class="category-tag"><?= htmlspecialchars($course['category']) ?></span>
                            <span class="duration-tag"><?= htmlspecialchars($course['duration'] ?? 'N/A') ?></span>
                        </div>
                        <h4><?= htmlspecialchars($course['title']) ?></h4>
                        <p class="uni-text"><i class="fa-solid fa-building-columns"></i> <?= htmlspecialchars($course['university_name']) ?></p>
                        
                        <div class="card-footer">
                            <span class="credits-info"><strong><?= $course['credits'] ?? '0' ?></strong> Credits</span>
                            <a href="users_course_details.php?id=<?= $course['id'] ?>" class="btn-view-sm">Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">No programs found matching your criteria.</div>
                <?php endif; ?>
            </div>

            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&category=<?= urlencode($_GET['category'] ?? '') ?>&university=<?= urlencode($_GET['university'] ?? '') ?>" 
                       class="<?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </section>

       

    </div>
</main>

<?php include_once __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>