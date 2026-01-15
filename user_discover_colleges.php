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

// Fetch all approved colleges
$colleges = $pdo->query("
    SELECT c.id, c.name, c.location, c.website, u.name AS university_name
    FROM colleges c
    LEFT JOIN universities u ON c.university_id = u.id
    WHERE c.is_approved = 1
    ORDER BY c.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Include header
include_once __DIR__ . '/includes/partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Discover Colleges | GradFinder</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="resources/css/index.css">
<style>
    .card-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap: 20px; }
    .college-card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); text-align:center; }
    .college-card h3 { margin:10px 0; font-size:1.2rem; }
    .college-card .location { color:#555; margin-bottom:10px; }
    .btn-card-outline { display:inline-block; padding:8px 12px; border:1px solid #007BFF; color:#007BFF; border-radius:5px; text-decoration:none; transition:0.3s; }
    .btn-card-outline:hover { background:#007BFF; color:#fff; }
</style>
</head>
<body>

<main class="main-container">
    <section class="section-block">
        <div class="section-header">
            <h1><i class="fa-solid fa-building-columns"></i> Discover Colleges</h1>
            <p>Browse all approved colleges registered on GradFinder.</p>
        </div>

        <div class="card-grid">
            <?php if ($colleges): ?>
                <?php foreach ($colleges as $college): ?>
                    <div class="college-card">
                        <div class="college-icon"><i class="fa-solid fa-school fa-2x"></i></div>
                        <h3><?= htmlspecialchars($college['name']) ?></h3>
                        <p class="location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($college['location'] ?? 'N/A') ?></p>
                        <p class="uni-name"><i class="fa-solid fa-university"></i> <?= htmlspecialchars($college['university_name'] ?? 'N/A') ?></p>
                        <?php if (!empty($college['website'])): ?>
                            <p><a href="<?= htmlspecialchars($college['website']) ?>" target="_blank">Visit Website</a></p>
                        <?php endif; ?>
                        <a href="users_college_details.php?id=<?= $college['id'] ?>" class="btn-card-outline">View College Profile</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No colleges are approved yet.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include_once __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>
