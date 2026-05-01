<?php
require_once '../includes/header.php';

$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : 2024;

// Get available years from API
$apiUrl = "../api/dashboard?api_key=fin_sys_2024&year=" . $selectedYear;
?>

<div class="container-fluid p-0">
    
    <!-- Welcome Header with Year Selector -->
    <div class="rounded-4 text-white p-4 mb-4" style="background: linear-gradient(135deg, #1e6b3e 0%, #14492a 100%);">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="display-5 fw-bold mb-2">System Dashboard</h1>
                <p class="opacity-90 mb-0">Welcome back, <span class="fw-bold"><?php echo htmlspecialchars($user_full_name); ?></span>. Here is your financial overview for <span class="fw-semibold" id="yearDisplay">FY <?php echo $selectedYear; ?></span>.</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <div class="d-flex align-items-center justify-content-md-end gap-3">
                    <div class="d-inline-flex gap-2 p-2 bg-white bg-opacity-25 rounded-4 align-items-center">
                        <div class="bg-success rounded-circle" style="width: 10px; height: 10px;"></div>
                        <span class="small fw-bold text-uppercase" style="font-size: 0.65rem;">System Operational</span>
                    </div>
                    <div>
                        <label class="small opacity-75 mb-1 d-block">Select Fiscal Year</label>
                        <select id="yearSelect" class="form-select form-select-sm" style="width: auto; display: inline-block; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);" onchange="changeYear(this.value)">
                            <option value="2023" style="color: #000;">FY 2023</option>
                            <option value="2024" style="color: #000;" selected>FY 2024</option>
                            <option value="2025" style="color: #000;">FY 2025</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3 text-primary">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Total Budget</div>
                            <h3 class="mb-0 fw-bold" id="totalBudget">₱0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 bg-danger bg-opacity-10 p-3 me-3 text-danger">
                            <i class="bi bi-graph-down-arrow fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Total Expenses</div>
                            <h3 class="mb-0 fw-bold" id="totalExpenses">₱0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3 text-success">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Total Revenue</div>
                            <h3 class="mb-0 fw-bold" id="totalRevenue">₱0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3 text-warning">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Pending Orders</div>
                            <h3 class="mb-0 fw-bold" id="pendingOrders">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Financial Performance Overview</h6>
                    <span class="badge bg-light text-muted fw-normal" id="chartYearBadge">FY 2024</span>
                </div>
                <div class="card-body" style="min-height: 350px;">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart-fill text-success me-2"></i>Budget Allocation</h6>
                </div>
                <div class="card-body d-flex align-items-center">
                    <canvas id="budgetPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue vs Expenses -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up text-info me-2"></i>Monthly Revenue vs Expenses</h6>
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-white" style="background: linear-gradient(180deg, #1e6b3e 0%, #14492a 100%);">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-activity me-2"></i>Activity Monitor</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                <i class="bi bi-cash-stack fs-5"></i>
                            </div>
                            <div>
                                <div class="small opacity-75">Student Payments</div>
                                <div class="fw-bold fs-5" id="studentPayments">₱0</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                <i class="bi bi-calculator fs-5"></i>
                            </div>
                            <div>
                                <div class="small opacity-75">Net Position</div>
                                <div class="fw-bold fs-5" id="netPosition">₱0</div>
                            </div>
                        </div>
                        <div class="mt-2" style="height: 60px;">
                            <canvas id="miniSparkline"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions and Department Spending -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Recent Transactions</h6>
                    <a href="transactions.php" class="btn btn-primary btn-sm rounded-3">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody id="recentTransactions">
                            <tr><td colspan="5" class="text-center text-muted py-3">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-building text-success me-2"></i>Department Spending</h6>
                </div>
                <div class="card-body" id="departmentSpending">
                    <p class="text-center text-muted py-3">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Modules -->
    <h5 class="fw-bold mb-4 text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem; color: #6c757d;">Financial Modules</h5>
    <div class="row g-4 mb-4">
        <?php 
        $modules = [
            ['title' => 'Chart of Accounts', 'desc' => 'Manage financial accounts and categories.', 'icon' => 'bi-book', 'link' => 'accounts.php', 'color' => '#3b82f6'],
            ['title' => 'Journal Entries', 'desc' => 'Record and track all transactions.', 'icon' => 'bi-arrow-left-right', 'link' => 'transactions.php', 'color' => '#ef4444'],
            ['title' => 'Budget Management', 'desc' => 'Allocate funds and monitor spending.', 'icon' => 'bi-pie-chart', 'link' => 'budgets.php', 'color' => '#f59e0b'],
            ['title' => 'Student Payments', 'desc' => 'Process tuition and fee payments.', 'icon' => 'bi-cash-coin', 'link' => 'student_payments.php', 'color' => '#10b981'],
            ['title' => 'Payroll', 'desc' => 'Manage employee salary disbursements.', 'icon' => 'bi-people', 'link' => 'employee_payments.php', 'color' => '#8b5cf6'],
            ['title' => 'Vendor Payments', 'desc' => 'Track accounts payable.', 'icon' => 'bi-truck', 'link' => 'vendor_payments.php', 'color' => '#06b6d4'],
            ['title' => 'Purchase Orders', 'desc' => 'Create procurement orders.', 'icon' => 'bi-cart', 'link' => 'purchase_orders.php', 'color' => '#ec4899'],
            ['title' => 'Financial Reports', 'desc' => 'Generate financial statements.', 'icon' => 'bi-file-earmark-bar-graph', 'link' => 'reports.php', 'color' => '#1e6b3e'],
        ];
        foreach($modules as $mod):
        ?>
        <div class="col-md-6 col-lg-3">
            <a href="<?php echo $mod['link']; ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="border-left: 5px solid <?php echo $mod['color']; ?> !important; transition: all 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="rounded-3 p-3 d-inline-block mb-3" style="background-color: <?php echo $mod['color']; ?>15;">
                            <i class="<?php echo $mod['icon']; ?> fs-4" style="color: <?php echo $mod['color']; ?>;"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2"><?php echo $mod['title']; ?></h6>
                        <p class="text-muted small mb-0"><?php echo $mod['desc']; ?></p>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Chart instances
let performanceChart = null;
let budgetPieChart = null;
let monthlyChart = null;
let sparklineChart = null;

// API Base URL
const API_BASE = '/api';
const API_KEY = 'fin_sys_2024';

// Color palette
const COLORS = ['#1e6b3e', '#2f7a4d', '#3aa86a', '#059669', '#10b981', '#34d399', '#6ee7b7', '#a7f3d0'];

// Format currency
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 0});
}

// Change year
function changeYear(year) {
    window.location.href = 'dashboard.php?year=' + year;
}

// Fetch data from API
async function fetchAPI(endpoint, params = {}) {
    const url = new URL(`${API_BASE}/${endpoint}`, window.location.origin);
    url.searchParams.append('api_key', API_KEY);
    
    Object.keys(params).forEach(key => {
        url.searchParams.append(key, params[key]);
    });
    
    try {
        const response = await fetch(url.toString());
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'API Error');
        }
        
        return data;
    } catch (error) {
        console.error('API Fetch Error:', error);
        showToast('Error', error.message, 'error');
        return null;
    }
}

// Load all dashboard data
async function loadDashboard() {
    const year = document.getElementById('yearSelect').value;
    
    // Update year display
    document.getElementById('yearDisplay').textContent = 'FY ' + year;
    document.getElementById('chartYearBadge').textContent = 'FY ' + year;
    
    // Fetch dashboard data
    const data = await fetchAPI('dashboard', { year: year });
    
    if (!data) return;
    
    // Update summary cards
    document.getElementById('totalBudget').textContent = formatCurrency(data.summary.total_budget);
    document.getElementById('totalExpenses').textContent = formatCurrency(data.summary.total_expenses);
    document.getElementById('totalRevenue').textContent = formatCurrency(data.summary.total_revenue);
    document.getElementById('studentPayments').textContent = formatCurrency(data.summary.total_student_payments);
    
    // Net position
    const netPos = parseFloat(data.summary.total_revenue) - parseFloat(data.summary.total_expenses);
    document.getElementById('netPosition').textContent = formatCurrency(netPos);
    
    // Update charts
    updatePerformanceChart(data.budget_by_department);
    updateBudgetPieChart(data.budget_by_department);
    updateMonthlyChart(data.monthly_data);
    updateRecentTransactions(data.recent_transactions);
    updateDepartmentSpending(data.budget_by_department);
}

// Update Performance Bar Chart
function updatePerformanceChart(budgetData) {
    const labels = budgetData.map(item => item.name || item.department_name);
    const values = budgetData.map(item => parseFloat(item.allocated || 0));
    
    if (performanceChart) performanceChart.destroy();
    
    const ctx = document.getElementById('performanceChart').getContext('2d');
    performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Allocated Budget',
                data: values,
                backgroundColor: COLORS.slice(0, labels.length),
                borderRadius: 8,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { callback: v => '₱' + v.toLocaleString() },
                    grid: { borderDash: [5, 5] }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

// Update Budget Pie Chart
function updateBudgetPieChart(budgetData) {
    const labels = budgetData.map(item => item.name || item.department_name);
    const values = budgetData.map(item => parseFloat(item.allocated || 0));
    
    if (budgetPieChart) budgetPieChart.destroy();
    
    const ctx = document.getElementById('budgetPieChart').getContext('2d');
    budgetPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: COLORS.slice(0, labels.length),
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { boxWidth: 10, usePointStyle: true, padding: 15, font: { size: 10 } } 
                } 
            },
            cutout: '60%'
        }
    });
}

// Update Monthly Chart - Only show months with data
function updateMonthlyChart(monthlyData) {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    // Filter only months that have data (revenue or expenses > 0)
    const activeMonths = monthlyData.filter(item => 
        parseFloat(item.revenue || 0) > 0 || parseFloat(item.expenses || 0) > 0
    );
    
    // If no data at all, show empty chart with message
    if (activeMonths.length === 0) {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        if (monthlyChart) monthlyChart.destroy();
        monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['No Data'],
                datasets: [{
                    label: 'Revenue',
                    data: [0],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4
                }, {
                    label: 'Expenses',
                    data: [0],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'bottom' },
                    title: {
                        display: true,
                        text: 'No monthly data available for this year',
                        position: 'top'
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: { callback: v => '₱' + v.toLocaleString() }
                    }
                }
            }
        });
        return;
    }
    
    // Get labels and data for active months only
    const labels = activeMonths.map(item => monthNames[item.month - 1]);
    const revenue = activeMonths.map(item => parseFloat(item.revenue || 0));
    const expenses = activeMonths.map(item => parseFloat(item.expenses || 0));
    
    if (monthlyChart) monthlyChart.destroy();
    
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: revenue,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 5,
                pointBackgroundColor: '#10b981',
                pointHoverRadius: 7
            }, {
                label: 'Expenses',
                data: expenses,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 5,
                pointBackgroundColor: '#ef4444',
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: { 
                        callback: v => '₱' + v.toLocaleString() 
                    },
                    grid: { borderDash: [5, 5] }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

// Update Recent Transactions
function updateRecentTransactions(transactions) {
    const tbody = document.getElementById('recentTransactions');
    
    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No transactions found</td></tr>';
        return;
    }
    
    tbody.innerHTML = transactions.map(trans => `
        <tr>
            <td class="small">${new Date(trans.trans_date).toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric'})}</td>
            <td class="small">${trans.account_name}</td>
            <td><span class="badge bg-${trans.type === 'Debit' ? 'danger' : 'success'} bg-opacity-10 text-${trans.type === 'Debit' ? 'danger' : 'success'}">${trans.type}</span></td>
            <td class="small fw-semibold">₱${parseFloat(trans.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
            <td class="small text-muted">${trans.description || ''}</td>
        </tr>
    `).join('');
}

// Update Department Spending
function updateDepartmentSpending(budgetData) {
    const container = document.getElementById('departmentSpending');
    const barColors = ['#1e6b3e', '#2f7a4d', '#3aa86a', '#059669', '#10b981'];
    
    // Simulate spending from allocated budget (70-95% spent)
    const spendingData = budgetData.map((item, index) => ({
        name: item.name || item.department_name,
        spent: parseFloat(item.allocated || 0) * (0.7 + Math.random() * 0.25)
    })).sort((a, b) => b.spent - a.spent).slice(0, 5);
    
    if (spendingData.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-3">No spending data available</p>';
        return;
    }
    
    const maxSpent = spendingData[0].spent || 1;
    
    container.innerHTML = spendingData.map((dept, index) => `
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small fw-semibold">${dept.name}</span>
                <span class="small text-muted">₱${dept.spent.toLocaleString('en-US', {minimumFractionDigits: 0})}</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar" style="width: ${(dept.spent / maxSpent) * 100}%; background-color: ${barColors[index] || '#1e6b3e'};"></div>
            </div>
        </div>
    `).join('');
}

// Init Sparkline
function initSparkline() {
    const ctx = document.getElementById('miniSparkline').getContext('2d');
    sparklineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                data: [12, 19, 8, 15, 12, 7, 14],
                borderColor: 'rgba(255,255,255,0.8)',
                borderWidth: 2,
                fill: true,
                backgroundColor: 'rgba(255,255,255,0.1)',
                pointRadius: 0,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
}

// Load everything on page load
document.addEventListener('DOMContentLoaded', function() {
    initSparkline();
    loadDashboard();
});
</script>

<?php require_once '../includes/footer.php'; ?>