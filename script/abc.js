// Test templates dictionary
const testTemplates = {
    "1": `
        <div id="testModal_1" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">General Check-up</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('1')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="form_test_1">
                            <label>BP:</label><input type="text" name="bp" required><br>
                            <label>HR:</label><input type="text" name="hr" required><br>
                            <label>RR:</label><input type="text" name="rr" required><br>
                            <label>O2 Saturation:</label><input type="text" name="o2_sat" required><br>
                            <label>Temperature:</label><input type="text" name="temperature" required><br>
                            <label>Subjective Complaints:</label><textarea name="subjective" required></textarea><br>
                            <label>Past Medical History:</label><textarea name="past_history"></textarea><br>
                            <label>Family History:</label><textarea name="family_history"></textarea><br>
                            <label>Physical Examination Findings:</label><textarea name="physical_exam"></textarea><br>
                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `,
    "2": `
        <div id="testModal_2" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">Blood Typing</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('2')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="form_test_2">
                            <label>Blood Type:</label>
                            <select name="blood_type" required>
                                <option value="">Select</option>
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                            </select><br>
                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `,
    "5": `
        <div id="testModal_5" class="modal">
            <div class="modal-dialog">
                <div class="modal-content neumorphic">
                    <div class="modal-header">
                        <h5 class="modal-title">Drug Test</h5>
                        <button type="button" class="btn-close" onclick="closeTestModal('5')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="form_test_5">
                            <label>THC Result:</label>
                            <select name="thc_result" required>
                                <option value="negative">Negative</option>
                                <option value="positive">Positive</option>
                            </select><br>
                            <label>Methamphetamine Result:</label>
                            <select name="meth_result" required>
                                <option value="negative">Negative</option>
                                <option value="positive">Positive</option>
                            </select><br><br>
                            <label>Assessment:</label>
                            <select name="assessment" required>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="conditional">Conditional</option>
                            </select><br><br>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `
    // Add other templates here easily
};

// Handle dynamic modal injection & submission
document.addEventListener("DOMContentLoaded", () => {
    const testSelect = document.getElementById("testSelect");
    const testModalsContainer = document.getElementById("testModalsContainer");

    testSelect.addEventListener("change", () => {
        const selectedTestId = testSelect.value.trim();
        testModalsContainer.innerHTML = "";

        const modalHTML = testTemplates[selectedTestId];
        if (modalHTML) {
            testModalsContainer.innerHTML = modalHTML;

            const modal = document.getElementById(`testModal_${selectedTestId}`);
            modal.style.display = "block";

            const form = document.getElementById(`form_test_${selectedTestId}`);
            form.addEventListener("submit", async function (e) {
                e.preventDefault();

                const formData = new FormData(form);
                const studentId = document.getElementById("studentSelect").value;
                formData.append("student_id", studentId);
                formData.append("test_id", selectedTestId);
                formData.append("action", "submit_test_data");

                try {
                    const response = await fetch("save_test_data.php", {
                        method: "POST",
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert("Test data saved successfully!");
                        modal.style.display = "none";
                        setTimeout(() => location.reload(), 100);
                    } else {
                        alert("Error: " + result.message);
                    }
                } catch (err) {
                    alert("Network error: " + err);
                }
            });
        }
    });
});

function loadAvailableTests() {
    const studentId = document.getElementById("studentSelect").value;
    if (studentId) {
        fetch(`get_available_tests.php?student_id=${studentId}`)
            .then(response => response.json())
            .then(tests => {
                const testSelect = document.getElementById("testSelect");
                testSelect.innerHTML = '<option value="">--Select a Test--</option>';
                tests.forEach(test => {
                    const option = document.createElement("option");
                    option.value = test.test_id;
                    option.textContent = test.test_name;
                    testSelect.appendChild(option);
                });
            });
    }
}

function closeTestModal(testId) {
    const modal = document.getElementById(`testModal_${testId}`);
    if (modal) modal.style.display = "none";
}
