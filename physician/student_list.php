<section class="students" id="student-list">
    <h1>Student List</h1>

    <!-- BSMT Search -->
    <div style="margin-bottom:15px;">
        <input type="text" id="searchBsmt" placeholder="Search BSMT by Student Number or Name" class="form-control" style="width:300px;">
    </div>
    <div id="bsmt-results"></div>

    <!-- BSMarE Search -->
    <div style="margin-top:30px; margin-bottom:15px;">
        <input type="text" id="searchBsmare" placeholder="Search BSMarE by Student Number or Name" class="form-control" style="width:300px;">
    </div>
    <div id="bsmare-results"></div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const bsmtInput = document.getElementById('searchBsmt');
    const bsmareInput = document.getElementById('searchBsmare');

    const fetchStudents = (course, query) => {
        fetch(`search_student.php?course=${course}&q=${encodeURIComponent(query)}`)
            .then(res => res.text())
            .then(html => {
                if(course === 'BSMT') {
                    document.getElementById('bsmt-results').innerHTML = html;
                } else {
                    document.getElementById('bsmare-results').innerHTML = html;
                }
            });
    };

    const debounce = (func, delay=300) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    };

    bsmtInput.addEventListener('input', debounce(() => fetchStudents('BSMT', bsmtInput.value)));
    bsmareInput.addEventListener('input', debounce(() => fetchStudents('BSMARE', bsmareInput.value)));

    // Initial load
    fetchStudents('BSMT', '');
    fetchStudents('BSMARE', '');
});
</script>
