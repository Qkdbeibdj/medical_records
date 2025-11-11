function debugOpenIshiharaTest() {
    const modal = document.getElementById("testModal_ishihara");
    modal.style.display = "block";
}

function closeIshiharaTestModal() {
    document.getElementById("testModal_ishihara").style.display = "none";
}

let ishiharaQuestions = [];
let currentIndex = 0;
let studentAnswers = {};

function loadIshiharaQuestions() {
    const studentId = document.getElementById("ishiharaStudentSelect").value;
    console.log("🚀 Loading plates for student ID:", studentId);

    const container = document.getElementById('testQuestionsContainer');
    if (!studentId) {
        container.innerHTML = '<p>Please select a student to start the test.</p>';
        return;
    }

    container.innerHTML = '<p>Loading test...</p>';

    fetch('load_ishihara_questions.php?student_id=' + studentId)
        .then(res => res.json())
        .then(data => {
            console.log("✅ Plates loaded:", data);
            ishiharaQuestions = data;
            currentIndex = 0;
            studentAnswers = {};
            showCurrentPlate();
        })
        .catch(err => {
            console.error("❌ Error loading questions:", err);
            container.innerHTML = '<p>Error loading test questions.</p>';
        });
}

function showCurrentPlate() {
    const plate = ishiharaQuestions[currentIndex];
    if (!plate) return;

    const imageUrl = `/medical_records/${plate.image_path}`;
    const stepText = `Plate ${currentIndex + 1} of ${ishiharaQuestions.length}`;

    const container = document.getElementById('testQuestionsContainer');
    
    // Fade out before update
    container.style.opacity = 0;

    setTimeout(() => {
        container.innerHTML = `
          <div class="text-center fade-in">
            <div style="font-weight: bold; margin-bottom: 10px;">${stepText}</div>

            <img 
              src="${imageUrl}" 
              alt="Ishihara Plate" 
              style="
                width: 420px;
                height: 420px;
                object-fit: contain;
                display: block;
                margin: 20px auto;
                border-radius: 20px;
                background: white;
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
              "
            />

            <div class="form-group mt-4">
              <label for="answerInput" style="display: block; font-weight: bold; margin-bottom: 10px;">What number do you see?</label>

              <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                <button 
                  class="btn btn-secondary" 
                  ${currentIndex === 0 ? 'disabled' : ''} 
                  onclick="prevPlate()"
                >← Prev</button>

                <input 
                  type="number" 
                  id="answerInput" 
                  class="form-control text-center" 
                  value="${studentAnswers[plate.id] || ''}" 
                  style="width: 180px; margin: 0 10px;"
                />

                ${currentIndex === ishiharaQuestions.length - 1
                  ? '<button class="btn btn-success" onclick="submitIshiharaAnswers()">Submit</button>'
                  : '<button class="btn btn-primary" onclick="nextPlate()">Next →</button>'
                }
              </div>
            </div>
          </div>
        `;

        // Refocus input
        setTimeout(() => {
            container.style.opacity = 1;
            const answerInput = document.getElementById("answerInput");
            if (answerInput) {
                answerInput.focus();

                // Attach enter key handler
                answerInput.addEventListener("keydown", function (e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        if (currentIndex === ishiharaQuestions.length - 1) {
                            submitIshiharaAnswers();
                        } else {
                            nextPlate();
                        }
                    }
                });
            }
        }, 50);
    }, 150); // Fade delay
}


function nextPlate() {
    const currentId = ishiharaQuestions[currentIndex].id;
    const answer = document.getElementById("answerInput").value.trim();
    studentAnswers[currentId] = answer;

    if (currentIndex < ishiharaQuestions.length - 1) {
        currentIndex++;
        showCurrentPlate();
    }
}

function prevPlate() {
    const currentId = ishiharaQuestions[currentIndex].id;
    const answer = document.getElementById("answerInput").value.trim();
    studentAnswers[currentId] = answer;

    if (currentIndex > 0) {
        currentIndex--;
        showCurrentPlate();
    }
}

function submitIshiharaAnswers() {
    const studentId = document.getElementById("ishiharaStudentSelect").value;

    // Save final answer
    const currentId = ishiharaQuestions[currentIndex].id;
    studentAnswers[currentId] = document.getElementById("answerInput").value.trim();

    // Calculate score
    let score = 0;
    ishiharaQuestions.forEach(q => {
        if (studentAnswers[q.id] && studentAnswers[q.id].toString() === q.correct_answer.toString()) {
            score++;
        }
    });

    const passed = score >= 10;
    
    // ✅ INSERT HERE
    const formData = new FormData();
    formData.append("student_id", studentId);
    formData.append("user_answers_json", JSON.stringify(studentAnswers));  // <-- this sends the answers
    formData.append("score", score);
    formData.append("passed", passed ? 1 : 0);

    // Now send the request
    fetch("submit_ishihara_debug.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(msg => {
        alert(`Test submitted. Score: ${score}. ${passed ? 'Passed' : 'Failed'}.\nServer: ${msg}`);
           setTimeout(() => {
                const container = document.getElementById("testModalsContainer");
                if (container) container.innerHTML = "";
                location.reload();
            }, 1000);
    })
    .catch(error => {
        alert('Error submitting answers: ' + error);
    });
    
}

document.addEventListener("DOMContentLoaded", function() {
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const detailId = e.target.getAttribute('data-id');
            const row = document.getElementById('detail-' + detailId);

            // Get the student id from the detailId (using the first part before '-')
            const studentId = detailId.split('-')[0];

            // Find all the rows for the same student
            const studentRows = document.querySelectorAll(`#detail-${studentId}-`);
            
            // Hide all other detail rows for this student
            studentRows.forEach(studentRow => {
                if (studentRow !== row) {
                    studentRow.style.display = 'none'; // Hide other rows
                }
            });

            // Toggle the visibility of the current clicked row
            if (row.style.display === 'none' || row.style.display === '') {
                row.style.display = 'table-row';  // Show the current row
            } else {
                row.style.display = 'none';  // Hide the current row
            }
        });
    });
});
function openIshiharaTestModal() {
    const modalHTML = testTemplates["6"];
    const testModalsContainer = document.getElementById("testModalsContainer");

    if (modalHTML) {
        testModalsContainer.innerHTML = modalHTML;

        setTimeout(() => {
            const modal = document.getElementById("testModal_ishihara");
            const studentDropdown = document.getElementById("ishiharaStudentSelect"); // fixed ID

            if (modal && studentDropdown) {
                modal.style.display = "flex";

                studentDropdown.addEventListener("change", loadIshiharaQuestions);

                if (studentDropdown.value) {
                    loadIshiharaQuestions(); // auto-load if already selected
                }
            }
        }, 50);
    }
}

function closeIshiharaTestModal() {
    const hasStarted = Object.keys(studentAnswers).length > 0;

    if (hasStarted) {
        const confirmClose = confirm("⚠️ You have started the test. Closing will discard all progress. Continue?");
        if (!confirmClose) return;
    }

    // ✅ Clear modal
    const container = document.getElementById("testModalsContainer");
    if (container) container.innerHTML = "";

    // ✅ Quick refresh
    location.reload();
}


function startIshiharaTest() {
    // Here you can load the actual test plates or logic
    alert("Starting Ishihara Test...");

    // Optionally fetch the student_id and begin test logic
    const studentId = document.getElementById("ishiharaStudentSelect").value;
    if (!studentId) {
        alert("No student selected.");
        return;
    }

    // Proceed with test logic (e.g., AJAX load plates, set timer, etc.)
    // You can also use a form to POST the test start data
}
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        e.preventDefault(); // Disable ESC close
    }
});

function filterByResult(resultType) {
    fetch(`filter_certificates.php?result=${resultType}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('certificate-body');
            tbody.innerHTML = '';

            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.student_number}</td>
                    <td>${row.name}</td>
                    <td>${new Date(row.generated_at).toLocaleDateString()}</td>
                `;

                tr.addEventListener('click', () => showTestDetails(row));
                tbody.appendChild(tr);
            });
        });
}

function filterStudents() {
    const input = document.getElementById('searchStudent').value.toUpperCase();
    const rows = document.querySelectorAll('.student-row');

    // Hide all test link/details rows initially
    document.querySelectorAll('.test-links, .test-detail-row').forEach(row => {
        row.style.display = 'none';
    });

    rows.forEach(row => {
        const sid = row.getAttribute('data-id');
        const studentNumberCell = row.querySelector('td');
        const txtValue = studentNumberCell.textContent || studentNumberCell.innerText;

        if (txtValue.toUpperCase().indexOf(input) > -1) {
            row.style.display = "";
            const testLinkRow = document.getElementById("tests-" + sid);
            if (testLinkRow) testLinkRow.style.display = "none"; // Keep collapsed on filter
        } else {
            row.style.display = "none";
        }
    });
}

document.querySelectorAll('.test-detail-row').forEach(row => {
    row.style.display = 'none';
});


document.querySelectorAll('.test-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const modalId = button.getAttribute('data-modal-id');
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
        }
    });
});

document.querySelectorAll('.modal .close').forEach(span => {
    span.addEventListener('click', () => {
        const modalId = span.getAttribute('data-modal-id');
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    });
});

// Close modal when clicking outside modal-content
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

function toggleNav() {
  const navLinks = document.getElementById('nav-links');
  navLinks.classList.toggle('show');
}



function loadAvailableTests() {
    const studentId = document.getElementById('studentSelect').value;
    const testSelect = document.getElementById('testSelect');

    // Clear the dropdown
    testSelect.innerHTML = '<option value="">--Select a Test--</option>';

    if (!studentId) return;

    fetch(`get_available_tests.php?student_id=${studentId}`)
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data)) {
                data.forEach(test => {
                    const option = document.createElement('option');
                    option.value = test.test_id;
                    option.textContent = test.test_name;
                    testSelect.appendChild(option);
                });
            } else {
                console.error('Expected an array of tests but got:', data);
            }
        })
        .catch(err => console.error('Error loading tests:', err));
}

const testTemplates = {
    // General Check-up Modal
    "1": `
        <div id="testModal_1" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">General Check-up</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('1')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="save_test_data.php" method="POST" id="form_test_1">

                            <!-- Vitals -->
                            <label>BP:</label><input type="text" name="bp" required><br>
                            <label>HR:</label><input type="text" name="hr" required><br>
                            <label>RR:</label><input type="text" name="rr" required><br>
                            <label>O2 Saturation:</label><input type="text" name="o2_sat" required><br>
                            <label>Temperature:</label><input type="text" name="temperature" required><br>
                            
                            <!-- Subjective -->
                            <label>Subjective Complaints:</label><textarea name="subjective" required></textarea><br>

                            <!-- Past History -->
                            <label>Past Medical History (check all that apply):</label><br>
                            <input type="checkbox" name="past_history[]" value="Heart Disease"> Heart Disease<br>
                            <input type="checkbox" name="past_history[]" value="Asthma/Allergy/Skin"> Asthma/Allergy/Skin<br>
                            <input type="checkbox" name="past_history[]" value="Kidney Disease"> Kidney Disease<br>
                            <input type="checkbox" name="past_history[]" value="Diabetes/Thyroid"> Diabetes/Thyroid<br>
                            <input type="checkbox" name="past_history[]" value="Cancer"> Cancer<br>
                            <label>Others / Hospitalizations / Pregnancy:</label><textarea name="past_history_others"></textarea><br>

                            <!-- Family History -->
                            <label>Family History (check all that apply):</label><br>
                            <input type="checkbox" name="family_history[]" value="Hypertension"> Hypertension<br>
                            <input type="checkbox" name="family_history[]" value="Diabetes"> Diabetes<br>
                            <input type="checkbox" name="family_history[]" value="Allergy"> Allergy<br>
                            <input type="checkbox" name="family_history[]" value="Cancer"> Cancer<br>
                            <input type="checkbox" name="family_history[]" value="Heart Disease"> Heart Disease<br>
                            <label>Others:</label><textarea name="family_history_others"></textarea><br>

                            <!-- Physical Exam -->
                            <label>Physical Examination (Check if remarkable):</label><br>
                            <input type="checkbox" name="physical_exam[]" value="General"> General<br>
                            <input type="checkbox" name="physical_exam[]" value="Neuro"> Neuro<br>
                            <input type="checkbox" name="physical_exam[]" value="Skin"> Skin<br>
                            <input type="checkbox" name="physical_exam[]" value="EENT"> EENT<br>
                            <input type="checkbox" name="physical_exam[]" value="CVS"> CVS<br>
                            <input type="checkbox" name="physical_exam[]" value="Respiratory"> Respiratory<br>
                            <input type="checkbox" name="physical_exam[]" value="GIT"> GIT<br>
                            <input type="checkbox" name="physical_exam[]" value="GUT"> GUT<br>
                            <input type="checkbox" name="physical_exam[]" value="Musculoskeletal"> Musculoskeletal<br>
                            <input type="checkbox" name="physical_exam[]" value="Genitalia"> Genitalia<br>
                            <label>Other remarkable findings:</label><textarea name="physical_exam_others"></textarea><br>

                            <!-- Assessment -->
                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="">-- Select Assessment --</option>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>

                            <button type="submit" class="btn btn-success">Submit General Check-up</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    `,

    // Blood Typing Modal
    // Blood Typing Modal
    "2": `
        <div id="testModal_2" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">Blood Typing</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('2')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="save_test_data.php" method="POST" id="form_test_2">
                            <label>Blood Type:</label>
                            <select name="blood_type" required>
                                <option value="">-- Select Blood Type --</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select><br>
                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="">-- Select Assessment --</option>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>
                            <button type="submit" class="btn btn-success">Submit Blood Typing</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `,

    // Chest X-ray Modal
    "3": `
        <div id="testModal_3" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">Chest X-ray</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('3')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="save_test_data.php" method="POST" id="form_test_3">
                            
                            <label>Lungs/Pleural Cavity Findings:</label>
                            <select name="lungs_findings" id="lungs_findings_3" required>
                                <option value="">-- Select --</option>
                                <option value="Normal">Normal</option>
                                <option value="For Referral">For Referral</option>
                            </select>
                            <div id="lungs_note_3" class="note"></div><br>

                            <label>Heart/Mediastinum Findings:</label>
                            <select name="heart_findings" id="heart_findings_3" required>
                                <option value="">-- Select --</option>
                                <option value="Normal">Normal</option>
                                <option value="For Referral">For Referral</option>
                            </select>
                            <div id="heart_note_3" class="note"></div><br>

                            <label>Bones Findings:</label>
                            <select name="bones_findings" id="bones_findings_3" required>
                                <option value="">-- Select --</option>
                                <option value="Normal">Normal</option>
                                <option value="For Referral">For Referral</option>
                            </select>
                            <div id="bones_note_3" class="note"></div><br>

                            <label>Impression:</label>
                            <textarea name="impression" required></textarea><br>

                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="">-- Select Assessment --</option>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>

                            <button type="submit" class="btn btn-success">Submit Chest X-ray</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const addReferralNote = (selectId, noteId) => {
                    const select = document.getElementById(selectId);
                    const note = document.getElementById(noteId);

                    if (!select || !note) return;

                    select.addEventListener("change", () => {
                        if (select.value === "For Referral") {
                            note.textContent = "⚠️ Note: For Referral";
                            note.style.color = "red";
                            note.style.fontWeight = "bold";
                        } else {
                            note.textContent = "";
                        }
                    });
                };

                addReferralNote("lungs_findings_3", "lungs_note_3");
                addReferralNote("heart_findings_3", "heart_note_3");
                addReferralNote("bones_findings_3", "bones_note_3");
            });
        </script>
    `,

    // Basic Hearing Screening Modal
    "4": `
        <div id="testModal_4" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">Basic Hearing Screening</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('4')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="save_test_data.php" method="POST" id="form_test_4">
                            <label>Hearing Result:</label>
                            <select name="hearing_result" required>
                                <option value="normal">Normal Hearing</option>
                                <option value="referral">For Referral</option>
                            </select><br>
                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="">-- Select Assessment --</option>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>
                            <button type="submit" class="btn btn-success">Submit Hearing Test</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `,
    // Drug Test Modal
    "5": `
        <div id="testModal_5" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">Drug Test</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('5')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="save_test_data.php" method="POST" id="form_test_5">
                            <label>THC Result:</label>
                            <select name="thc_result" id="thc_result_5" required>
                                <option value="negative">Negative</option>
                                <option value="positive">Positive</option>
                            </select><br>
                            <label>Methamphetamine Result:</label>
                            <select name="meth_result" id="meth_result_5" required>
                                <option value="negative">Negative</option>
                                <option value="positive">Positive</option>
                            </select><br>
                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="">-- Select Assessment --</option>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>
                            <button type="submit" class="btn btn-success">Submit Drug Test</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `,

};


document.addEventListener("DOMContentLoaded", () => {
    const testSelect = document.getElementById("testSelect");
    const testModalsContainer = document.getElementById("testModalsContainer");

    testSelect.addEventListener("change", () => {
        const selectedTestId = testSelect.value.trim();
        testModalsContainer.innerHTML = ""; // Clear previous modal
        

        const modalHTML = testTemplates[selectedTestId];
        if (modalHTML) {
            testModalsContainer.innerHTML = modalHTML;

            const modal = document.getElementById(`testModal_${selectedTestId}`);
            if (modal) {
                modal.style.display = "flex";

                const form = document.getElementById(`form_test_${selectedTestId}`);
                if (form) {
                    form.addEventListener("submit", function (e) {
                        e.preventDefault();
                        
                        const formData = new FormData(form); // Get all form data

                        // Add student_id and test_id
                        formData.append('student_id', document.getElementById('studentSelect').value);
                        formData.append('test_id', selectedTestId);
                        formData.append('action', 'submit_test_data');

                        // Send data to save in the database
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'save_test_data.php', true);
                        xhr.onload = function () {
                            if (xhr.status === 200) {
                                alert('Test data saved successfully!');
                                // ✅ Close both modals
                                    const testModal = document.getElementById(`testModal_${selectedTestId}`);
                                    if (testModal) testModal.style.display = "none";
                                    const startTestModal = document.getElementById("studentTestModal");

                                    if (startTestModal) startTestModal.style.display = "none";

                                    // ✅ Refresh the page after a short delay
                                    setTimeout(() => {
                                        location.reload();
                                    }, 100);// Adjust delay as needed
                            } else {
                                alert('Error saving test data!');
                            }
                        };
                        xhr.send(formData); // Send form data
                    });
                }
            }
        } else {
            console.warn("No template found for test ID:", selectedTestId);
        }
    });
});


// Function to close the test modal
function closeTestModal(testId) {
    const modal = document.getElementById(`testModal_${testId}`);
    if (modal) {
        modal.style.display = 'none';
    }
}

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

function showStudentListCustom(input) {
    const ul = input.nextElementSibling;
    ul.style.display = "block";
}

function filterStudentListCustom(input) {
    const val = input.value.toLowerCase();
    const ul = input.nextElementSibling;
    let anyVisible = false;

    ul.querySelectorAll("li").forEach(li => {
        if (li.textContent.toLowerCase().includes(val)) {
            li.style.display = "";
            anyVisible = true;
        } else li.style.display = "none";
    });

    ul.style.display = anyVisible ? "block" : "none";
}

function selectStudentCustom(id, displayText, liElement) {
    const input = liElement.parentElement.previousElementSibling;
    input.value = displayText;

    const select = liElement.parentElement.nextElementSibling;
    select.innerHTML = `<option value="${id}" selected>${displayText}</option>`;

    liElement.parentElement.style.display = "none";
}