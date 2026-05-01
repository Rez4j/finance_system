<?php
require '../includes/header.php'; 
?>

<!-- Include Chart.js for visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Financial Reports</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase small fw-bold">Total Revenue</h6>
                <h2 class="mb-0" id="stat-revenue">₱0.00</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body">
                <h6 class="text-uppercase small fw-bold">Total Expenses</h6>
                <h2 class="mb-0" id="stat-expenses">₱0.00</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase small fw-bold">Net Income</h6>
                <h2 class="mb-0" id="stat-net">₱0.00</h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart Section -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold">Revenue vs Expense Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="financeChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Report Selection & Filters -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Report Filters</h5>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Report Type</label>
                    <select id="reportType" class="form-select" onchange="generateReport()">
                        <option value="income_statement">Income Statement</option>
                        <option value="budget_utilization">Budget vs Actual</option>
                        <option value="student_revenue">Student Fees Summary</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Fiscal Year</label>
                    <select id="reportYear" class="form-select" onchange="generateReport()">
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100 py-2 mt-2" onclick="generateReport()">
                    Update View
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Data Table -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="reportTable">
                <thead class="table-light" id="reportHeader">
                    <!-- Dynamic Header -->
                </thead>
                <tbody id="reportContent">
                    <!-- Dynamic Content -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const API_REPORTS = '../api/reports.php';
const API_KEY = 'fin_sys_2024';
let financeChart = null;

$(document).ready(function() {
    generateReport();
});

/**
 * Main function to fetch data and update UI
 */
function generateReport() {
    const type = $('#reportType').val();
    const year = $('#reportYear').val();

    $.ajax({
        url: `${API_REPORTS}?api_key=${API_KEY}&type=${type}&year=${year}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateStats(response.summary);
                updateChart(response.chartData);
                renderTable(type, response.tableData);
            }
        }
    });
}

function updateStats(summary) {
    $('#stat-revenue').text('₱' + parseFloat(summary.revenue).toLocaleString());
    $('#stat-expenses').text('₱' + parseFloat(summary.expenses).toLocaleString());
    $('#stat-net').text('₱' + parseFloat(summary.net).toLocaleString());
}

function updateChart(chartData) {
    const ctx = document.getElementById('financeChart').getContext('2d');
    
    if (financeChart) financeChart.destroy();

    financeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.months,
            datasets: [
                {
                    label: 'Revenue',
                    data: chartData.revenue,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Expenses',
                    data: chartData.expenses,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

function renderTable(type, data) {
    let header = '';
    let body = '';

    if (type === 'income_statement') {
        header = `<tr><th>Category</th><th class="text-end">Total Amount</th></tr>`;
        data.forEach(item => {
            body += `<tr><td>${item.label}</td><td class="text-end fw-bold">₱${parseFloat(item.amount).toLocaleString()}</td></tr>`;
        });
    } else if (type === 'budget_utilization') {
        header = `<tr><th>Department</th><th class="text-end">Budget</th><th class="text-end">Spent</th><th class="text-end">Remaining</th></tr>`;
        data.forEach(item => {
            const remaining = item.allocated - item.spent;
            body += `<tr>
                <td>${item.dept_name}</td>
                <td class="text-end">₱${parseFloat(item.allocated).toLocaleString()}</td>
                <td class="text-end text-danger">₱${parseFloat(item.spent).toLocaleString()}</td>
                <td class="text-end fw-bold">₱${parseFloat(remaining).toLocaleString()}</td>
            </tr>`;
        });
    }

    $('#reportHeader').html(header);
    $('#reportContent').html(body);
}
</script>

<?php require '../includes/footer.php'; ?>