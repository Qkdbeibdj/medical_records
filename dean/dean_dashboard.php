<?php
// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");


session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dean') {
    header("Location: ../index.php"); // redirect to login
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$dean_name = $user['name']?? 'Unknown';
$stmt->close();

if (isset($_POST['add_physician'])) {
    $name = $_POST['physician_name'] ?? null;
    $email = $_POST['physician_email'] ?? null;
    $default_password = "1234"; // You may want to generate a secure password and email it
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    $role = "physician";

    if (!$name || !$email) {
        echo "<script>alert('Name and email are required!');</script>";
    } else {
        // Check if email already exists
        $check_query = "SELECT email FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('Error: Email already exists!');</script>";
        } else {
            // Insert physician
            $sql = "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Physician added successfully.');
                        window.location.href = 'dean_dashboard.php';
                      </script>";
                exit();
            } else {
                echo "<script>alert('Error adding physician.');</script>";
            }
        }
        $check_stmt->close();
    }
}


if (isset($_POST['add_student'])) {
    $student_number = $_POST['student_number'] ?? null;
    $name = $_POST['name'] ?? null;
    $year_level = $_POST['year_level'] ?? null;
    $course = $_POST['course'] ?? null;
    $email = $_POST['email'] ?? null;
    $contact_number = $_POST['contact_number'] ?? null;
    $birthdate = $_POST['birthdate'] ?? null;
    $address = $_POST['address'] ?? null;
    $age = $_POST['age'] ?? null;
    $sex = $_POST['sex'] ?? null;

    $default_password = "1234"; // Default password
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    $role = "student";

    if (!$student_number || !$name || !$year_level || !$course || !$email || !$contact_number || !$birthdate || !$address || !$age || !$sex) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        // Check for duplicate student number
        $check_query = "SELECT student_number FROM students WHERE student_number = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $student_number);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('Error: Student number already exists!');</script>";
        } else {
            // Insert student
            $sql_student = "INSERT INTO students (student_number, name, year_level, course, email, contact_number, birthdate, address, age, sex) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_student = $conn->prepare($sql_student);
            $stmt_student->bind_param("ssssssssss", $student_number, $name, $year_level, $course, $email, $contact_number, $birthdate, $address, $age, $sex);

            if ($stmt_student->execute()) {
                $student_id = $stmt_student->insert_id; // ✅ Get the new student's ID

                // Now insert into users
                $sql_user = "INSERT INTO users (name, email, password_hash, role, student_id) VALUES (?, ?, ?, ?, ?)";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("ssssi", $name, $email, $hashed_password, $role, $student_id);
                if ($stmt_user->execute()) {
                    echo "<script>
                            alert('Student added successfully.');
                            window.location.href = 'dean_dashboard.php';
                          </script>";
                    exit();
                } else {
                    echo "<script>alert('Error adding user account.');</script>";
                }
            } else {
                echo "<script>alert('Error adding student.');</script>";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dean_style.css">
    <script src="../script/dean_script.js"></script>
</head>
<body>
<div class="dashboard-container">
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="../images/logo/NOBG.png" alt="Logo" class="logo">
                <div class="college-info">
                    <h2>PHILIPPINE COLLEGE OF</h2>
                    <h3>SCIENCE AND TECHNOLOGY</h3>
                    <p>CALASIAO, PANGASINAN, PHILIPPINES 2418</p>
                </div>
            </div>
        </div>
    </header>

   <nav class="top-nav">
    <div class="menu-bar-container" id="menu-bar-container">
        <div class="hamburger-menu" onclick="toggleNav()">
            <i class="bi bi-list"></i>
        </div>
        
        <nav id="nav-links" class="nav-links">
            <ul>
                <li>
                    <a href="dean_dashboard.php" class="nav-link" data-title="Home">
                        <i class="bi bi-house-door"></i>
                        <span class="nav-text">Home</span>
                    </a>
                </li>

                <li>
                    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#addStudentPanel" aria-expanded="false" data-title="Add Student">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Add Student</span>
                    </button>
                </li>
                                        
                

                <li>
                    <a href="javascript:void(0);" class="nav-link" onclick="openSettingsModal()" data-title="Settings">
                        <i class="bi bi-gear"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>

                <li>
                    <button class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#manageUsersPanel" aria-expanded="false" data-title="Manage Accounts">
                        <i class="bi bi-person-x-fill"></i>
                        <span>Manage Accounts</span>
                    </button>
                </li>

                <!-- <li>
                    <a href="backup_restore.php" class="nav-link" data-title="Backup & Restore">
                        <i class="bi bi-hdd-network"></i>
                        <span class="nav-text">Backup & Restore</span>
                    </a>
                </li> -->

                <li>
                    <a href="logout.php" class="nav-link" data-title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>       
    </div>
</nav>

<!-- Add Student Modal -->
        <div class="collapse mt-3" id="addStudentPanel">
            <div class="card card-body shadow">
                <h3>Add Student</h3>
                <form method="post">
                    <label>Student Number:</label>
                    <input type="text" name="student_number" required>
                    
                    <label>Year Level:</label>
                    <select name="year_level" required>
                        <option value="">Select Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                    </select>
                    
                    <label>Course:</label>
                    <select name="course" required>
                        <option value="">Select Course</option>
                        <option value="BSMT">BSMT</option>
                        <option value="BSMarE">BSMarE</option>
                    </select>

                    <label>Full Name(FirstName/MiddleInitial/LastName):</label>
                    <input type="text" name="name" required>
                    <label>Email:</label>
                    <input type="email" name="email" required>
                    <label>Contact Number:</label>
                    <input type="text" name="contact_number" required>
                    <label>Birthdate:</label>
                    <input type="date" name="birthdate" required>
                    <label>Address:</label>
                    <input type="text" name="address" required>
                    <label>Age:</label>
                    <input type="number" name="age" required>
                    <label>Sex:</label>
                    <select name="sex" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <button type="submit" name="add_student" class="add-student-btn">Add Student</button>
                </form>
            </div>
        </div>


        <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ Student status updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<script>
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) alert.style.display = 'none';
}, 4000);
</script>

<div class="collapse mt-3" id="manageUsersPanel">
    <div class="card card-body shadow">
        <h5><i class="bi bi-person-lines-fill"></i> Manage Student Account</h5>
        <form method="POST" action="manage_users.php">
            <div class="mb-3">
                <label for="student_id" class="form-label">Select Inactive Student</label>
                <select class="form-select" name="student_id" required>
                    <option value="" disabled selected>-- Choose a student --</option>
                    <?php
                    include '../includes/db_connect.php';
                    $students = $conn->query("
                        SELECT student_id, student_number, name, status 
                        FROM students 
                        WHERE status = 'inactive'
                        ORDER BY name
                    ");

                    $inactiveCount = $students->num_rows;

                    while ($row = $students->fetch_assoc()):
                    ?>
                        <option value="<?= $row['student_id'] ?>">
                            <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['student_number']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if ($inactiveCount > 0): ?>
                <input type="hidden" name="new_status" value="active">
                <button type="submit" class="btn btn-success w-100">
                    Activate Student
                </button>
            <?php else: ?>
                <p class="text-muted">No inactive students available.</p>
            <?php endif; ?>
        </form>
    </div>
</div>

<div id="importCsvModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeImportCsvModal()">&times;</span>
        <h3>Import CSV</h3>
        <form id="importCsvForm" method="POST" action="import_students.php" enctype="multipart/form-data">
            <label>Select CSV File:</label>
            <input type="file" name="csv_file" id="csvFileInput" accept=".csv" required>
            <button type="submit" class="import-csv-btn">Import</button>
        </form>
    </div>
</div>


        
        <div id="settingsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeSettingsModal()">&times;</span>
                <h3>Settings</h3>
                <form action="update_settings.php" method="POST">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>


                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password">

                    <input type="submit" value="Save Changes">
                </form>
            </div>
        </div>

      <section class="students" id="student-list">
    <h1>Student Enrollment Overview</h1>

    <?php
require '../includes/db_connect.php';

$bsmt_result = $conn->query("
    SELECT *, 
           SUBSTRING_INDEX(name, ' ', 1) AS first_name,
           SUBSTRING_INDEX(name, ' ', -1) AS last_name
    FROM students 
    WHERE course = 'BSMT' AND status = 'active'
    ORDER BY FIELD(year_level, '1st Year', '2nd Year', '3rd Year')
");

$bsmare_result = $conn->query("
    SELECT *, 
           SUBSTRING_INDEX(name, ' ', 1) AS first_name,
           SUBSTRING_INDEX(name, ' ', -1) AS last_name
    FROM students 
    WHERE course = 'BSMarE' AND status = 'active'
    ORDER BY FIELD(year_level, '1st Year', '2nd Year', '3rd Year')
");

$bsmt_students = $bsmt_result->fetch_all(MYSQLI_ASSOC);
$bsmare_students = $bsmare_result->fetch_all(MYSQLI_ASSOC);
?>


    <!-- Summary Boxes -->
    <div class="course-summary">
        <div class="course-card">
            <h2>BSMT</h2>
            <p>Total Enrolled: <?= count($bsmt_students) ?></p>
            <button onclick="showDetails('bsmt')">View Details</button>
        </div>
        <div class="course-card">
            <h2>BSMarE</h2>
            <p>Total Enrolled: <?= count($bsmare_students) ?></p>
            <button onclick="showDetails('bsmare')">View Details</button>
        </div>
    </div>
<!-- BSMT Table -->
<div class="details-table" id="bsmt-table" style="display: none;">
    <h3>BSMT Students</h3>
    <input type="text" id="search-bsmt" placeholder="Search by Student Number or Name..." 
           onkeyup="filterTable('bsmt')" 
           style="padding: 8px 12px; margin-bottom: 15px; width: 100%; border-radius: 10px; border: 1px solid #ccc;">

    <table>
        <thead>
            <tr>
                <th>Student Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Year Level</th>
                <th>Contact Number</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="bsmt-body">
            <?php foreach ($bsmt_students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['student_number']) ?></td>
                    <td><?= htmlspecialchars($student['first_name']) ?></td>
                    <td><?= htmlspecialchars($student['last_name']) ?></td>
                    <td><?= htmlspecialchars($student['year_level']) ?></td>
                    <td><?= htmlspecialchars($student['contact_number']) ?></td>
                    <td><button class="evaluateBtn" data-student="<?= $student['student_id']; ?>">Evaluate</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination" id="bsmt-pagination"></div>
</div>

<!-- BSMarE Table -->
<div class="details-table" id="bsmare-table" style="display: none;">
    <h3>BSMarE Students</h3>
    <input type="text" id="search-bsmare" placeholder="Search by Student Number or Name..." 
           onkeyup="filterTable('bsmare')" 
           style="padding: 8px 12px; margin-bottom: 15px; width: 100%; border-radius: 10px; border: 1px solid #ccc;">

    <table>
        <thead>
            <tr>
                <th>Student Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Year Level</th>
                <th>Contact Number</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="bsmare-body">
            <?php foreach ($bsmare_students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['student_number']) ?></td>
                    <td><?= htmlspecialchars($student['first_name']) ?></td>
                    <td><?= htmlspecialchars($student['last_name']) ?></td>
                    <td><?= htmlspecialchars($student['year_level']) ?></td>
                    <td><?= htmlspecialchars($student['contact_number']) ?></td>
                    <td><button class="evaluateBtn" data-student="<?= $student['student_id']; ?>">Evaluate</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination" id="bsmare-pagination"></div>
</div>


<div id="evaluateModal" class="modal" style="
    display:none; 
    position:fixed; 
    top:0; 
    left:0; 
    width:100%; 
    height:100%; 
    background: rgba(0,0,0,0.6); 
    z-index:999;
    overflow:auto;
">
  <div style="
      background:#e0e5ec; 
      width:80%; 
      max-width: 1000px;
      margin: 50px auto; 
      padding:0; 
      border-radius:20px; 
      position:relative;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
  ">

    <!-- Non-scrolling header -->
    <div style="
        padding: 20px; 
        background: #e0e5ec; 
        border-top-left-radius: 20px; 
        border-top-right-radius: 20px;
        position: relative;
    ">
      <button id="closeModal" style="
          position: absolute;
          top: 10px;
          right: 10px;
          font-size: 18px;
          padding: 8px 16px;
          border: none;
          background-color: #f44336;
          color: white;
          border-radius: 8px;
          cursor: pointer;
      ">Close</button>
      <h2 style="margin: 0;">Student Evaluation</h2>
    </div>

    <!-- ✅ Scrollable dynamic content -->
    <div id="evaluateContent" style="
        padding: 20px;
        overflow-y: auto;
        max-height: calc(90vh - 80px); /* reserve header height */
    ">
      Loading...
    </div>

  </div>
</div>

<script>
document.querySelectorAll('.evaluateBtn').forEach(button => {
  button.addEventListener('click', () => {
    const studentId = button.dataset.student;
    fetch(`evaluate_student_content.php?student_id=${studentId}`)
      .then(res => res.text())
      .then(html => {
        document.getElementById('evaluateContent').innerHTML = html;
        document.getElementById('evaluateModal').style.display = 'block';

        // ✅ Attach listeners for both buttons separately
        document.querySelectorAll('#statusForm button').forEach(statusButton => {
          statusButton.addEventListener('click', function(e) {
            e.preventDefault();

            const formData = new FormData(document.getElementById('statusForm'));
            formData.append('status', this.value); // manually append clicked button value

            fetch('update_status.php', {
              method: 'POST',
              body: formData
            })
            .then(res => res.text())
            .then(response => {
              if (response.trim() === 'success') {
                document.getElementById('statusMessage').innerText = "Status updated successfully!";
                // ✅ Auto-refresh after 1 second
                setTimeout(() => {
                  location.href = "dean_dashboard.php";
                }, 1000);
              } else {
                document.getElementById('statusMessage').innerText = response;
              }
            })
            .catch(err => {
              document.getElementById('statusMessage').innerText = "Error updating status.";
            });
          });
        });

      });
  });
});

document.getElementById('closeModal').addEventListener('click', () => {
  document.getElementById('evaluateModal').style.display = 'none';
});

</script>

<script>
function showDetails(course) {
    const bsmt = document.getElementById('bsmt-table');
    const bsmare = document.getElementById('bsmare-table');
    const searchBsmt = document.getElementById('search-bsmt');
    const searchBsmare = document.getElementById('search-bsmare');

    if (course === 'bsmt') {
        if (bsmt.style.display === 'none' || bsmt.style.display === '') {
            bsmt.style.display = 'block';
            bsmare.style.display = 'none';
        } else {
            bsmt.style.display = 'none';
            searchBsmt.value = '';  // ✅ Reset search
            filterTable('bsmt');   // ✅ Refresh list
        }
    } else if (course === 'bsmare') {
        if (bsmare.style.display === 'none' || bsmare.style.display === '') {
            bsmare.style.display = 'block';
            bsmt.style.display = 'none';
        } else {
            bsmare.style.display = 'none';
            searchBsmare.value = ''; // ✅ Reset search
            filterTable('bsmare');   // ✅ Refresh list
        }
    }
}
</script>

<script>
function filterTable(course) {
    const input = document.getElementById(`search-${course}`);
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll(`#${course}-body tr`);

    rows.forEach(row => {
        const studentNumber = row.cells[0].textContent.toLowerCase();
        const firstName = row.cells[1].textContent.toLowerCase();
        const lastName = row.cells[2].textContent.toLowerCase();

        if (studentNumber.includes(filter) || firstName.includes(filter) || lastName.includes(filter)) {
            row.dataset.visible = "true";  // mark visible
        } else {
            row.dataset.visible = "false"; // mark hidden
        }
    });

    // ✅ Re-run pagination for this course
    paginateTable(course);
}

function paginateTable(course, rowsPerPage = 10) {
    const rows = Array.from(document.querySelectorAll(`#${course}-body tr`));
    const pagination = document.getElementById(`${course}-pagination`);

    // Only visible rows after filter
    const visibleRows = rows.filter(row => row.dataset.visible !== "false");
    const totalRows = visibleRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    let currentPage = 1;

    function showPage(page) {
        currentPage = page;
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach(row => row.style.display = "none"); // hide all
        visibleRows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = "";
            }
        });

        // Build pagination buttons
        pagination.innerHTML = "";
        if (totalPages > 1) {
            if (page > 1) {
                pagination.innerHTML += `<button onclick="window.showPage_${course}(${page-1})">« Prev</button>`;
            }
            for (let i = 1; i <= totalPages; i++) {
                pagination.innerHTML += `<button onclick="window.showPage_${course}(${i})" ${i === currentPage ? 'style="font-weight:bold;"' : ''}>${i}</button>`;
            }
            if (page < totalPages) {
                pagination.innerHTML += `<button onclick="window.showPage_${course}(${page+1})">Next »</button>`;
            }
        }
    }

    // Expose showPage globally for each course
    window[`showPage_${course}`] = showPage;

    showPage(currentPage);
}

// ✅ Initialize on load
document.addEventListener("DOMContentLoaded", () => {
    ["bsmt", "bsmare"].forEach(course => {
        // mark all rows as visible initially
        document.querySelectorAll(`#${course}-body tr`).forEach(row => row.dataset.visible = "true");
        paginateTable(course);
    });
});
</script>
<script>
    // Toggle navigation menu on mobile
function toggleNav() {
    const navLinks = document.getElementById('nav-links');
    const hamburger = document.querySelector('.hamburger-menu i');
    
    navLinks.classList.toggle('active');
    
    // Change hamburger icon
    if (navLinks.classList.contains('active')) {
        hamburger.classList.remove('bi-list');
        hamburger.classList.add('bi-x');
    } else {
        hamburger.classList.remove('bi-x');
        hamburger.classList.add('bi-list');
    }
}

// Close nav menu when clicking outside
document.addEventListener('click', function(event) {
    const navLinks = document.getElementById('nav-links');
    const hamburger = document.querySelector('.hamburger-menu');
    const menuBar = document.getElementById('menu-bar-container');
    
    if (!menuBar.contains(event.target) && navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        const icon = hamburger.querySelector('i');
        icon.classList.remove('bi-x');
        icon.classList.add('bi-list');
    }
});

// Close nav menu when a link is clicked (on mobile)
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            const navLinks = document.getElementById('nav-links');
            const hamburger = document.querySelector('.hamburger-menu i');
            
            navLinks.classList.remove('active');
            hamburger.classList.remove('bi-x');
            hamburger.classList.add('bi-list');
        }
    });
});

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        const navLinks = document.getElementById('nav-links');
        const hamburger = document.querySelector('.hamburger-menu i');
        
        if (window.innerWidth > 768) {
            navLinks.classList.remove('active');
            hamburger.classList.remove('bi-x');
            hamburger.classList.add('bi-list');
        }
    }, 250);
});
</script>

</body>
</html>