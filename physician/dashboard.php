<?php


session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

    // Prevent caching
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);


    // Check if the logged-in user is a physician
    if ($_SESSION['role'] !== 'physician') {
        header("Location: ../index.php");
        exit();
    }

    include '../includes/db_connect.php'; // Ensure database connection is included
    include 'header.php';

    $physician_name = "";
    $physician_email = "";

    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $physician_name = $user['name'];
        $physician_email = $user['email'];
    } else {
        // Handle the case when no user is found
        echo "⚠️ User not found in the database for user_id: $user_id";
        exit();
    }
    
// Fetch all medical tests
$tests_result = $conn->query("SELECT test_id, test_name FROM medical_tests");
$tests = [];
while ($test = $tests_result->fetch_assoc()) {
    $tests[] = $test;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'submit_test_data')
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Physician Dashboard</title>
        <!-- Bootstrap CSS -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

         
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&di   splay=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/physician.css">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
        <script src="../script/script.js" ></script>
        <script src="../script/physician.js" ></script>
        <!-- Put these in your <head> -->
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
        <div id="settingsModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeSettingsModal()">&times;</span>
    <h2>Update Your Profile</h2>

    <form action="update_settings.php" method="POST" onsubmit="return validatePasswordChange()">
      <!-- Name -->
      <label for="name">Name:</label>
      <input 
        type="text" 
        id="name" 
        name="name" 
        value="<?php echo htmlspecialchars($physician_name); ?>" 
        required
      >

      <!-- Email -->
      <label for="email">Email:</label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        value="<?php echo htmlspecialchars($physician_email); ?>" 
        required
      >

      <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">

      <!-- Old Password -->
      <label for="old_password">Old Password:</label>
      <div class="password-wrapper">
        <input 
          type="password" 
          id="old_password" 
          name="old_password" 
          placeholder="Enter your current password"
        >
        <span class="toggle-password" onclick="togglePassword('old_password', this)">
          <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon show" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon hide" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.04-3.368M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-2.04 3.368M3 3l18 18" />
          </svg>
        </span>
      </div>

      <!-- New Password -->
      <label for="new_password">New Password:</label>
      <div class="password-wrapper">
        <input 
          type="password" 
          id="new_password" 
          name="new_password" 
          placeholder="Enter new password"
        >
        <span class="toggle-password" onclick="togglePassword('new_password', this)">
          <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon show" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon hide" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.04-3.368M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-2.04 3.368M3 3l18 18" />
          </svg>
        </span>
      </div>

      <!-- Confirm New Password -->
      <label for="confirm_password">Confirm New Password:</label>
      <div class="password-wrapper">
        <input 
          type="password" 
          id="confirm_password" 
          name="confirm_password" 
          placeholder="Re-enter new password"
        >
        <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
          <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon show" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon hide" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.04-3.368M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-2.04 3.368M3 3l18 18" />
          </svg>
        </span>
      </div>

      <small id="passwordError" style="color: red; display: none;">Passwords do not match.</small>

      <input type="submit" value="Save Changes" class="submit-btn">
    </form>
  </div>
</div>

<script>
function validatePasswordChange() {
  const oldPass = document.getElementById("old_password").value.trim();
  const newPass = document.getElementById("new_password").value.trim();
  const confirmPass = document.getElementById("confirm_password").value.trim();
  const errorMsg = document.getElementById("passwordError");

  if (newPass !== "" || confirmPass !== "" || oldPass !== "") {
    if (!oldPass) {
      alert("Please enter your old password before setting a new one.");
      return false;
    }
    if (newPass.length < 6) {
      alert("New password must be at least 6 characters long.");
      return false;
    }
    if (newPass !== confirmPass) {
      errorMsg.style.display = "block";
      return false;
    }
  }
  errorMsg.style.display = "none";
  return true;
}

function togglePassword(id, el) {
  const input = document.getElementById(id);
  const showIcon = el.querySelector('.eye-icon.show');
  const hideIcon = el.querySelector('.eye-icon.hide');
  const isHidden = input.type === "password";

  input.type = isHidden ? "text" : "password";
  showIcon.style.display = isHidden ? "none" : "inline";
  hideIcon.style.display = isHidden ? "inline" : "none";
}
</script>

<style>
.modal-content {
  background: white;
  padding: 35px;
  border-radius: 16px;
  max-width: 600px;
  margin: 60px auto;
  box-shadow: 0 6px 25px rgba(0,0,0,0.15);
  font-family: 'Segoe UI', sans-serif;
}

@media (max-width: 640px) {
  .modal-content {
    width: 90%;
    padding: 25px;
  }
}

h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #1f2937;
  font-weight: 700;
}

label {
  display: block;
  margin-top: 14px;
  font-weight: 600;
  color: #374151;
}

input[type="text"], 
input[type="email"], 
input[type="password"] {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  margin-top: 6px;
  font-size: 15px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
}

.password-wrapper {
  position: relative;
}

.password-wrapper input {
  width: 100%;
  padding-right: 42px;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #6b7280;
  transition: color 0.2s ease;
}

.toggle-password:hover {
  color: #111827;
}

.eye-icon {
  width: 22px;
  height: 22px;
  stroke-width: 2;
}

.submit-btn {
  width: 100%;
  background-color: #1f2937;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 10px;
  margin-top: 20px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  transition: background 0.2s ease;
}

.submit-btn:hover {
  background-color: #111827;
}

.close {
  float: right;
  font-size: 28px;
  cursor: pointer;
  color: #4b5563;
  transition: color 0.2s;
}

.close:hover {
  color: #000;
}
</style>


<!-- Student and Test Selection Modal -->
<div class="modal" id="studentTestModal" tabindex="-1" aria-labelledby="studentTestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content neumorphic">
            <div class="modal-header">
                <h5 class="modal-title" id="studentTestModalLabel">Select Student and Test</h5>
                <button type="button" class="btn-close" onclick="closeStudentTestModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="testSelectionForm">

                    <!-- Enhanced Searchable Student Dropdown -->
                    <label for="studentSelect">Select Student:</label>
                    <select id="studentSelect" name="student_id" class="form-control" required onchange="loadAvailableTests()">
                        <option value="">--Select a Student--</option>
                        <?php 
                        $students_result = $conn->query("SELECT student_id, student_number, name FROM students");
                        while ($student = $students_result->fetch_assoc()) {
                            $student_name = htmlspecialchars($student['name']);
                            $student_number = htmlspecialchars($student['student_number']);
                            echo "<option value='{$student['student_id']}'>{$student_name} - {$student_number}</option>";
                        }
                        ?>
                    </select>

                    <!-- Test Selection -->
                    <label for="testSelect" style="margin-top:15px;">Select Test:</label>
                    <select id="testSelect" name="test_id" required>
                        <option value="">--Select a Test--</option>
                    </select>

                    <!-- Test Fields Container: Dynamically populated -->
                    <div id="testFieldsContainer" style="margin-top: 20px;"></div>
                    <br>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dynamically added Test Modals -->
<div id="testModalsContainer"></div>

<script>
$(document).ready(function() {
    $('#studentSelect').select2({
        placeholder: "--Select a Student--",
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 3, // Show search box if ≥3 students
        templateResult: function(data) {
            if (!data.id) return data.text; // placeholder
            // Show student name and number in separate styles
            let parts = data.text.split(" - ");
            return $('<div><strong>' + parts[0] + '</strong><br><small>' + parts[1] + '</small></div>');
        },
        templateSelection: function(data, container) {
            return data.text;
        }
    });
});
</script>

<div id="testModal_ishihara" class="modal" style="display: none;">
    <div class="modal-dialog" style="width: auto; max-width: none;">
        <div class="modal-content neumorphic" style="padding: 30px; border-radius: 20px;">
            <div class="modal-header" style="border-bottom: none;">
                <h5 class="modal-title">Ishihara Color Blindness Test</h5>
                <button type="button" class="btn-close" onclick="closeIshiharaTestModal()">&times;</button>
            </div>
            <div class="modal-body" id="ishiharaTestContainer">
                <div class="form-group mb-3" style="position: relative;">
                    <label for="studentSearchInput">Search Student Name</label>
                    <input type="text" id="studentSearchInput" class="form-control mb-2" placeholder="Type or click to search..." oninput="filterStudentList()" onclick="showStudentList()" autocomplete="off" />

                    <!-- Custom dropdown list -->
                    <ul id="studentListDropdown" class="list-group" style="display: none; max-height: 200px; overflow-y: auto; position: absolute; z-index: 10; width: 100%;">
                        <?php
                        $query = "
                            SELECT student_id, name, student_number 
                            FROM students 
                            WHERE student_id NOT IN (
                                SELECT student_id FROM student_ishihara_results
                            )
                        ";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $id = htmlspecialchars($row['student_id']);
                            $text = htmlspecialchars($row['student_number']) . ' - ' . htmlspecialchars($row['name']);
                            echo '<li class="list-group-item" style="cursor: pointer;" onclick="selectStudent(\'' . $id . '\', \'' . addslashes($text) . '\')">' . $text . '</li>';
                        }
                        ?>
                    </ul>

                    <!-- Hidden select used by existing test logic -->
                    <select id="ishiharaStudentSelect" style="display: none;">
                        <option value="">-- Choose a student --</option>
                    </select>
                </div>

                <!-- Area where questions will load -->
                <div id="testQuestionsContainer">
                    <p>Please select a student to start the test.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showStudentList() {
    document.getElementById("studentListDropdown").style.display = "block";
}

function selectStudent(studentId, displayText) {
    document.getElementById("studentSearchInput").value = displayText;

    // Update hidden select with selected ID
    const select = document.getElementById("ishiharaStudentSelect");
    select.innerHTML = `<option value="${studentId}" selected>${displayText}</option>`;

    document.getElementById("studentListDropdown").style.display = "none";

    // Trigger your test loader
    loadIshiharaQuestions(); // uses ishiharaStudentSelect.value internally
}

// Close list when clicking outside
document.addEventListener("click", function (e) {
    if (!document.getElementById("ishiharaTestContainer").contains(e.target)) {
        document.getElementById("studentListDropdown").style.display = "none";
    }
});
</script>

<?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
<div class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999; padding-left: 600px;">
    <div id="uploadSuccessToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                ✅ Certificate uploaded successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() { 
    var toastEl = document.getElementById("uploadSuccessToast");
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();

        setTimeout(function() {
            window.location.href = "http://localhost/medical_records/physician/dashboard.php";
        }, 2100); // Slight delay after toast disappears
    }
});
</script>


<?php endif; ?>
<!-- Upload Certificate Modal -->
<div id="uploadCertificateModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content neumorphic">
            <div class="modal-header">
                <h5 class="modal-title">Upload Medical Certificate</h5>
                <button type="button" class="btn-close" onclick="closeUploadCertificateModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="uploadCertificateForm" enctype="multipart/form-data" method="POST" action="upload_certificate.php">

                    <!-- Searchable Student List -->
                    <div class="form-group mb-3" style="position: relative;">
                        <label for="studentSearchInput_UPLOAD">Select Student:</label>
                        <input type="text" id="studentSearchInput_UPLOAD" class="form-control mb-2" placeholder="Type or click to search..." oninput="filterStudentListCustom(this)" onclick="showStudentListCustom(this)" autocomplete="off" required />

                        <ul id="studentListDropdown_UPLOAD" class="list-group" style="display: none; max-height: 200px; overflow-y: auto; position: absolute; z-index: 10; width: 100%;">
                            <?php 
                            $no_cert_students_result = $conn->query("
                                SELECT s.student_id, s.student_number, s.name
                                FROM students s
                                LEFT JOIN medical_certificate mc ON s.student_id = mc.student_id
                                WHERE mc.student_id IS NULL
                            ");
                            while ($student = $no_cert_students_result->fetch_assoc()) {
                                $id = htmlspecialchars($student['student_id']);
                                $text = htmlspecialchars($student['student_number']) . ' - ' . htmlspecialchars($student['name']);
                                echo "<li class='list-group-item' style='cursor:pointer;' onclick='selectStudentCustom(\"$id\", \"" . addslashes($text) . "\", this)'>$text</li>";
                            }
                            ?>
                        </ul>

                        <select id="hiddenStudentSelect_UPLOAD" style="display:none;" name="student_id"></select>
                    </div>

                    <label for="certificate_file">Select Certificate File (PDF only):</label>
                    <input type="file" name="certificate_file" id="certificate_file" accept="application/pdf" required><br><br>

                    <button type="submit" class="btn btn-primary">Upload Certificate</button>
                </form>
            </div>
        </div>
    </div>
</div>

<section class="students" id="student-list">
<main class="content-container">
    <!-- 📊 Statistics + Actions Dashboard -->
    <section class="dashboard-grid">
        <div class="div3">
            <button id="uploadCertificateBtn" class="btn btn-primary" onclick="openUploadCertificateModal()">Upload Certificate</button>
        </div>
        <div class="div4">
            <button id="startTestBtn" class="btn btn-success" onclick="openStudentTestModal()">Enter Test Data</button>
        </div>
        <div class="div5">
            <button class="btn btn-warning" onclick="debugOpenIshiharaTest()">Start Eye Test</button>
        </div>
        <div class="div6">
            <button class="btn btn-info" onclick="openLogsModal()">View Logs</button>
        </div>
    </section>
</main>

    
<?php
include '../includes/db_connect.php';

$perPage = 10; 

// Capture search inputs for each group
$searchBsmt = $_GET['search_bsmt'] ?? '';
$searchBsmt = $conn->real_escape_string($searchBsmt);

$searchBsmare = $_GET['search_bsmare'] ?? '';
$searchBsmare = $conn->real_escape_string($searchBsmare);

$pageBsmt = max(1, intval($_GET['page_bsmt'] ?? 1));
$pageBsmare = max(1, intval($_GET['page_bsmare'] ?? 1));

// Helper to split full name
function getFirstAndLastName($fullName) {
    $parts = preg_split('/\s+/', trim($fullName));
    return ['first'=>$parts[0] ?? '', 'last'=>$parts[count($parts)-1] ?? ''];
}

// Fetch all students with tests
$query = "
    SELECT 
        s.student_id, s.student_number, s.year_level, s.course, s.name, s.sex, s.contact_number,
        mt.test_name, st.assessment, st.created_at, st.test_id,
        st.bp, st.hr, st.rr, st.o2_sat, st.temperature, st.blood_type,
        st.hearing_result, st.thc_result, st.meth_result, st.subjective,
        st.past_history, st.family_history, st.physical_exam,
        st.lungs_findings, st.heart_findings, st.bones_findings, st.impression,
        NULL AS score
    FROM students s
    LEFT JOIN student_tests st ON s.student_id = st.student_id
    LEFT JOIN medical_tests mt ON st.test_id = mt.test_id
    ORDER BY s.student_number ASC, st.created_at DESC
";

$result = $conn->query($query);

// Group students by course
$students = [];
while ($row = $result->fetch_assoc()) {
    $sid = $row['student_id'];
    if (!isset($students[$sid])) $students[$sid] = ['info'=>$row, 'tests'=>[]];
    $students[$sid]['tests'][] = $row;
}

// Sort helper
function yearLevelOrder($level) {
    $mapping = ['1st year'=>1,'2nd year'=>2,'3rd year'=>3];
    return $mapping[strtolower(trim($level))] ?? 99;
}

function sortStudents(&$group) {
    uasort($group, fn($a,$b)=>yearLevelOrder($a['info']['year_level']) <=> yearLevelOrder($b['info']['year_level']));
}

// Split by course
$bsmt = $bsmare = [];
foreach($students as $sid => $data){
    $course = strtoupper(trim($data['info']['course']));
    if($course==='BSMT') $bsmt[$sid]=$data;
    elseif($course==='BSMARE') $bsmare[$sid]=$data;
}

sortStudents($bsmt);
sortStudents($bsmare);

// Filter function
function filterStudentsArray($students, $search) {
    if(!$search) return $students;
    $search = strtoupper($search);
    return array_filter($students, function($data) use ($search) {
        $student = $data['info'];
        return (stripos($student['student_number'], $search)!==false) || (stripos($student['name'], $search)!==false);
    });
}

// Pagination helper
function paginateGroup($group, $page, $perPage){
    $total = count($group);
    $pages = ceil($total/$perPage);
    $offset = ($page-1)*$perPage;
    return [array_slice($group,$offset,$perPage,true), $pages];
}

// Render function
function renderGroup($students, $groupLabel, $page, $perPage, $queryParam, $searchParam, $searchValue){
    $students = filterStudentsArray($students, $searchValue);
    list($pagedStudents,$totalPages) = paginateGroup($students,$page,$perPage);

    echo "<div style='margin-bottom:60px;'>";
    echo "<h2 style='margin-top:40px;'>$groupLabel</h2>";

    // Search box with Reset
    echo "<form method='GET' style='margin-bottom:10px; display:inline-block;'>
            <input type='text' name='$searchParam' value='".htmlspecialchars($searchValue)."' placeholder='Search by Student Number or Name' class='form-control' style='width:300px; display:inline-block; margin-right:5px;'>
            <input type='hidden' name='$queryParam' value='1'>
            <button type='submit' class='btn btn-primary'>Search</button>
            <a href='{$_SERVER['PHP_SELF']}#student-list' class='btn btn-secondary' style='margin-left:5px;'>Reset</a>
        </form>";


    echo "<table class='table'><thead>
        <tr>
            <th>Student Number</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Year Level</th>
            <th>Course</th>
            <th>Sex</th>
            <th>Contact Number</th>
        </tr></thead><tbody>";

    foreach($pagedStudents as $sid=>$data){
        $student = $data['info'];
        $nameParts = getFirstAndLastName($student['name']);

        echo "<tr class='student-row' data-id='{$sid}'>
                <td>{$student['student_number']}</td>
                <td>".htmlspecialchars($nameParts['first'])."</td>
                <td>".htmlspecialchars($nameParts['last'])."</td>
                <td>{$student['year_level']}</td>
                <td>{$student['course']}</td>
                <td>{$student['sex']}</td>
                <td>{$student['contact_number']}</td>
            </tr>";

        // Test links row
        echo "<tr class='test-links' id='tests-{$sid}' style='display:none;background:#f9f9f9'><td colspan='7'>";
        foreach($data['tests'] as $index=>$test){
            $detailId="detail-{$sid}-{$index}";
            $numericTestId=intval($test['test_id']);
            $assessment=strtolower(trim($test['assessment']??''));
            $statusClass=$assessment==='passed'?'status-passed':($assessment==='failed'?'status-failed':'status-conditional');
            $testName=htmlspecialchars($test['test_name']??'');
            echo "<button class='test-toggle $statusClass' style='color:white;' data-detail-id='{$detailId}' data-test-id='{$numericTestId}'>{$testName}</button> ";
        }
        echo "</td></tr>";

        // Test details
        foreach($data['tests'] as $index=>$test){
            $testId=$sid.'-'.$index;
            echo "<tr class='test-detail-row' id='detail-{$testId}' style='display:none;background:#f1f1f1'><td colspan='7'>";
            echo "<strong>{$test['test_name']}</strong><br>";
            $fields=[
                'bp'=>'Blood Pressure','hr'=>'Heart Rate','rr'=>'Respiratory Rate','o2_sat'=>'Oxygen Saturation',
                'temperature'=>'Temperature','blood_type'=>'Blood Type','hearing_result'=>'Hearing Result',
                'thc_result'=>'THC Result','meth_result'=>'Meth Result','subjective'=>'Subjective Notes',
                'past_history'=>'Past History','family_history'=>'Family History','physical_exam'=>'Physical Exam',
                'assessment'=>'Assessment','lungs_findings'=>'Lungs','heart_findings'=>'Heart','bones_findings'=>'Bones',
                'impression'=>'Impression'
            ];
            foreach($fields as $field=>$label){
                if(!empty($test[$field])) echo "<strong>$label:</strong>".htmlspecialchars($test[$field])."<br>";
            }
            echo "</td></tr>";
        }
    }

    echo "</tbody></table>";

    // Pagination
    $qs = $_GET;
    $qs[$searchParam] = $searchValue;

    echo "<div style='margin-top:10px;'>";
    if($page>1){
        $qs[$queryParam]=$page-1;
        $prevLink=$_SERVER['PHP_SELF'].'?'.http_build_query($qs).'#student-list';
        echo "<a href='{$prevLink}' style='margin-right:10px;'>⬅ Previous</a>";
    }
    if($page<$totalPages){
        $qs[$queryParam]=$page+1;
        $nextLink=$_SERVER['PHP_SELF'].'?'.http_build_query($qs).'#student-list';
        echo "<a href='{$nextLink}' style='margin-left:10px;'>Next ➡</a>";
    }
    echo "</div>";

    echo "</div>";
}

// Render groups
renderGroup($bsmt,'BSMT - Bachelor of Science in Marine Transportation',$pageBsmt,$perPage,'page_bsmt','search_bsmt',$searchBsmt);
renderGroup($bsmare,'BSMarE - Bachelor of Science in Marine Engineering',$pageBsmare,$perPage,'page_bsmare','search_bsmare',$searchBsmare);
?>
</section>

<div id="testModal" class="modal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div class="modal-inner" style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
    <div class="modal-content" style="background:#fff; padding:20px; border-radius:8px; max-width:500px; width:90%;">
      <span id="modalClose" style="float:right; cursor:pointer; font-weight:bold; font-size:20px;">&times;</span>
      <div id="modalBody">Loading...</div>
    </div>
  </div>
</div>

<script>
const tableContainer = document.querySelector('.students');

// Toggle student test rows
document.querySelectorAll('.student-row').forEach(row => {
  row.addEventListener('click', () => {
    const sid = row.getAttribute('data-id');
    const testRow = document.getElementById('tests-' + sid);
    document.querySelectorAll('.test-links').forEach(tr => {
      if (tr !== testRow) tr.style.display = 'none';
    });
    testRow.style.display = (testRow.style.display === 'none' || testRow.style.display === '') ? 'table-row' : 'none';
  });
});

// Close rows on click outside
document.addEventListener('click', (e) => {
  const modal = document.getElementById('testModal');
  if (!tableContainer.contains(e.target) && !modal.contains(e.target)) {
    document.querySelectorAll('.test-links').forEach(tr => tr.style.display = 'none');
  }
});

// Handle test modal logic
document.querySelectorAll('.test-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const detailId = btn.getAttribute('data-detail-id');
    const testName = btn.textContent.trim();
    const isIshihara = testName.toLowerCase().includes('ishihara');

    const detailRow = document.getElementById(detailId);
    if (!detailRow || detailRow.innerText.trim() === '') {
      alert("No test result available for this test.");
      return;
    }

    const studentRow = btn.closest('tr').previousElementSibling;
    const sid = studentRow.getAttribute('data-id');
    const testButtons = studentRow.nextElementSibling.querySelectorAll('.test-toggle');

    const modal = document.getElementById('testModal');
    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = '';
    modal.style.display = 'flex';

    if (isIshihara) {
      const temp = document.createElement('div');
      temp.innerHTML = detailRow.innerHTML;
      temp.querySelectorAll('div').forEach(div => {
        if (div.innerText.trim() === '' && div.children.length === 0) div.remove();
      });
      modalBody.innerHTML = temp.innerHTML || '<p style="color:#888;">No Ishihara test results available for this student.</p>';
      return;
    }

    // Build dropdown and testId map
    const select = document.createElement('select');
    select.id = 'testSelectDropdown';
    select.style = 'width:100%; margin-bottom:12px; padding:6px;';
    
    const testIdMap = {}; // detailId => numeric test_id
    testButtons.forEach(button => {
      const name = button.textContent.trim();
      if (name.toLowerCase().includes('ishihara')) return;

      const tid = button.getAttribute('data-detail-id')?.trim();
      const numericTid = button.getAttribute('data-test-id')?.trim(); // ✅ Should be numeric (1–5)

      if (!tid || !numericTid) return;
      testIdMap[tid] = numericTid;

      const opt = document.createElement('option');
      opt.value = tid;
      opt.textContent = name;
      if (tid === detailId) opt.selected = true;
      select.appendChild(opt);
    });

    const form = document.createElement('form');
    form.id = 'editTestForm';
    form.dataset.studentId = sid;
    modalBody.appendChild(select);
    modalBody.appendChild(form);

    function loadTestContent(selectedId) {
  const row = document.getElementById(selectedId);
  if (!row) return;

  const rawHtml = row.innerHTML;
  form.innerHTML = '';

  const regex = /<strong>([^<:]+):<\/strong>\s*([^<]*)<br>/g;
  let match;
  const fields = [];

  while ((match = regex.exec(rawHtml)) !== null) {
    const label = match[1].trim();
    const value = match[2].trim();
    const name = label.toLowerCase().replace(/[^a-z0-9_]/gi, '_');

    fields.push({ label, value, name });
  }

  if (fields.length === 0) {
    form.innerHTML = `<p style="color:#999;">No editable fields found for this test.</p>`;
    return;
  }

  // Render view-only mode
  fields.forEach(f => {
    form.innerHTML += `
      <label><strong>${f.label}:</strong></label>
      <span class="view-mode">${f.value}</span>
      <input type="text" class="edit-mode" name="${f.name}" value="${f.value}" style="width:100%; margin-bottom:10px; display:none;">
      <br>
    `;
  });

  // Action buttons
  form.innerHTML += `
    <div style="margin-top:10px;">
      <button type="button" id="editBtn" style="background:#007bff; color:white; padding:8px 14px; border:none; border-radius:5px;">Edit</button>
      <button type="submit" id="saveBtn" style="display:none; background:#28a745; color:white; padding:8px 14px; border:none; border-radius:5px;">Save</button>
      <button type="button" id="cancelBtn" style="display:none; background:#6c757d; color:white; padding:8px 14px; border:none; border-radius:5px;">Cancel</button>
    </div>
  `;

  // Edit toggle
  form.querySelector('#editBtn').addEventListener('click', () => {
    form.querySelectorAll('.view-mode').forEach(e => e.style.display = 'none');
    form.querySelectorAll('.edit-mode').forEach(e => e.style.display = 'inline-block');
    form.querySelector('#editBtn').style.display = 'none';
    form.querySelector('#saveBtn').style.display = 'inline-block';
    form.querySelector('#cancelBtn').style.display = 'inline-block';
  });

  // Cancel toggle
  form.querySelector('#cancelBtn').addEventListener('click', () => {
    form.querySelectorAll('.edit-mode').forEach((input, i) => {
      input.value = fields[i].value;
      input.style.display = 'none';
    });
    form.querySelectorAll('.view-mode').forEach((span, i) => {
      span.textContent = fields[i].value;
      span.style.display = 'inline';
    });
    form.querySelector('#editBtn').style.display = 'inline-block';
    form.querySelector('#saveBtn').style.display = 'none';
    form.querySelector('#cancelBtn').style.display = 'none';
  });
}


    // Load initial test content
    loadTestContent(detailId);

    // Switch test on dropdown change
    select.addEventListener('change', () => {
      loadTestContent(select.value.trim());
    });

    // Submit form
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const selectedDetailId = select.value.trim();
      const numericTestId = testIdMap[selectedDetailId];

      if (!numericTestId || isNaN(numericTestId)) {
        console.error('Invalid test_id mapping:', {
          selectedDetailId,
          testIdMap,
          matchedValue: testIdMap[selectedDetailId]
        });
        alert('Error: Invalid numeric test ID!');
        return;
      }

      const formData = new FormData(form);
      formData.append('student_id', form.dataset.studentId);
      formData.append('test_id', numericTestId);
      formData.append('test_detail_id', selectedDetailId);

      // Debug what's being submitted
      console.log('Submitting:', Object.fromEntries(formData.entries()));

      fetch('update_test_result.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.text())
      .then(response => {
        alert(response.includes('success') ? 'Test result updated!' : 'Update failed.');
        modal.style.display = 'none';
        location.reload();
      })
      .catch(err => {
        alert('Error updating test: ' + err.message);
      });
    });
  });
});

// Modal close handler
document.getElementById('modalClose').addEventListener('click', () => {
  document.getElementById('testModal').style.display = 'none';
});

</script>
<!-- Modal for Activity Logs -->
<div id="logsModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y:auto;">
    <div style="background:#fff; width:100%; max-width:1000px; margin:100px auto; padding:40px; border-radius:8px; position:relative;">
        <span onclick="closeLogsModal()" style="position:absolute; top:10px; right:20px; cursor:pointer; font-size:24px;">&times;</span>
        <h2>Activity Logs</h2>

        <div id="logsContainerWrapper" style="overflow-x:auto; max-height:260px; overflow-y:hidden;">
            <div id="logsContainer" style="min-width:900px;">
                Loading logs...
            </div>
        </div>

        <!-- View All Button -->
        <button id="viewAllBtn" onclick="viewAllLogs()" 
                style="margin-top:10px; background:#0069d9; color:#fff; border:none; padding:10px 18px; border-radius:6px; cursor:pointer;">
            View All
        </button>

    </div>
</div>

<script>
function openLogsModal() {
    document.getElementById('logsModal').style.display = 'block';

    fetch('fetch_logs.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('logsContainer').innerHTML = data;
        })
        .catch(err => {
            document.getElementById('logsContainer').innerHTML = 'Error loading logs.';
            console.error(err);
        });
}

function closeLogsModal() {
    document.getElementById('logsModal').style.display = 'none';
}

/* Expand logs to show everything */
function viewAllLogs() {
    let wrapper = document.getElementById('logsContainerWrapper');
    wrapper.style.maxHeight = "none";
    wrapper.style.overflowY = "auto";
    
    document.getElementById('viewAllBtn').style.display = "none";
}
</script>


<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>
</html>
