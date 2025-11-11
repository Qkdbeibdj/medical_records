<?php
session_start();
include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { echo "<li>User not logged in.</li>"; exit(); }

$activities_per_page = 4;
$page = max(1, intval($_GET['page'] ?? 1));
$view = $_GET['view'] ?? 'paginated';
$offset = ($page - 1) * $activities_per_page;

// Build WHERE condition dynamically
$today_filter_st = ($view === 'all') ? "" : "WHERE DATE(st.created_at) = CURDATE()";
$today_filter_sn = ($view === 'all') ? "" : "WHERE DATE(sn.created_at) = CURDATE()";
$today_filter_mc = ($view === 'all') ? "" : "WHERE DATE(mc.issued_date) = CURDATE()";

// Base query
$base_sql = "
    SELECT st.created_at, CONCAT('📝 ', s.name, ' completed ', mt.test_name) AS description
    FROM student_tests st
    JOIN students s ON st.student_id = s.student_id
    JOIN medical_tests mt ON st.test_id = mt.test_id
    $today_filter_st

    UNION ALL

    SELECT sn.created_at, CONCAT('📢 Notified ', s.name, ' for ', sn.test_type) AS description
    FROM student_notifications sn
    JOIN students s ON sn.student_id = s.student_id
    $today_filter_sn

    UNION ALL

    SELECT mc.issued_date AS created_at, CONCAT('📁 Uploaded certificate for ', s.name) AS description
    FROM medical_certificate mc
    JOIN students s ON mc.student_id = s.student_id
    $today_filter_mc
";

// Count total activities
$total_activities = $conn->query("SELECT COUNT(*) AS total FROM ($base_sql) AS combined")->fetch_assoc()['total'];

// Add ordering and pagination if needed
$sql = "SELECT * FROM ($base_sql) AS combined ORDER BY created_at DESC";
if ($view !== 'all') {
    $sql .= " LIMIT $activities_per_page OFFSET $offset";
}

$result = $conn->query($sql);

// Render activities
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['description']) . 
             " <em>(" . date('M d, Y H:i', strtotime($row['created_at'])) . ")</em></li>";
    }
} else {
    echo ($view === 'all') 
        ? "<li>No activities recorded.</li>" 
        : "<li>No activities recorded today.</li>";
}

// Pagination (only for paginated view)
if ($view !== 'all' && $total_activities > $activities_per_page) {
    $total_pages = ceil($total_activities / $activities_per_page);
    echo '<div class="pagination">';
    if ($page > 1) echo '<a href="#" class="page-link" data-page="'.($page-1).'">&laquo; Previous</a>';
    for ($i = 1; $i <= $total_pages; $i++) {
        echo '<a href="#" class="page-link'.($i === $page ? ' active' : '').'" data-page="'.$i.'">'.$i.'</a>';
    }
    if ($page < $total_pages) echo '<a href="#" class="page-link" data-page="'.($page+1).'">Next &raquo;</a>';
    echo '</div>';
}
?>
