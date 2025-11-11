// dash.js

// === Student Filtering ===
function filterStudents() {
    const input = document.getElementById('searchStudent');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#student-list table tbody tr');

    rows.forEach(row => {
        const studentNumber = row.cells[0]?.textContent.toLowerCase() || '';
        row.style.display = studentNumber.includes(filter) ? '' : 'none';
    });
}

// === Modal Controls ===
function openModal(id) {
    document.getElementById(id).style.display = 'block';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
    if (id === 'studentTestModal') clearTestSelectionForm();
    if (id === 'testModal_ishihara') clearIshiharaTest();
}

function debugOpenIshiharaTest() {
    openModal('testModal_ishihara');
}

// === Test Selection Form ===
function clearTestSelectionForm() {
    const form = document.getElementById('testSelectionForm');
    if (form) form.reset();
    const testSelect = document.getElementById('testSelect');
    if (testSelect) testSelect.innerHTML = '<option value="">--Select a Test--</option>';
    hideTestSelectError();
}

function validateTestSelection() {
    const testSelect = document.getElementById('testSelect');
    const errorDiv = document.getElementById('testSelectError');
    errorDiv.style.display = testSelect.value ? 'none' : 'block';
}

function loadAvailableTests() {
    const studentId = document.getElementById('studentSelect')?.value;
    const testSelect = document.getElementById('testSelect');

    testSelect.innerHTML = '<option value="">--Select a Test--</option>';
    if (!studentId) return console.warn("No student selected.");

    fetch(`php/get_available_tests.php?student_id=${studentId}`)
        .then(res => res.json())
        .then(tests => {
            if (Array.isArray(tests) && tests.length > 0) {
                tests.forEach(test => {
                    const option = new Option(test.test_name, test.test_id);
                    testSelect.appendChild(option);
                });
            } else {
                testSelect.appendChild(new Option('No tests remaining for this student.', '', true));
            }
        })
        .catch(() => {
            testSelect.appendChild(new Option('Error loading tests.', '', true));
        });
}

// === Ishihara Test Logic ===
function clearIshiharaTest() {
    const container = document.getElementById('testQuestionsContainer');
    if (container) container.innerHTML = '<p>Please select a student to start the test.</p>';

    const studentSelect = document.getElementById('studentSelect');
    if (studentSelect) studentSelect.value = '';
}

function loadIshiharaQuestions() {
    const studentId = document.getElementById('studentSelect')?.value;
    const container = document.getElementById('testQuestionsContainer');

    if (!studentId || !container) {
        if (container) container.innerHTML = '<p>Please select a student to start the test.</p>';
        return;
    }

    const questions = [
        { plate: 1, image: 'images/ishihara/plate1.webp', correctAnswer: '7' },
        { plate: 2, image: 'images/ishihara/plate2.webp', correctAnswer: '6' },
        { plate: 3, image: 'images/ishihara/plate3.webp', correctAnswer: '26' },
        { plate: 4, image: 'images/ishihara/plate4.webp', correctAnswer: '15' },
        { plate: 5, image: 'images/ishihara/plate5.webp', correctAnswer: '6' },
        { plate: 6, image: 'images/ishihara/plate6.webp', correctAnswer: '73' },
        { plate: 7, image: 'images/ishihara/plate7.webp', correctAnswer: '5' },
        { plate: 8, image: 'images/ishihara/plate8.webp', correctAnswer: '16' },
        { plate: 9, image: 'images/ishihara/plate9.webp', correctAnswer: '45' },
        { plate: 10, image: 'images/ishihara/plate10.webp', correctAnswer: '12' },
        { plate: 11, image: 'images/ishihara/plate11.webp', correctAnswer: '29' },
        { plate: 12, image: 'images/ishihara/plate12.webp', correctAnswer: '8' },
    ];

    let html = '<form id="ishiharaTestForm">';
    questions.forEach(q => {
        html += `
            <div class="ishihara-question">
                <label>Plate ${q.plate}</label><br>
                <img src="${q.image}" alt="Plate ${q.plate}" style="max-width: 150px;"><br>
                <input type="text" name="answer_${q.plate}" placeholder="Your answer" required>
            </div><br>
        `;
    });
    html += '<button type="submit" class="btn btn-success">Submit Test</button></form>';

    container.innerHTML = html;

    document.getElementById('ishiharaTestForm').addEventListener('submit', function (e) {
        e.preventDefault();
        submitIshiharaTest(studentId);
    });
}

function submitIshiharaTest(studentId) {
    const form = document.getElementById('ishiharaTestForm');
    const formData = new FormData(form);

    // Replace this demo with actual backend logic
    alert(`Ishihara test submitted for student ID: ${studentId}`);
    closeModal('testModal_ishihara');
}

// === Toggle Detail Row Logic ===
function setupToggleButtons() {
    const buttons = document.querySelectorAll('.toggle-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const detailId = this.dataset.id;
            const row = document.getElementById(`detail-${detailId}`);

            if (!row) return;

            const studentId = detailId.split('-')[0];
            const allDetailRows = document.querySelectorAll(`[id^="detail-${studentId}-"]`);

            allDetailRows.forEach(r => {
                if (r !== row) r.style.display = 'none';
            });

            row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row';
        });
    });
}

// === On Page Load ===
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchStudent');
    if (searchInput) searchInput.addEventListener('keyup', filterStudents);
    setupToggleButtons();
});
