<?php
session_start();
include '../includes/db_connect.php';

// Fetch pending certificate requests
$query = "
    SELECT 
        cr.request_id,
        s.name AS student_name,
        s.student_number,
        cr.requested_at
    FROM certificate_requests cr
    JOIN students s ON cr.student_id = s.student_id
    WHERE cr.status = 'pending'
    ORDER BY cr.requested_at DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate Requests</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
body { font-family: Arial, sans-serif; padding:20px; }
table { border:1px solid #ccc; width:100%; border-collapse: collapse; }
th, td { text-align:left; padding: 8px; }
th { background:#f0f0f0; }
button { border:none; padding:5px 10px; border-radius:4px; cursor:pointer; color:#fff; }
.btn-approve { background:#4CAF50; }
.btn-approve:hover { background:#45a049; }
.btn-reject { background:#f44336; }
.btn-reject:hover { background:#d32f2f; }

/* Modal styles */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); }
.modal-content { background:#fff; margin:10% auto; padding:20px; border-radius:8px; max-width:400px; }
.modal-content label { display:block; margin-top:10px; }
.modal-content input { width:100%; padding:8px; margin-top:5px; }
.modal-content button { margin-top:15px; }
.close { float:right; cursor:pointer; font-weight:bold; }
</style>
</head>
<body>

<h2>Pending Certificate Requests</h2>

<table>
<thead>
<tr>
    <th>Student Number</th>
    <th>Student Name</th>
    <th>Request Date</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr id="row-<?= $row['request_id'] ?>">
        <td><?= htmlspecialchars($row['student_number']) ?></td>
        <td><?= htmlspecialchars($row['student_name']) ?></td>
        <td><?= date('M d, Y h:i A', strtotime($row['requested_at'])) ?></td>
        <td>
            <button class="btn-approve" data-id="<?= $row['request_id'] ?>">Approve</button>
            <button class="btn-reject" data-id="<?= $row['request_id'] ?>">Reject</button>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="4">No pending requests.</td></tr>
<?php endif; ?>
</tbody>
</table>

<script>
$(document).ready(function(){

    // Open approve modal
    $('.btn-approve').click(function(){
        var id = $(this).data('id');
        $('#modal_request_id').val(id);
        $('#approveModal').show();
    });

    // Submit approve form via AJAX
    $('#approveForm').submit(function(e){
        e.preventDefault();
        $.post('approve_request.php', $(this).serialize(), function(response){
            alert(response);
            var id = $('#modal_request_id').val();
            $('#row-'+id).fadeOut();
            $('#approveModal').hide();
        });
    });

    // Reject request
    $('.btn-reject').click(function(){
        var id = $(this).data('id');
        if(confirm("Reject this request?")){
            $.post('reject_request.php', {id: id}, function(response){
                alert(response);
                $('#row-'+id).fadeOut();
            });
        }
    });

});
</script>

</body>
</html>
