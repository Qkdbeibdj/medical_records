<?php
session_start();
include 'includes/db_connect.php';

$error_email = "";
$error_password = "";
$error_general = "";
$errors_register = [];

$email = "";
$password = "";
$name = "";
$student_number = "";
$course = "";
$year_level = "";

// Determine action
$action = $_GET['action'] ?? 'login';

// POST handling
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // LOGIN
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if(empty($email) && empty($password)) $error_general="Please enter email and password before logging in.";
        elseif(empty($email)) $error_email="Email is required.";
        elseif(empty($password)) $error_password="Password is required.";
        else {
            $stmt = $conn->prepare("
                SELECT u.user_id, u.name, u.role, u.password_hash,
                       s.student_id, s.status
                FROM users u
                LEFT JOIN students s ON u.user_id = s.user_id
                WHERE u.email = ?
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows>0){
                $row = $result->fetch_assoc();
                if(!password_verify($password, $row['password_hash'])){
                    $error_password = "Password is incorrect. Please try again.";
                } else {
                    $role = $row['role'];
                    if($role === 'student'){
                        if(empty($row['student_id'])){
                            $error_general = "Student record not found.";
                        } elseif($row['status'] !== 'active'){
                            $error_general = "Sorry, your student account is inactive.";
                        } else {
                            $_SESSION['student_id'] = $row['student_id'];
                            $_SESSION['name'] = $row['name'];
                            $_SESSION['role'] = $role;
                            header("Location: student/student_dashboard.php");
                            exit;
                        }
                    } elseif($role === 'physician'){
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['role'] = $role;
                        header("Location: physician/homepage.php");
                        exit;
                    } elseif($role === 'dean'){
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['role'] = $role;
                        header("Location: dean/dean_dashboard.php");
                        exit;
                    }
                }
            } else {
                $error_general = "The email and password you entered did not match our records. Please double-check and try again.";
            }
            $stmt->close();
        }
    }
    // REGISTRATION
    if (isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $student_number = trim($_POST['student_number']);
        $course = trim($_POST['course']);
        $year_level = trim($_POST['year_level']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // === Basic field validation ===
        if (empty($name)) $errors_register['name'] = "Full name is required.";
        if (empty($student_number)) $errors_register['student_number'] = "Student number is required.";
        if (empty($course)) $errors_register['course'] = "Course is required.";
        if (empty($year_level)) $errors_register['year_level'] = "Year level is required.";
        if (empty($email)) $errors_register['email'] = "Email is required.";

        // === Email format check ===
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors_register['email'] = "Please enter a valid email address.";
        }

        // === Password verification rules ===
        if (empty($password)) {
            $errors_register['password'] = "Password is required.";
        } elseif (strlen($password) < 8) {
            $errors_register['password'] = "Password must be at least 8 characters long.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors_register['password'] = "Password must contain at least one uppercase letter.";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors_register['password'] = "Password must contain at least one lowercase letter.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors_register['password'] = "Password must contain at least one number.";
        } elseif (!preg_match('/[@$!%*?&]/', $password)) {
            $errors_register['password'] = "Password must include at least one special character.";
        }

        // === Proceed if no validation errors ===
        if (empty($errors_register)) {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors_register['email'] = "Email already registered.";
            } else {
                // Hash password securely
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'student')");
                $stmt->bind_param("sss", $name, $email, $password_hash);

                if ($stmt->execute()) {
                    $user_id = $stmt->insert_id;

                    // Insert into students table
                    $stmt2 = $conn->prepare("INSERT INTO students (user_id, student_number, name, email, course, year_level, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                    $stmt2->bind_param("isssss", $user_id, $student_number, $name, $email, $course, $year_level);
                    $stmt2->execute();

                    $_SESSION['flash_success'] = "Student registered successfully! Please log in.";
                    header("Location: index.php");
                    exit;
                } else {
                    $error_general = "Something went wrong during registration: " . $stmt->error;
                }

                $stmt->close();
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $action==='register'?'Register':'Login' ?> | Medical Records</title>
<link rel="stylesheet" href="css/login.css">
<style>
.error-message-inline{
    color:red;
    font-size:0.8rem;
    position:absolute;
    right:0;
    top:-1rem;
}
#error-general{
    color:red;
    text-align:center;
    margin-bottom:10px;
}
.password-group{position:relative;}
.toggle-password{position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer;}
.toggle-password svg{width:20px;height:20px;}
.auth-link a{font-size:0.85rem;}
.scrollable-form-container{
    max-height:65vh;
    overflow-y:auto;
    padding-right:5px;
}
#success-alert{
    background:#d4edda;
    color:#155724;
    padding:10px;
    border-radius:5px;
    margin-bottom:10px;
    text-align:center;
}
</style>
</head>
<body>
<div class="auth-container">
<h2><?= $action==='register'?'Create Student Account':'Login to Medical Records' ?></h2>

<?php if(isset($_SESSION['flash_success'])): ?>
<div id="success-alert">
    <?= htmlspecialchars($_SESSION['flash_success']); ?>
</div>
<script>
setTimeout(()=>{document.getElementById('success-alert').style.display='none';},4000);
</script>
<?php unset($_SESSION['flash_success']); endif; ?>

<p id="error-general" style="<?= !empty($error_general)?'display:block;':'display:none;' ?>"><?= htmlspecialchars($error_general) ?></p>

<?php if($action==='register'): ?>
<form method="POST" novalidate>
<div class="scrollable-form-container">
<div class="form-group">
<input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required placeholder=" " id="name"/>
<label>Full Name</label>
<?php if(!empty($errors_register['name'])) echo '<span class="error-message-inline">'.$errors_register['name'].'</span>'; ?>
</div>

<div class="form-group">
<input type="text" name="student_number" value="<?= htmlspecialchars($student_number) ?>" required placeholder=" " id="student_number"/>
<label>Student Number</label>
<?php if(!empty($errors_register['student_number'])) echo '<span class="error-message-inline">'.$errors_register['student_number'].'</span>'; ?>
</div>

<div class="form-group">
<select name="course" required id="course">
<option value="">Select Course</option>
<option value="BSMT" <?= $course==='BSMT'?'selected':'' ?>>BSMT</option>
<option value="BSMarE" <?= $course==='BSMarE'?'selected':'' ?>>BSMarE</option>
</select>
<label>Course</label>
<?php if(!empty($errors_register['course'])) echo '<span class="error-message-inline">'.$errors_register['course'].'</span>'; ?>
</div>

<div class="form-group">
<select name="year_level" required id="year_level">
<option value="">Select Year Level</option>
<option value="1st Year" <?= $year_level==='1st Year'?'selected':'' ?>>1st Year</option>
<option value="2nd Year" <?= $year_level==='2nd Year'?'selected':'' ?>>2nd Year</option>
<option value="3rd Year" <?= $year_level==='3rd Year'?'selected':'' ?>>3rd Year</option>
</select>
<label>Year Level</label>
<?php if(!empty($errors_register['year_level'])) echo '<span class="error-message-inline">'.$errors_register['year_level'].'</span>'; ?>
</div>

<div class="form-group">
<input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder=" " id="email"/>
<label>Email</label>
<?php if(!empty($errors_register['email'])) echo '<span class="error-message-inline">'.$errors_register['email'].'</span>'; ?>
</div>

<div class="form-group password-group">
<input type="password" name="password" value="<?= htmlspecialchars($password) ?>" required placeholder=" " id="password-input-reg"/>
<label>Password</label>
<span class="toggle-password" id="toggle-password-reg">
<svg viewBox="0 0 24 24">
<path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>
<line x1="1" y1="23" x2="23" y2="1" stroke="gray" stroke-width="2" style="display:none"/>
</svg>
</span>
<?php if(!empty($errors_register['password'])) echo '<span class="error-message-inline">'.$errors_register['password'].'</span>'; ?>
</div>
</div>
<button type="submit" name="register">Register</button>
<p class="auth-link">Already have an account? <a href="index.php?action=login">Login here</a></p>
</form>

<?php else: ?>
<form method="POST" novalidate>
<div class="form-group">
<input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder=" " id="email-input"/>
<label>Email</label>
<?php if(!empty($error_email)) echo '<span class="error-message-inline" id="email-error">'.$error_email.'</span>'; ?>
</div>

<div class="form-group password-group">
<input type="password" name="password" value="<?= htmlspecialchars($password) ?>" required placeholder=" " id="password-input"/>
<label>Password</label>
<span class="toggle-password" id="toggle-password">
<svg viewBox="0 0 24 24">
<path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>
<line x1="1" y1="23" x2="23" y2="1" stroke="gray" stroke-width="2" style="display:none"/>
</svg>
</span>
<?php if(!empty($error_password)) echo '<span class="error-message-inline" id="password-error">'.$error_password.'</span>'; ?>
</div>

<button type="submit" name="login">Login</button>
</form>

<p class="auth-link"><a href="forgot_password.php">Forgot Password?</a></p>
<p class="auth-link">Don’t have an account? <a href="index.php?action=register">Register here</a></p>
<?php endif; ?>
</div>

<script>
// Password toggle login
const toggle = document.getElementById('toggle-password');
if(toggle){
    const input = document.getElementById('password-input');
    const line = toggle.querySelector('line');
    toggle.addEventListener('click', ()=>{
        input.type = input.type==='password'?'text':'password';
        line.style.display = input.type==='password'?'none':'block';
    });
}

// Password toggle registration
const toggleReg = document.getElementById('toggle-password-reg');
if(toggleReg){
    const inputReg = document.getElementById('password-input-reg');
    const lineReg = toggleReg.querySelector('line');
    toggleReg.addEventListener('click', ()=>{
        inputReg.type = inputReg.type==='password'?'text':'password';
        lineReg.style.display = inputReg.type==='password'?'none':'block';
    });
}

// Hide errors on typing
['email-input','password-input','password-input-reg','name','student_number','course','year_level'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('input',()=>{
        const parent=el.closest('.form-group');
        if(parent){
            const inline=parent.querySelector('.error-message-inline');
            if(inline) inline.style.display='none';
        }
        const general=document.getElementById('error-general');
        if(general) general.style.display='none';
    });
});
</script>
</body>
</html>
