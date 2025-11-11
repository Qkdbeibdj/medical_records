<?php
// Start the session to get the current logged-in user
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ../index.php");
    exit();
}

// Include the database connection
include '../includes/db_connect.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Query to get user data from the database
$query = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = $conn->query($query);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="settings-container">
        <header>
            <h1>Settings</h1>
        </header>

        <main>
            <section class="settings-form">
                <h2>Update Your Profile</h2>

                <!-- Display any session messages for success or error -->
                <?php if (isset($_SESSION['success_message'])) { ?>
                    <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php } ?>

                <?php if (isset($_SESSION['error_message'])) { ?>
                    <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php } ?>

                <!-- Profile update form -->
                <form action="update_settings.php" method="POST">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password (leave empty to keep current)">
                    
                    <input type="submit" value="Save Changes">
                </form>
            </section>
        </main>
    </div>
</body>
</html>
