<?php
// ===============================================
// MERS - Student Dashboard with Receipt Upload
// ===============================================

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
include '../includes/db_connect.php';

// ✅ Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

/* ======================================================
   🔹 DELETE EXPIRED SCHEDULED TESTS
====================================================== */
date_default_timezone_set('Asia/Manila');
$currentDateTime = date('Y-m-d H:i:s');

$stmt = $conn->prepare("
    DELETE FROM student_notifications 
    WHERE student_id = ? 
    AND test_datetime < NOW()
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$deleted_count = $stmt->affected_rows;
$stmt->close();

/* ======================================================
   🔹 FETCH STUDENT INFORMATION
====================================================== */
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ======================================================
   🔹 FETCH ALL AVAILABLE MEDICAL TESTS
====================================================== */
$all_tests = [];
$result = $conn->query("SELECT * FROM medical_tests ORDER BY test_name ASC");
while ($row = $result->fetch_assoc()) {
    $all_tests[] = $row;
}

/* ======================================================
   🔹 FETCH STUDENT'S COMPLETED TESTS
====================================================== */
$taken_ids = [];
$stmt = $conn->prepare("
    SELECT st.test_id, mt.test_name 
    FROM student_tests st
    JOIN medical_tests mt ON st.test_id = mt.test_id
    WHERE st.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $taken_ids[] = $row['test_id'];
}
$stmt->close();

/* ======================================================
   🔹 FETCH SCHEDULED TESTS (FILTER OUT COMPLETED ONES)
====================================================== */
$stmt = $conn->prepare("
    SELECT sn.*, u.name AS physician_name, mt.test_id
    FROM student_notifications sn
    LEFT JOIN users u ON sn.physician_user_id = u.user_id
    LEFT JOIN medical_tests mt ON sn.test_type = mt.test_name
    WHERE sn.student_id = ?
    AND sn.test_datetime >= ?
    ORDER BY sn.test_datetime ASC
");
$stmt->bind_param("is", $student_id, $currentDateTime);
$stmt->execute();

$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    if (!in_array($row['test_id'], $taken_ids)) {
        $notifications[] = $row;
    }
}

$stmt->close();

/* ======================================================
   🔹 FETCH LATEST MEDICAL CERTIFICATE
====================================================== */
$stmt = $conn->prepare("
    SELECT status, certificate_file, issued_date 
    FROM medical_certificate 
    WHERE student_id = ?
    ORDER BY certificate_id DESC LIMIT 1
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$certificate = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ======================================================
   🔹 COMPUTE PROGRESS
====================================================== */
$total_tests = count($all_tests);
$completed_tests = count($taken_ids);
$progress = $total_tests > 0 ? round(($completed_tests / $total_tests) * 100) : 0;

/* ======================================================
   🔹 FETCH CERTIFICATE REQUEST LOGS
====================================================== */
$logs = [];
$stmt = $conn->prepare("
    SELECT action, details, created_at
    FROM student_certificate_logs
    WHERE student_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
$stmt->close();

/* ======================================================
   🔹 CHECK IF STUDENT HAS PENDING OR APPROVED REQUEST
====================================================== */
$pending_request_exists = false;

$stmt = $conn->prepare("
    SELECT status 
    FROM certificate_requests
    WHERE student_id = ? 
    ORDER BY requested_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$last_request = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($last_request && strtolower($last_request['status']) !== 'rejected') {
    $pending_request_exists = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | MERS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f8f9fa;
    color: #3e1760;
    font-family: "Segoe UI", Tahoma, sans-serif;
}
.card-header-purple { background-color: #6a1b9a; color: #fff; font-weight: bold; }
.progress { height: 25px; margin-top: 10px; }
.progress-bar { background: linear-gradient(90deg,#9c27b0,#7b1fa2); color: #fff; text-align: center; }
.alert-floating {
    position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
    z-index: 2000; display: none; min-width: 300px; background-color: #ffc107;
    color: #212529; font-weight: bold; border-radius: 5px; text-align: center; padding: 10px;
}
.receipt-option {
    cursor: pointer;
    padding: 30px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-align: center;
}
.receipt-option:hover {
    border-color: #6a1b9a;
    background-color: #f8f9fa;
    transform: translateY(-5px);
}
.receipt-option i {
    font-size: 48px;
    color: #6a1b9a;
    margin-bottom: 15px;
}
#receiptPreview {
    max-width: 100%;
    max-height: 300px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    margin-top: 15px;
}
#videoPreview {
    width: 100%;
    max-height: 400px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
}
.camera-controls {
    margin-top: 15px;
}
</style>
</head>
<body class="bg-light">

<!-- Floating Alert -->
<div id="floatingAlert" class="alert alert-warning text-center alert-floating" role="alert"></div>

<!-- HEADER -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
    <div class="container">
        <div class="row w-100 align-items-center">
            <div class="col-3 col-md-3 text-start">
                <img src="../images/logo/logo.png" alt="Logo Left" class="img-fluid" style="max-height: 60px; height: auto;">
            </div>
            <div class="col-6 col-md-6 text-center">
                <h3 class="fw-bold text-primary mb-0">MERS</h3>
                <small class="text-muted d-none d-sm-inline">(Medical Electronic Records System)</small>
            </div>
            <div class="col-3 col-md-3 text-end">
                <img src="../images/logo/NOBG.png" alt="Logo Right" class="img-fluid" style="max-height: 60px; height: auto;">
            </div>
        </div>
    </div>
</nav>

<div class="container my-4">

    <!-- WELCOME & PROGRESS -->
    <div class="text-center mb-4">
        <?php
        $nameParts = explode(' ', trim($student['name'] ?? ''));
        $lastName = end($nameParts);
        $sex = strtolower($student['sex'] ?? '');
        $title = ($sex === 'male') ? 'Mr.' : (($sex === 'female') ? 'Ms.' : '');
        ?>
        <h4>Welcome, <?= $title ? $title . ' ' : '' ?><?= htmlspecialchars($lastName) ?>!</h4>
        <p>You have completed <strong><?= $completed_tests ?>/<?= $total_tests ?></strong> medical tests.</p>
        <div class="progress">
            <?php if ($completed_tests < $total_tests): ?>
                <div class="progress-bar" style="width: <?= $progress ?>%;"><?= $progress ?>%</div>
            <?php else: ?>
                <div class="progress-bar bg-success" style="width:100%;">All Tests Completed ✅</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MEDICAL CERTIFICATE -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header card-header-purple">📜 Medical Certificate</div>
        <div class="card-body">
            <?php if ($certificate): ?>
                <p><strong>Status:</strong>
                    <span class="badge bg-<?= strtolower($certificate['status']) === 'approved' ? 'success' : 'warning' ?>">
                        <?= htmlspecialchars($certificate['status']) ?>
                    </span>
                </p>
                <p><strong>Issued Date:</strong> <?= htmlspecialchars($certificate['issued_date']) ?></p>
                <?php if (!empty($certificate['certificate_file'])): ?>
                    <a href="../<?= htmlspecialchars($certificate['certificate_file']) ?>" target="_blank" class="btn btn-primary btn-sm">👁 View</a>
                    <a href="../<?= htmlspecialchars($certificate['certificate_file']) ?>" download class="btn btn-secondary btn-sm">⬇ Download</a>
                <?php else: ?>
                    <p><em>No certificate file uploaded yet.</em></p>
                <?php endif; ?>

                <?php if (strtolower($certificate['status']) !== 'approved'): ?>
                    <?php if (!$pending_request_exists): ?>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#receiptModal">
                            🔄 Request Certificate
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-sm mt-3" disabled>📅 Already Requested</button>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <p>No certificate record available.</p>
                <?php if (!$pending_request_exists): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#receiptModal">
                        🔄 Request Certificate
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-sm mt-3" disabled>📅 Already Requested</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- CERTIFICATE REQUEST LOGS -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header card-header-purple">📋 Certificate Request Logs</div>
        <div class="card-body">
            <?php if (!empty($logs)): ?>
                <ul class="list-group mb-2">
                    <li class="list-group-item">
                        <strong><?= ucfirst($logs[0]['action']) ?>:</strong>
                        <?= htmlspecialchars($logs[0]['details']) ?><br>
                        <small class="text-muted"><?= date('M d, Y h:i A', strtotime($logs[0]['created_at'])) ?></small>
                    </li>
                </ul>
                <?php if (count($logs) > 1): ?>
                    <button id="toggleLogs" class="btn btn-outline-primary btn-sm">📂 Show All</button>
                    <ul id="allLogs" class="list-group mt-2" style="display:none; max-height:200px; overflow-y:auto;">
                        <?php for ($i = 1; $i < count($logs); $i++): ?>
                            <li class="list-group-item">
                                <strong><?= ucfirst($logs[$i]['action']) ?>:</strong>
                                <?= htmlspecialchars($logs[$i]['details']) ?><br>
                                <small class="text-muted"><?= date('M d, Y h:i A', strtotime($logs[$i]['created_at'])) ?></small>
                            </li>
                        <?php endfor; ?>
                    </ul>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted mb-0">No logs available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- MEDICAL TESTS PROGRESS -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header card-header-purple">🧪 Medical Tests Progress</div>
        <div class="card-body">
            <?php if (!empty($all_tests)): ?>
                <ul class="list-group">
                    <?php foreach ($all_tests as $test): ?>
                        <?php $isTaken = in_array($test['test_id'], $taken_ids); ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <input type="checkbox" disabled <?= $isTaken ? 'checked' : '' ?> />
                                <?php if ($isTaken): ?>
                                    <a href="javascript:void(0);" onclick="openTestModal(<?= $test['test_id'] ?>)" class="text-primary fw-bold">
                                        <?= htmlspecialchars($test['test_name']) ?>
                                    </a> ✅
                                <?php else: ?>
                                    <?= htmlspecialchars($test['test_name']) ?> ❌
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No medical tests available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- SCHEDULED MEDICAL TESTS -->
    <?php if (!empty($notifications)): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header card-header-purple">🗓 Scheduled Medical Tests</div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($notifications as $note): ?>
                        <li class="list-group-item">
                            <strong>Test:</strong> <?= htmlspecialchars($note['test_type']) ?><br>
                            <strong>Scheduled On:</strong> <?= date('M d, Y \a\t h:i A', strtotime($note['test_datetime'])) ?><br>
                            <?php if ($note['deadline']): ?>
                                <strong>Deadline:</strong> <?= date('M d, Y', strtotime($note['deadline'])) ?><br>
                            <?php endif; ?>
                            <strong>Assigned by:</strong> Dr. <?= htmlspecialchars($note['physician_name']) ?><br>
                            <small class="text-muted">Sent on <?= date('M d, Y \a\t h:i A', strtotime($note['created_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mb-5">
        <a href="logout.php" class="btn btn-danger px-4">Logout</a>
    </div>

</div>

<!-- RECEIPT UPLOAD MODAL -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">📸 Upload Payment Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Choose Method -->
                <div id="chooseMethod">
                    <h6 class="text-center mb-4">Choose how to upload your receipt:</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="receipt-option" onclick="showCamera()">
                                <div style="font-size: 48px;">📷</div>
                                <h5 class="mt-3">Take Photo</h5>
                                <p class="text-muted mb-0">Use your camera</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="receipt-option" onclick="showUpload()">
                                <div style="font-size: 48px;">📁</div>
                                <h5 class="mt-3">Upload Image</h5>
                                <p class="text-muted mb-0">Choose from gallery</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Camera Capture -->
                <div id="cameraSection" style="display: none;">
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="backToChoose()">
                        ← Back
                    </button>
                    <video id="videoPreview" autoplay playsinline></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                    <div class="camera-controls text-center">
                        <button type="button" class="btn btn-primary btn-lg" id="captureBtn" onclick="capturePhoto()">
                            📸 Capture Photo
                        </button>
                    </div>
                </div>

                <!-- Step 3: Upload File -->
                <div id="uploadSection" style="display: none;">
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="backToChoose()">
                        ← Back
                    </button>
                    <form id="requestForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="receiptFile" class="form-label">Select Receipt Image:</label>
                            <input type="file" id="receiptFile" name="receipt_file" class="form-control" accept="image/*" required>
                        </div>
                        <img id="receiptPreview" src="#" alt="Receipt Preview" style="display:none;">
                    </form>
                </div>

                <!-- Step 4: Preview & Confirm -->
                <div id="previewSection" style="display: none;">
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="backToChoose()">
                        ← Retake
                    </button>
                    <h6 class="text-center mb-3">Preview your receipt:</h6>
                    <img id="capturedPreview" src="#" alt="Captured Receipt" style="max-width: 100%; border: 2px solid #dee2e6; border-radius: 10px;">
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-success btn-lg" onclick="submitReceipt()">
                            ✅ Submit Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FOR TEST DETAILS -->
<div class="modal fade" id="testModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Test Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">Loading...</div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let stream = null;
let capturedBlob = null;

function openTestModal(test_id) {
    $.get('../get_tests_details.php', { test_id: test_id }, function(data) {
        $('#modalContent').html(data);
        new bootstrap.Modal(document.getElementById('testModal')).show();
    });
}

// Toggle logs
const toggleBtn = document.getElementById('toggleLogs');
if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const allLogs = document.getElementById('allLogs');
        if (allLogs.style.display === 'none') {
            allLogs.style.display = 'block';
            toggleBtn.textContent = '🔼 Show Less';
        } else {
            allLogs.style.display = 'none';
            toggleBtn.textContent = '📂 Show All';
        }
    });
}

// Show camera section
async function showCamera() {
    document.getElementById('chooseMethod').style.display = 'none';
    document.getElementById('cameraSection').style.display = 'block';
    
    // Check if camera API is available
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Camera is not supported on this device. Please use the upload option instead.');
        backToChoose();
        return;
    }
    
    try {
        // Try with rear camera first
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: { ideal: 'environment' },
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        document.getElementById('videoPreview').srcObject = stream;
    } catch (err) {
        console.error('Camera error:', err);
        
        // If rear camera fails, try any camera
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: true 
            });
            document.getElementById('videoPreview').srcObject = stream;
        } catch (err2) {
            console.error('Camera error 2:', err2);
            
            // Check if it's a permission issue or HTTPS issue
            if (err2.name === 'NotAllowedError' || err2.name === 'PermissionDeniedError') {
                alert('Camera permission denied. Please allow camera access and try again, or use the upload option.');
            } else if (err2.name === 'NotFoundError') {
                alert('No camera found on this device. Please use the upload option.');
            } else if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                alert('Camera access requires HTTPS. Please use the upload option instead.');
            } else {
                alert('Unable to access camera: ' + err2.message + '\n\nPlease use the upload option instead.');
            }
            backToChoose();
        }
    }
}

// Show upload section
function showUpload() {
    document.getElementById('chooseMethod').style.display = 'none';
    document.getElementById('uploadSection').style.display = 'block';
}

// Back to choose method
function backToChoose() {
    // Stop camera if running
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    
    // Reset all sections
    document.getElementById('cameraSection').style.display = 'none';
    document.getElementById('uploadSection').style.display = 'none';
    document.getElementById('previewSection').style.display = 'none';
    document.getElementById('chooseMethod').style.display = 'block';
    
    // Reset form
    document.getElementById('receiptFile').value = '';
    document.getElementById('receiptPreview').style.display = 'none';
    capturedBlob = null;
}

// Capture photo from camera
function capturePhoto() {
    const video = document.getElementById('videoPreview');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    canvas.toBlob((blob) => {
        capturedBlob = blob;
        const url = URL.createObjectURL(blob);
        document.getElementById('capturedPreview').src = url;
        
        // Stop camera
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        
        // Show preview section
        document.getElementById('cameraSection').style.display = 'none';
        document.getElementById('previewSection').style.display = 'block';
    }, 'image/jpeg', 0.9);
}

// Handle file upload preview
document.getElementById('receiptFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('receiptPreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Submit receipt
function submitReceipt() {
    const formData = new FormData();
    
    if (capturedBlob) {
        // From camera
        formData.append('receipt_file', capturedBlob, 'receipt.jpg');
    } else {
        // From file upload
        const fileInput = document.getElementById('receiptFile');
        if (!fileInput.files[0]) {
            alert('Please select a file first.');
            return;
        }
        formData.append('receipt_file', fileInput.files[0]);
    }
    
    fetch('request_certificate.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const floatingAlert = document.getElementById('floatingAlert');
            floatingAlert.textContent = '🔄 ' + data.message;
            floatingAlert.style.display = 'block';
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('receiptModal')).hide();
            
            // Reload after 2 seconds
            setTimeout(() => location.reload(), 2000);
        } else {
            alert(data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('An unexpected error occurred. Please try again.');
    });
}

// Reset modal when closed
document.getElementById('receiptModal').addEventListener('hidden.bs.modal', function() {
    backToChoose();
});
</script>

</body>
</html>