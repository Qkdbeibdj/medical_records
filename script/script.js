

function loadPage(url) {
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Replace content of a container (make sure you have one in the HTML)
            const content = doc.body.innerHTML;
            document.querySelector('.main-content').innerHTML = content;

            // Re-run scripts if needed
            const scripts = doc.querySelectorAll("script");
            scripts.forEach(oldScript => {
                const newScript = document.createElement("script");
                if (oldScript.src) {
                    newScript.src = oldScript.src;
                } else {
                    newScript.textContent = oldScript.textContent;
                }
                document.body.appendChild(newScript);
            });
        })
        .catch(err => console.error("Failed to load page: ", err));
}

function filterStudentList() {
    const input = document.getElementById("studentSearchInput").value.toLowerCase();
    const list = document.getElementById("studentListDropdown");
    const items = list.getElementsByTagName("li");

    let found = false;

    for (let i = 0; i < items.length; i++) {
        const text = items[i].textContent.toLowerCase();
        if (text.includes(input)) {
            items[i].style.display = "";
            found = true;
        } else {
            items[i].style.display = "none";
        }
    }

    // Only show the dropdown if any match exists
    list.style.display = found ? "block" : "none";
}

// Show the dropdown when clicking the input
function showStudentList() {
    const list = document.getElementById("studentListDropdown");
    list.style.display = "block";
}

// When clicking outside the container, hide the dropdown
document.addEventListener("click", function (e) {
    const container = document.getElementById("ishiharaTestContainer");
    const list = document.getElementById("studentListDropdown");
    if (!container.contains(e.target)) {
        list.style.display = "none";
    }
});

function openUploadModal() {
    document.getElementById('uploadModal').classList.add('show');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.remove('show');
}

function openSettingsModal() {
    document.getElementById("settingsModal").style.display = "block";
  }

  function closeSettingsModal() {
    document.getElementById("settingsModal").style.display = "none";
  }
  
// Google Docs Medical Test
function startMedicalTest() {
    let loadingTab = window.open("about:blank", "_blank");
    loadingTab.document.write("<p>Loading medical test document...</p>");
    var scriptUrl = "https://script.google.com/macros/s/AKfycbyDLqjLoXhrpqmfewp_YRGKOz3fZ3uEXdIOx1DyWh5is8IOIUklfNNx49eKaKzO0vNe/exec?t=" + new Date().getTime();

    fetch(scriptUrl)
        .then(response => response.text())
        .then(newDocUrl => {
            if (newDocUrl.startsWith("http")) {
                loadingTab.location.href = newDocUrl;
            } else {
                loadingTab.document.write("<p>Error: Could not generate a new document.</p>");
            }
        })
        .catch(error => {
            console.error("Error fetching new document:", error);
            loadingTab.document.write("<p>Error generating document. Please try again.</p>");
        });
}

// Notification Fetcher
function fetchNotifications() {
    fetch("fetch_notifications.php")
        .then(response => response.json())
        .then(data => {
            document.getElementById("notificationCount").innerText = data.count;
            let dropdown = document.getElementById("notificationDropdown");
            dropdown.innerHTML = "";

            if (data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    let p = document.createElement("p");
                    p.innerHTML = notification;
                    dropdown.appendChild(p);
                });
            } else {
                dropdown.innerHTML = "<p>No new notifications</p>";
            }
        })
        .catch(error => console.error("Error fetching notifications:", error));
}

// Auto-refresh notifications
setInterval(fetchNotifications, 5000);
document.addEventListener("DOMContentLoaded", fetchNotifications);

// Notification Dropdown Toggle
function toggleNotificationDropdown() {
    var dropdown = document.getElementById("notificationDropdown");
    dropdown.classList.toggle("show");
}

// Global click handler for modals & dropdowns
window.onclick = function (event) {
    // Close settings modal
    var settingsModal = document.getElementById("settingsModal");
    if (event.target === settingsModal) {
        settingsModal.style.display = "none";
    }

    // Close notification dropdown
    if (!event.target.matches('.dropbtn')) {
        let dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) {
                dropdowns[i].classList.remove('show');
            }
        }
    }
};

// Highlight active menu on login
document.addEventListener("DOMContentLoaded", function () {
    const homeButton = document.getElementById('homeButton');
    const dashboardButton = document.getElementById('dashboardButton');

    if (homeButton && dashboardButton) {
        homeButton.classList.add('active');
        dashboardButton.classList.remove('active');

        dashboardButton.addEventListener('click', function () {
            dashboardButton.classList.add('active');
            homeButton.classList.remove('active');
        });
    }
});


document.addEventListener("DOMContentLoaded", function() {
    // Get all the navigation links
    const navLinks = document.querySelectorAll('.nav-links ul li a');
    
    // Loop through each link
    navLinks.forEach(link => {
        // Check if the current link's href matches the current URL
        if (window.location.href.includes(link.href)) {
            link.classList.add('active'); // Add the active class if it matches
        } else {
            link.classList.remove('active'); // Remove the active class if it doesn't match
        }
    });
});


document.addEventListener("DOMContentLoaded", function() {
    const modal = document.querySelector('.modal');
    const modalContent = modal.querySelector('.modal-content');
    const openModalButton = document.querySelector('.open-modal-button');  // Button that opens the modal
    const closeButton = document.querySelector('.close');

    // Function to open the modal
    function openModal() {
        // Get button position relative to the viewport
        const buttonRect = openModalButton.getBoundingClientRect();
        
        // Adjust modal position directly below the button
        modal.style.top = `${buttonRect.bottom + window.scrollY + 10}px`;  // 10px gap below the button
        modal.style.left = `${buttonRect.left + window.scrollX}px`;  // Align left with the button

        // Show modal
        modal.style.display = "block";
    }

    // Close modal
    closeButton.addEventListener('click', function() {
        modal.style.display = "none";
    });

    // Close modal if clicked outside
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });

    // Check current page and adjust modal size accordingly
    if (window.location.href.includes("home.php")) {
        modal.classList.add('home-modal');
        modal.classList.remove('dashboard-modal');
    } else if (window.location.href.includes("dashboard.php")) {
        modal.classList.add('dashboard-modal');
        modal.classList.remove('home-modal');
    }

    // Attach openModal function to your button
    openModalButton.addEventListener('click', openModal);
});
function toggleNav() {
    const navLinks = document.querySelector('.nav-links ul');
    navLinks.style.display = (navLinks.style.display === 'flex') ? 'none' : 'flex';
}

// Load student list into the modal when it is shown
function loadStudentList() {
    const studentList = document.getElementById('studentList');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_students.php', true); // Fetch students
    xhr.onload = function() {
        if (xhr.status === 200) {
            studentList.innerHTML = xhr.responseText;
            const studentBtns = document.querySelectorAll('.student-select');
            studentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-student-id');
                    loadMedicalTests(studentId);  // Load medical tests for the selected student
                    // Close the modal after selecting a student
                    const studentSelectModal = new bootstrap.Modal(document.getElementById('studentSelectModal'));
                    studentSelectModal.hide();
                });
            });
        }
    };
    xhr.send();
}

// Initialize the modal and load the student list when it's opened
var studentSelectModal = document.getElementById('studentSelectModal');
studentSelectModal.addEventListener('show.bs.modal', function () {
    loadStudentList(); // Load the student list when the modal is shown
});
// Open the Student Test Modal
function openStudentTestModal() {
    const modal = document.getElementById('studentTestModal');
    modal.style.display = 'block'; // Show the modal
    loadStudentDropdown();
    loadTestDropdown();
}

// Close the Student Test Modal
function closeStudentTestModal() {
    const modal = document.getElementById('studentTestModal');
    modal.style.display = 'none'; // Hide the modal
}

// Load students into the dropdown
function loadStudentDropdown() {
    const studentSelect = document.getElementById('studentSelect');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_students.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const students = JSON.parse(xhr.responseText);
            students.forEach(student => {
                const option = document.createElement('option');
                option.value = student.student_id;
                option.textContent = student.name;
                studentSelect.appendChild(option);
            });
        }
    };
    xhr.send();
}

// Load medical tests into the dropdown and disable already selected tests
function loadTestDropdown() {
    const testSelect = document.getElementById('testSelect');
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_medical_tests.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const tests = JSON.parse(xhr.responseText);
            const selectedTests = getSelectedTests(); // Get already selected tests

            // Clear previous options in the test dropdown
            testSelect.innerHTML = '';

            tests.forEach(test => {
                const option = document.createElement('option');
                option.value = test.test_id;
                option.textContent = test.test_name;

                // Disable the test if already selected
                if (selectedTests.includes(test.test_id)) {
                    option.disabled = true;
                    option.style.backgroundColor = '#f0f0f0'; // Optional: Style disabled options
                }

                testSelect.appendChild(option);
            });
        } else {
            console.error("Error fetching tests");
        }
    };
    xhr.send();
}

// Example: Get the list of already selected test IDs (could be dynamic from the database or session)
function getSelectedTests() {
    const selectedTests = []; // For now, empty array (replace with actual logic to track selected tests)
    return selectedTests;
}

function openTestDetailsModal() {
    const testSelect = document.getElementById('testSelect');
    const selectedTestId = testSelect.value;

    if (selectedTestId) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `get_test_details.php?test_id=${selectedTestId}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const testDetails = JSON.parse(xhr.responseText);
                
                // Create and show dynamic modal
                showTestInputModal(testDetails);
            }
        };
        xhr.send();
    } else {
        alert("Please select a test");
    }
}
function showTestInputModal(testDetails) {
    // Remove any existing modal
    const existingModal = document.getElementById('dynamicTestModal');
    if (existingModal) existingModal.remove();

    // Create modal structure
    const modal = document.createElement('div');
    modal.id = 'dynamicTestModal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content neumorphic">
                <div class="modal-header">
                    <h5 class="modal-title">${testDetails.test_name}</h5>
                    <button type="button" class="btn-close" onclick="document.getElementById('dynamicTestModal').remove()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>${testDetails.description}</p>
                    <form id="testInputForm">
                        ${generateInputFields(testDetails.fields)}
                        <br>
                        <button type="submit" class="btn btn-success">Submit Test</button>
                    </form>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    // Optional: Add form submission logic here
}


// Close the Test Details Modal
function closeTestDetailsModal() {
    const modal = document.getElementById('testDetailsModal');
    modal.style.display = 'none'; // Hide the modal
}
function showTestInputModal(testDetails) {
// Remove any existing modal
const existingModal = document.getElementById('dynamicTestModal');
if (existingModal) existingModal.remove();

// Create modal structure
const modal = document.createElement('div');
modal.id = 'dynamicTestModal';
modal.className = 'modal';
modal.innerHTML = `
<div class="modal-dialog">
    <div class="modal-content neumorphic">
        <div class="modal-header">
            <h5 class="modal-title">${testDetails.test_name}</h5>
            <button type="button" class="btn-close" onclick="document.getElementById('dynamicTestModal').remove()">&times;</button>
        </div>
        <div class="modal-body">
            <p>${testDetails.description}</p>
            <form id="testInputForm">
                ${generateInputFields(testDetails.fields)}
                <br>
                <button type="submit" class="btn btn-success">Submit Test</button>
            </form>
        </div>
    </div>
</div>
`;

document.body.appendChild(modal);
modal.style.display = 'block'; // <<< this is the fix: make it visible

// Optional: Add form submission logic here later
}
function openTestDetailsModal() {
    const testId = document.getElementById('testSelect').value;
    const modal = document.getElementById(`testModal_${testId}`);
    if (modal) {
        modal.style.display = "block";
    }
}

function closeTestModal(testId) {
    const modal = document.getElementById(`testModal_${testId}`);
    if (modal) {
        modal.style.display = "none";
    }
}

// Function to open the Upload Certificate Modal
function openUploadCertificateModal() {
    document.getElementById('uploadCertificateModal').style.display = 'block';
}

// Function to close the Upload Certificate Modal
function closeUploadCertificateModal() {
    document.getElementById('uploadCertificateModal').style.display = 'none';
}


