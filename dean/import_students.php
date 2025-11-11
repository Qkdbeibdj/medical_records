<?php
include '../includes/db_connect.php'; // Ensure database connection

if (isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (!$file) {
        echo json_encode(["status" => "error", "message" => "No file uploaded."]);
        exit;
    }

    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle); // Read headers
        $header[0] = preg_replace('/[\x{FEFF}]/u', '', $header[0]); // Fix BOM if present
    
        $imported = 0;
        $errors = 0;
    
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) < 9) {
                $errors++;
                continue;
            }
            
            // Extract data safely and trim whitespace
            $student_number = mysqli_real_escape_string($conn, trim($data[0]));
            $name = mysqli_real_escape_string($conn, trim($data[1]));
            $year_level = mysqli_real_escape_string($conn, trim($data[2]));
            $course = mysqli_real_escape_string($conn, trim($data[3]));
            $email = mysqli_real_escape_string($conn, trim($data[4]));
            $contact_number = mysqli_real_escape_string($conn, trim($data[5]));
            $birthdate = mysqli_real_escape_string($conn, trim($data[6]));
            $address = mysqli_real_escape_string($conn, trim($data[7]));
            $sex = mysqli_real_escape_string($conn, trim($data[8])); // Ensure sex is stored

            // Validate required fields
            if (empty($student_number) || empty($year_level) || empty($course) || empty($email) || empty($birthdate) || empty($sex)) {
                $errors++;
                continue;
            }

            // Validate birthdate format (MM/DD/YYYY or YYYY-MM-DD)
            if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$|^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
                $errors++;
                continue;
            }

            // ✅ Compute Age from Birthdate
            $birthDateObj = DateTime::createFromFormat('m/d/Y', $birthdate) ?: DateTime::createFromFormat('Y-m-d', $birthdate);
            if (!$birthDateObj) {
                $errors++;
                continue;
            }

            $today = new DateTime();
            $age = $today->diff($birthDateObj)->y; // Calculate age

            // Check if student already exists in students table
            $check_query = "SELECT student_id FROM students WHERE student_number = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("s", $student_number);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors++;
                error_log("Error importing student: " . json_encode($data));
                continue;
                }

            // Insert student into students table
            $query = "INSERT INTO students (student_number, name, year_level, course, email, contact_number, birthdate, age, address, sex) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssiss", $student_number, $name, $year_level, $course, $email, $contact_number, $birthdate, $age, $address, $sex);

            if ($stmt->execute()) {
                $imported++;

                // Ensure student is added to the users table with role 'student'
                $hashed_password = password_hash("1234", PASSWORD_DEFAULT); // Default password
                $role = "student"; // Ensure it matches ENUM values in the users table

                // Check if student already exists in users table using email
                $user_check = "SELECT user_id FROM users WHERE email = ?";
                $stmt = $conn->prepare($user_check);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 0) { // If student is not in users table, add them
                    $insert_user = "INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($insert_user);
                    $stmt->bind_param("sss", $email, $hashed_password, $role);
                    $stmt->execute();
                }

            } else {
                $errors++;
            }
        }
        fclose($handle);

        echo "<script>
            alert('Import complete: $imported student(s) added, $errors error(s).');
            window.location.href = 'dean_dashboard.php'; // Change this to your dashboard or upload page
        </script>";

    } else {
        echo json_encode(["status" => "error", "message" => "Failed to open file."]);
    }
}
?>
