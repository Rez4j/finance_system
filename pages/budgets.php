<?php
require '../includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Budget Management</h1>
    <button class="btn btn-primary shadow-sm" onclick="showAddModal()">
        <i class="bi bi-plus-lg"></i> Add Budget
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Fiscal Year</th>
                        <th class="text-end">Allocated Amount</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody id="budgetsTableBody">
                    <tr><td colspan="4" class="text-center py-5 text-muted">Loading budgets...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Budget Modal -->
<div class="modal fade" id="budgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Budget Allocation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="budgetForm" onsubmit="event.preventDefault(); saveBudget();">
                <div class="modal-body">
                    <input type="hidden" id="budget_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select class="form-select" id="department_id" required>
                            <option value="">Select Department...</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Year</label>
                            <input type="number" class="form-control" id="year" min="2000" max="2099" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="allocated_amount" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API_BUDGETS = '../api/budgets.php';
const API_DEPARTMENTS = '../api/departments.php'; // You will need this API to fetch department names[cite: 1]
const API_KEY = 'fin_sys_2024';

$(document).ready(function() {
    loadBudgets();
    loadDepartmentsForSelect();
});

function loadBudgets() {
    $.ajax({
        url: `${API_BUDGETS}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) renderTable(response.data);
        }
    });
}

function loadDepartmentsForSelect() {
    // Fetches departments to populate the dropdown
    $.ajax({
        url: `${API_DEPARTMENTS}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Department...</option>';
                response.data.forEach(dept => {
                    options += `<option value="${dept.department_id}">${dept.name}</option>`;
                });
                $('#department_id').html(options);
            }
        }
    });
}

function saveBudget() {
    const id = $('#budget_id').val();
    const data = {
        department_id: $('#department_id').val(),
        year: $('#year').val(),
        allocated_amount: $('#allocated_amount').val()
    };

    if (id) {
        $.ajax({
            url: `${API_BUDGETS}?api_key=${API_KEY}&id=${id}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: handleResponse
        });
    } else {
        $.ajax({
            url: `${API_BUDGETS}?api_key=${API_KEY}`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: handleResponse
        });
    }
}

function handleResponse(response) {
    if (response.success) {
        $('#budgetModal').modal('hide');
        loadBudgets();
        showToast('Success', response.message, 'success');
    }
}

function showAddModal() {
    $('#budgetForm')[0].reset();
    $('#budget_id').val('');
    $('#modalTitle').text('New Budget Allocation');
    $('#budgetModal').modal('show');
}

function showEditModal(id, dept_id, year, amount) {
    $('#budget_id').val(id);
    $('#department_id').val(dept_id);
    $('#year').val(year);
    $('#allocated_amount').val(amount);
    $('#modalTitle').text('Edit Budget');
    $('#budgetModal').modal('show');
}

function renderTable(data) {
    let html = '';
    data.forEach(b => {
        html += `
            <tr>
                <td class="fw-bold text-dark">${b.department_name}</td>
                <td>${b.year}</td>
                <td class="text-end fw-bold">₱${parseFloat(b.allocated_amount).toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-outline-warning border-0" onclick="showEditModal(${b.budget_id}, ${b.department_id}, '${b.year}', ${b.allocated_amount})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteBudget(${b.budget_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
    });
    $('#budgetsTableBody').html(html || '<tr><td colspan="4" class="text-center py-4">No budget records found</td></tr>');
}
</script>

<?php require '../includes/footer.php'; ?>