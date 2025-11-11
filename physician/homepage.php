<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include '../includes/db_connect.php';
include 'header.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "⚠️ User not logged in.";
    exit();
}

// Get physician info
$stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "⚠️ User not found.";
    exit();
}

$physician_name = $user['name'];
$physician_email = $user['email'];

// Fetch total students
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];

// Fetch total certificates stored
$total_certificates = $conn->query("SELECT COUNT(*) AS total FROM medical_certificate")->fetch_assoc()['total'];

// --- Students with remaining tests ---
$stmt = $conn->prepare("
    SELECT s.student_id, s.name, s.student_number
    FROM students s
    WHERE EXISTS (
        SELECT 1 FROM medical_tests mt
        WHERE NOT EXISTS (
            SELECT 1 FROM student_tests st
            WHERE st.student_id = s.student_id
            AND st.test_id = mt.test_id
        )
    )
    ORDER BY s.name ASC
");
$stmt->execute();
$students_result = $stmt->get_result();
$stmt->close();

// Fetch total number of certificate requests
$countQuery = "SELECT COUNT(*) AS total_requests FROM certificate_requests WHERE status = 'Pending'";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalRequests = $countRow['total_requests'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Physician Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../css/home.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    .pagination { margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap; }
    .pagination a { padding: 5px 10px; border: 1px solid #ccc; text-decoration: none; border-radius: 5px; color: #333; cursor:pointer; }
    .pagination a:hover { background-color: #f0f0f0; }
    .pagination a.active { background-color: #007bff; color: white; border-color: #007bff; }
    .view-all-link { display: inline-block; margin-top: 10px; color: #007bff; text-decoration: none; font-weight: 500; cursor:pointer; }
    .view-all-link:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="dashboard">
    <h2 class="neumorphic-heading">Welcome back, Dr. <?= htmlspecialchars($physician_name) ?>!</h2>

    <!-- Stats -->
    <div class="stats-cards">
        <div class="card neumorphic-card">
            <h3>Total Students</h3>
            <p><?= $total_students ?></p>
        </div>
        <div class="card neumorphic-card">
            <h3>Certificates Stored</h3>
            <p><?= $total_certificates ?></p>
        </div>
    </div>

    <!-- Notify Student -->
<div class="neumorphic-section">
    <h3 style="cursor: pointer;" onclick="toggleForm()">➕ Notify Student for Upcoming Test</h3>
    <form id="notifyForm" action="notify_student.php" method="POST" style="display: none;">
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                
                <!-- Student Dropdown (with Select2 applied) -->
                <label for="studentSelect">Select Student:</label>
                <select id="studentSelect" name="student_id" required style="width: 100%;">
                    <option value="">-- Select Student --</option>
                    <?php while ($student = $students_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($student['student_id']) ?>">
                            <?= htmlspecialchars($student['student_number'] . ' - ' . $student['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label for="test_id">Select Test:</label>
                <select name="test_type" id="test_id" required style="width: 100%;">
                    <option value="">-- Select Test --</option>
                </select>
            </div>
        </div>

        <label for="test_date">Test Date & Time:</label>
        <input type="datetime-local" name="test_date" required />

        <label for="deadline">Deadline (optional):</label>
        <input type="date" name="deadline" />

        <label for="message">Additional Notes:</label>
        <textarea name="message" placeholder="Add any additional instructions here..."></textarea>

        <input type="submit" value="Send Notification" class="notify-btn" />
    </form>
</div>


<!-- Load Select2 CSS/JS once (at the bottom of your HTML before closing </body>) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for student dropdown
    $('#studentSelect').select2({
        placeholder: "-- Select Student --",
        allowClear: true,
        width: '100%'
    });

    // When a student is selected, fetch their available tests
    $('#studentSelect').on('change', function() {
        var studentId = $(this).val();
        var testDropdown = $('#test_id');

        // Reset dropdown
        testDropdown.empty().append('<option value="">-- Select Test --</option>');

        if (studentId) {
            $.ajax({
                url: 'get_tests_for_student.php',
                type: 'GET',
                data: { student_id: studentId },
                dataType: 'json',
                success: function(data) {
                    if (data.length > 0) {
                        $.each(data, function(index, test) {
                            testDropdown.append(
                                $('<option>', {
                                    value: test.test_id,
                                    text: test.test_name
                                })
                            );
                        });
                    } else {
                        testDropdown.append('<option value="">No tests available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    testDropdown.append('<option value="">Error loading tests</option>');
                }
            });
        }
    });
});
</script>

    <!-- View All Certificate Requests -->
    <div class="neumorphic-section">
        <h3 style="cursor: pointer;" onclick="toggleCertificateSection()">
            📄 View All Certificate Requests 
            <span id="requestCount" class="badge"><?php echo $totalRequests; ?></span>
        </h3>
        <div id="certificateSection" style="display: none;">
            <div id="certificateRequests" style="margin-top: 15px;">
                Loading requests...
            </div>
        </div>
    </div>
    <style>
        .badge {
            background-color: #2563eb; /* Tailwind blue-600 */
            color: white;
            border-radius: 9999px;
            padding: 3px 10px;
            font-size: 12px;
            margin-left: 8px;
        }
    </style>

    <!-- Recent Activities -->
    <div class="neumorphic-section">
        <h3>Recent Activities</h3>
        <ul class="activities-list">
            <!-- Activities will be loaded here via AJAX -->
        </ul>
        <div class="activities-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top:10px;">
            <div id="activities-pagination" style="display: flex; gap: 8px;"></div>
            <a id="toggleViewAll" class="view-all-link" data-view="paginated">View All Activities</a>
        </div>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('notifyForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

// Load tests for selected student
document.querySelector('select[name="student_id"]').addEventListener('change', function() {
    const studentId = this.value;
    const testSelect = document.getElementById('test_id');
    testSelect.innerHTML = '<option value="">Loading tests...</option>';
    if (!studentId) {
        testSelect.innerHTML = '<option value="">-- Select Test --</option>';
        return;
    }
    fetch('get_tests_for_student.php?student_id=' + encodeURIComponent(studentId))
        .then(res => res.json())
        .then(data => {
            testSelect.innerHTML = '';
            if (data.length === 0) {
                testSelect.innerHTML = '<option value="">No remaining tests for this student</option>';
            } else {
                testSelect.innerHTML = '<option value="">-- Select Test --</option>';
                data.forEach(test => {
                    const option = document.createElement('option');
                    option.value = test.test_id;
                    option.textContent = test.test_name;
                    testSelect.appendChild(option);
                });
            }
        })
        .catch(() => testSelect.innerHTML = '<option value="">Error loading tests</option>');
});

// Certificate Requests Section
function toggleCertificateSection() {
    const section = document.getElementById('certificateSection');
    section.style.display = (section.style.display === 'none' || section.style.display === '') ? 'block' : 'none';
    if (section.style.display === 'block') loadCertificateRequests();
}
function loadCertificateRequests() {
    const container = $('#certificateRequests');
    container.html('Loading requests...');

    $.get('certificate_request_ajax.php', function(data) {
        container.html(data);

        let currentRequestId = null;

        // Show modal
        container.off('click', '.btn-approve').on('click', '.btn-approve', function() {
        currentRequestId = $(this).data('id');
        $('#claimDatetimeInput').val('');
        $('#claimModal, #claimModalBackdrop').fadeIn(150).removeClass('hidden').css('display', 'flex');
        });

        // Cancel modal
        $('#cancelClaimBtn').off('click').on('click', function() {
        $('#claimModal, #claimModalBackdrop').fadeOut(150, function() {
            $(this).addClass('hidden');
        });
        currentRequestId = null;
        });

        // Submit modal
        $('#submitClaimBtn').off('click').on('click', function() {
        const claimDatetime = $('#claimDatetimeInput').val();
        if (!claimDatetime) {
            alert("Please select a date and time.");
            return;
        }

        if (!currentRequestId) {
            alert("No request selected.");
            return;
        }

        $.post('approve_request.php', {
            request_id: currentRequestId,
            claim_datetime: claimDatetime
        }, function(response) {
            alert(response);
            location.reload();
        });

        $('#claimModal, #claimModalBackdrop').fadeOut(150, function() {
            $(this).addClass('hidden');
        });
        });


        // Delegate reject button click
        container.off('click', '.btn-reject').on('click', '.btn-reject', function() {
            const requestId = $(this).data('id');
            if (!requestId) return;

            if (confirm("Reject this request?")) {
                $.post('reject_request.php', { id: requestId }, function(response) {
                    alert(response);
                    // Reload the page after rejection
                    location.reload();
                });
            }
        });
    });
}

let currentView = 'paginated';

function loadActivities(page = 1) {
    const activitiesList = $('.activities-list');
    const paginationContainer = $('#activities-pagination');
    activitiesList.html('<li>Loading...</li>');
    paginationContainer.html('');
    $.get('fetch_activities.php', { page: page, view: currentView }, function(data) {
        activitiesList.html(data);
        // Bind click for pagination links
        $('.page-link').click(function(e) {
            e.preventDefault();
            const pageNum = $(this).data('page');
            loadActivities(pageNum);
        });
    });
}

// Toggle between paginated / all view
$('#toggleViewAll').click(function() {
    currentView = $(this).data('view') === 'paginated' ? 'all' : 'paginated';
    $(this).text(currentView === 'all' ? 'Back to Paginated View' : 'View All Activities');
    $(this).data('view', currentView === 'all' ? 'all' : 'paginated');
    loadActivities(1);
});

// Initial load
loadActivities();
</script>

<!-- Claim Date & Time Modal -->
<div id="claimModalBackdrop" class="modal-backdrop hidden"></div>

<div id="claimModal" class="modal-container hidden">
  <div class="modal-content">
    <h3 class="modal-title">Select Claim Date & Time</h3>
    <input type="datetime-local" id="claimDatetimeInput" class="modal-input" />

    <div class="modal-actions">
      <button id="cancelClaimBtn" class="btn-secondary">Cancel</button>
      <button id="submitClaimBtn" class="btn-primary">Confirm</button>
    </div>
  </div>
</div>
<style>
    /* Backdrop */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 50;
  display: none;
}

.modal-container {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 1rem; /* rounded-2xl */
  padding: 2rem;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  animation: fadeIn 0.3s ease;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.modal-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb; /* neutral-200 */
  border-radius: 0.5rem;
  font-size: 0.95rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn-primary {
  background-color: #2563eb; /* blue-600 */
  color: white;
  border: none;
  padding: 0.6rem 1.25rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-primary:hover {
  background-color: #1e40af; /* blue-800 */
}

.btn-secondary {
  background-color: #f3f4f6; /* neutral-100 */
  color: #374151; /* neutral-700 */
  border: none;
  padding: 0.6rem 1.25rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-secondary:hover {
  background-color: #e5e7eb;
}

.hidden {
  display: none;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</body>
</html>
