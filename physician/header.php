<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/db_connect.php';

// ✅ Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// ✅ Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "⚠️ User not found.";
    exit();
}

// ✅ Detect current page for active highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="main-header">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <div class="logo-nav-container">

        <!-- ✅ Logo -->
        <div class="logo-section">
            <img src="../images/logo/NOBG.png" alt="Logo" class="logo">
            <div class="college-info">
                <h2>PHILIPPINE COLLEGE OF</h2>
                <h3>SCIENCE AND TECHNOLOGY</h3>
                <p>CALASIAO, PANGASINAN, PHILIPPINES 2418</p>
            </div>
        </div>

        <!-- ✅ Desktop Navigation -->
        <nav class="nav-links desktop-nav">
            <ul>
                <li><a href="homepage.php" class="<?= $current_page === 'homepage.php' ? 'active' : '' ?>"><i class="bi bi-house-door"></i> Home</a></li>
                <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

                <?php if ($current_page === 'dashboard.php'): ?>
                    <li><a href="#" onclick="openSettingsModal()"><i class="bi bi-gear"></i> Settings</a></li>
                <?php endif; ?>

                <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- ✅ Mobile Dropdown Menu -->
        <div class="mobile-menu">
            <button class="dropdown-btn"><i class="bi bi-list"></i> Menu</button>
            <ul class="dropdown-content">
                <li><a href="homepage.php"><i class="bi bi-house-door"></i> Home</a></li>
                <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

                <?php if ($current_page === 'dashboard.php'): ?>
                    <li><a href="#" onclick="openSettingsModal()"><i class="bi bi-gear"></i> Settings</a></li>
                <?php endif; ?>

                <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const dropdownBtn = document.querySelector(".dropdown-btn");
    const dropdownContent = document.querySelector(".dropdown-content");

    dropdownBtn.addEventListener("click", () => {
        dropdownContent.classList.toggle("active");
    });
});
</script>

<style>
/* ---------- RESET ---------- */
* { box-sizing: border-box; margin: 0; padding: 0; }

/* ---------- HEADER STYLING ---------- */
.main-header {
    background: #ffffff;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    padding: 10px 25px;
    border-bottom: 2px solid #eee;
}

.logo-nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo {
    width: 55px;
}

.college-info h2, .college-info h3, .college-info p {
    font-size: 11px;
    line-height: 1.1;
}

/* ---------- DESKTOP NAVIGATION ---------- */
.desktop-nav ul {
    display: flex;
    list-style: none;
    gap: 25px;
}

.desktop-nav a {
    text-decoration: none;
    color: #000;
    font-weight: 600;
    font-size: 15px;
}

.desktop-nav a.active,
.desktop-nav a:hover {
    color: #3e1760;
}

/* ---------- MOBILE MENU ---------- */
.mobile-menu {
    display: none;
    position: relative;
}

.dropdown-btn {
    font-size: 17px;
    font-weight: bold;
    background: #ffffff;
    border: 1px solid #d1d1d1;
    padding: 8px 15px;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dropdown-content {
    display: none;
    flex-direction: column;
    background: #fff;
    position: absolute;
    top: 45px;
    right: 0;
    width: 200px;
    padding: 12px;
    list-style: none;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    gap: 15px;
}

.dropdown-content.active {
    display: flex;
    animation: fadeIn .25s ease-in-out;
}

.dropdown-content a {
    text-decoration: none;
    font-size: 14px;
    color: #000;
}

.dropdown-content a:hover {
    color: #3e1760;
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-7px);}
    to {opacity: 1; transform: translateY(0);}
}

/* ✅ RESPONSIVE BREAKPOINT */
@media (max-width: 1024px) {
    .desktop-nav { display: none; }
    .mobile-menu { display: block; }
}

/* ✅ Prevent overlap with content */
body { padding-top: 95px; }
</style>
