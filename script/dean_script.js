
    // Open the modal
    function openImportCsvModal() {
        document.getElementById('importCsvModal').style.display = 'flex';
    }

    // Close the modal
    function closeImportCsvModal() {
        document.getElementById('importCsvModal').style.display = 'none';
    }

    // Handle form submission via AJAX
    document.getElementById('importCsvForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent normal form submission

        // Create FormData object to send the file via AJAX
        let formData = new FormData();
        formData.append('csv_file', document.getElementById('csvFileInput').files[0]);

        // Send AJAX request to import_students.php
        fetch('import_students.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Show the import result in the modal
            if (data.status === 'success') {
                alert(`✔ ${data.imported} records imported successfully.`);
            } else {
                alert(`❌ Error: ${data.message}`);
            }
            // Close modal after the process
            closeImportCsvModal();
        })
        .catch(error => {
            alert('❌ An error occurred during import.');
            closeImportCsvModal();
        });
    });

    // Get the hamburger icon and nav links
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const navLinks = document.querySelector('.nav-links ul');

    // Toggle the 'show' class when the hamburger icon is clicked
    hamburgerMenu.addEventListener('click', () => {
        navLinks.classList.toggle('show');
    });

        function toggleNav() {
            document.getElementById('menu-bar-container').classList.toggle('active');
        }
        var modal = document.getElementById("studentModal");
        var openModalBtn = document.querySelector(".add-student-btn");
        var closeModalBtn = document.querySelector(".close-btn");

    // Close modal when clicking outside the modal content
        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        }

        function fetchStudents() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_students.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.querySelector("#student-list tbody").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

    // Auto-refresh every 1 second
        setInterval(fetchStudents, 1000);

        function confirmDelete(student_id) {
            if (confirm("Are you sure you want to delete this student?")) {
                fetch('delete_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'student_id=' + student_id
                })
                .then(response => response.text())
                .then(data => {
                    if (data === "success") {
                        alert("Student deleted successfully!");
                        location.reload(); // Refresh the list instantly
                    } else {
                        alert("Error deleting student.");
                }
            })
            .catch(error => console.error("Error:", error));
        }
    }

        function openAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'flex';
}
function closeAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'none';
}

function openDeleteStudentModal() {
    document.getElementById('deleteStudentModal').style.display = 'flex';
}
function closeDeleteStudentModal() {
    document.getElementById('deleteStudentModal').style.display = 'none';
}

function openImportCsvModal() {
    document.getElementById('importCsvModal').style.display = 'flex';
}
function closeImportCsvModal() {
    document.getElementById('importCsvModal').style.display = 'none';
}

function openSettingsModal() {
    document.getElementById('settingsModal').style.display = 'flex';
}
function closeSettingsModal() {
    document.getElementById('settingsModal').style.display = 'none';
}

        document.querySelector('.hamburger-menu').addEventListener('click', function() {
            const navLinks = document.getElementById('nav-links');
            navLinks.classList.toggle('active');
        });
        function toggleNotificationDropdown() {
            const dropdown = document.querySelector(".dropdown");
            dropdown.classList.toggle("active");  // Toggle the dropdown visibility
        }
        // Add 'active' class to the clicked nav item
        document.querySelectorAll('.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                // Remove 'active' class from all links
                document.querySelectorAll('.nav-link').forEach(function(link) {
                    link.classList.remove('active');
                });
                // Add 'active' class to the clicked link
                link.classList.add('active');
            });
        });

        function openAddPhysicianModal() {
            document.getElementById('addPhysicianModal').style.display = 'flex';
        }
        function closeAddPhysicianModal() {
            document.getElementById('addPhysicianModal').style.display = 'none';
        }
         
        // Fetch all students on page load
        window.onload = function() {
            filterByResult(''); // Fetch all certificates initially
        };

// Function to filter students by result (passed, failed, conditional) or show all students
function filterByResult(result) {
    console.log("Filtering by:", result);  // Debugging line to check the result sent
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'filter_certificates.php?assessment=' + result, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var students = JSON.parse(xhr.responseText);
            var certificateBody = document.getElementById('certificate-body');
            certificateBody.innerHTML = ''; // Clear the table

            students.forEach(function(student) {
                const studentRow = document.createElement('tr');
                studentRow.setAttribute('data-student-id', student.student_id); // Add student ID as data attribute

                // Add the student data, including student name and test names
                studentRow.innerHTML = `
                    <td>${student.student_number}</td>
                    <td>${student.name}</td>
                    <td>${student.test_names.join(', ')}</td>  <!-- Display the test names -->
                `;
                certificateBody.appendChild(studentRow);
            });
        }
    };
    xhr.send();
}

        function loadTestsForStudent(studentId) {
            // Fetch the list of medical tests
            fetch('get_medical_tests.php')
                .then(response => response.json())
                .then(tests => {
                    // Find the select dropdown for the specific student
                    const select = document.querySelector(`#certificate-body tr[data-student-id='${studentId}'] select`);

                    // Reset the options
                    select.innerHTML = '<option value="">-- Select a test --</option>';

                    // Populate the dropdown with available tests
                    tests.forEach(test => {
                        const option = document.createElement('option');
                        option.value = test.test_id;
                        option.textContent = test.test_name;
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading medical tests:', error));
        }

        function showTestDetails(selectElement, studentId) {
            const testId = selectElement.value;

            if (!testId) {
                // Clear the test details if no test is selected
                const testDetailsColumn = selectElement.closest('tr').querySelector('.test-details-column');
                testDetailsColumn.innerHTML = ''; // Clear the details column
                return;
            }

            // Fetch the details of the selected test for this student
            fetch(`get_student_test_details.php?student_id=${studentId}&test_id=${testId}`)
                .then(response => response.json())
                .then(data => {
                    // Find the details column in the student's row
                    const testDetailsColumn = selectElement.closest('tr').querySelector('.test-details-column');

                    // Populate the test details
                    testDetailsColumn.innerHTML = `
                        <strong>Test Name:</strong> ${data.test_name ?? 'N/A'}<br>
                        <strong>Assessment:</strong> ${data.assessment ?? 'N/A'}<br>
                        ${data.bp ? `<strong>BP:</strong> ${data.bp}<br>` : ''}
                        ${data.hr ? `<strong>HR:</strong> ${data.hr}<br>` : ''}
                        ${data.rr ? `<strong>RR:</strong> ${data.rr}<br>` : ''}
                        ${data.blood_type ? `<strong>Blood Type:</strong> ${data.blood_type}<br>` : ''}
                        ${data.impression ? `<strong>Impression:</strong> ${data.impression}<br>` : ''}
                        ${data.hearing_result ? `<strong>Hearing Result:</strong> ${data.hearing_result}<br>` : ''}
                        ${data.thc_result ? `<strong>THC:</strong> ${data.thc_result}<br>` : ''}
                        ${data.meth_result ? `<strong>Meth:</strong> ${data.meth_result}<br>` : ''}
                    `;
                })
                .catch(error => {
                    console.error('Error fetching test details:', error);
                    // If there's an error, show a message in the details column
                    const testDetailsColumn = selectElement.closest('tr').querySelector('.test-details-column');
                    testDetailsColumn.innerHTML = '<span style="color: red;">Error loading test details.</span>';
                });
        }


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