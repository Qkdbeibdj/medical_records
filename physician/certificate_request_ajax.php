<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "⚠️ Not logged in.";
    exit();
}

// Fetch pending certificate requests with uploaded receipt
$requests = $conn->query("
    SELECT cr.request_id, s.name AS student_name, s.student_number, cr.requested_at, cr.receipt_file
    FROM certificate_requests cr
    JOIN students s ON cr.student_id = s.student_id
    WHERE cr.status = 'pending'
    ORDER BY cr.requested_at DESC
");

if ($requests->num_rows > 0):
?>
<table style="width:100%; border-collapse: collapse;">
    <tr>
        <th>Student</th>
        <th>Requested At</th>
        <th>Receipt</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $requests->fetch_assoc()): ?>
    <tr data-request-id="<?= htmlspecialchars($row['request_id']) ?>">
        <td><?= htmlspecialchars($row['student_number'].' - '.$row['student_name']) ?></td>
        <td><?= htmlspecialchars($row['requested_at']) ?></td>
        <td>
            <?php if (!empty($row['receipt_file'])): ?>
                <button class="btn-view-receipt" 
                        data-receipt="<?= htmlspecialchars(basename($row['receipt_file'])) ?>">
                    View Receipt
                </button>
            <?php else: ?>
                No receipt uploaded
            <?php endif; ?>
        </td>
        <td>
            <button class="btn-approve" data-id="<?= htmlspecialchars($row['request_id']) ?>" disabled>Approve</button>
            <button class="btn-reject" data-id="<?= htmlspecialchars($row['request_id']) ?>" disabled>Reject</button>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Receipt Modal -->
<div id="receiptModalBackdrop" class="modal-backdrop hidden"></div>
<div id="receiptModal" class="modal-container hidden">
    <div class="modal-content">
        <h3 class="modal-title">Student Uploaded Receipt</h3>
        <img id="receiptImage" src="" alt="Receipt" style="width:100%; max-height:500px; object-fit:contain; margin-top:1rem;">
        <div class="modal-actions">
            <button id="closeReceiptBtn" class="btn-secondary">Close</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let viewedRequest = null;

// Open receipt modal
$(document).on('click', '.btn-view-receipt', function() {
    const receiptSrc = $(this).data('receipt');
    viewedRequest = $(this).closest('tr').data('request-id');

    $('#receiptImage').attr('src', '../uploads/receipts/' + receiptSrc);
    $('#receiptModal, #receiptModalBackdrop').fadeIn(150).removeClass('hidden').css('display', 'flex');

    // Enable Approve/Reject buttons
    $(this).closest('td').siblings().find('.btn-approve, .btn-reject').prop('disabled', false);
});

// Close modal
$('#closeReceiptBtn, #receiptModalBackdrop').click(function() {
    $('#receiptModal, #receiptModalBackdrop').fadeOut(150, function() { $(this).addClass('hidden'); });
});

// Ensure physician views receipt before approving/rejecting
$(document).on('click', '.btn-approve, .btn-reject', function(e) {
    const requestId = $(this).data('id');
    if (viewedRequest !== requestId) {
        alert("⚠️ Please view the uploaded receipt before approving/rejecting!");
        e.preventDefault();
        return false;
    }
});
</script>

<style>
/* Modal Styles */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 50;
    display: none;
}
.modal-container {
    position: fixed;
    top: 50px; /* move it lower than the very top */
    left: 0;
    right: 0;
    display: none;
    z-index: 60;
    align-items: flex-start; /* align to top instead of center */
    justify-content: center;
    padding: 1rem;
}

.modal-content {
    background: #fff;
    border-radius: 1rem;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    margin-top: -50px; /* negative margin to overlap header */
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
    gap: 0.5rem;
}
.btn-primary, .btn-secondary {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    border: none;
    cursor: pointer;
}
.btn-primary { background-color:#2563eb; color:white; }
.btn-secondary { background-color:#f3f4f6; color:#374151; }
.hidden { display:none; }
</style>

<?php
else:
    echo '<p>No pending certificate requests.</p>';
endif;
?>
