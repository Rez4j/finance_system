<?php
require '../includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Payroll Management</h1>
    <button class="btn btn-primary shadow-sm" onclick="showAddModal()">
        <i class="bi bi-plus-lg"></i> Process Payment
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Pay Date</th>
                        <th>Employee</th>
                        <th class="text-end">Gross</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end text-primary">Net Amount</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody id="payrollTableBody">
                    <tr><td colspan="6" class="text-center py-5 text-muted">Loading payroll records...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payroll Modal -->
<div class="modal fade" id="payrollModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Process Salary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payrollForm" onsubmit="event.preventDefault(); savePayment();">
                <div class="modal-body">
                    <input type="hidden" id="pay_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee</label>
                        <select class="form-select" id="employee_id" required>
                            <option value="">Select Employee...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pay Date</label>
                        <input type="date" class="form-control" id="pay_date" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gross Amount</label>
                            <input type="number" step="0.01" class="form-control calc-trigger" id="gross_amount" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Deductions</label>
                            <input type="number" step="0.01" class="form-control calc-trigger" id="deductions" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-primary">Net Amount</label>
                            <input type="number" step="0.01" class="form-control bg-light" id="net_amount" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API_PAYROLL = '../api/employee_payments.php';
const API_EMPLOYEES = '../api/employees.php';
const API_KEY = 'fin_sys_2024';

$(document).ready(function() {
    loadPayments();
    loadEmployeesForSelect();

    // Auto-calculate Net Amount
    $('.calc-trigger').on('input', function() {
        const gross = parseFloat($('#gross_amount').val()) || 0;
        const ded = parseFloat($('#deductions').val()) || 0;
        $('#net_amount').val((gross - ded).toFixed(2));
    });
});

function loadPayments() {
    $.ajax({
        url: `${API_PAYROLL}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) renderTable(response.data);
        }
    });
}

function loadEmployeesForSelect() {
    $.ajax({
        url: `${API_EMPLOYEES}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Employee...</option>';
                response.data.forEach(e => {
                    options += `<option value="${e.employee_id}">${e.last_name}, ${e.first_name}</option>`;
                });
                $('#employee_id').html(options);
            }
        }
    });
}

function savePayment() {
    const id = $('#pay_id').val();
    const data = {
        employee_id: $('#employee_id').val(),
        pay_date: $('#pay_date').val(),
        gross_amount: $('#gross_amount').val(),
        deductions: $('#deductions').val(),
        net_amount: $('#net_amount').val()
    };

    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_PAYROLL}?api_key=${API_KEY}&id=${id}` : `${API_PAYROLL}?api_key=${API_KEY}`;

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                $('#payrollModal').modal('hide');
                loadPayments();
                showToast('Success', response.message, 'success');
            }
        }
    });
}

function showAddModal() {
    $('#payrollForm')[0].reset();
    $('#pay_id').val('');
    $('#modalTitle').text('Process Salary Payment');
    $('#payrollModal').modal('show');
}

function renderTable(data) {
    let html = '';
    data.forEach(p => {
        html += `
            <tr>
                <td class="text-muted small">${p.pay_date}</td>
                <td class="fw-bold text-dark">${p.employee_name}</td>
                <td class="text-end">₱${parseFloat(p.gross_amount).toLocaleString()}</td>
                <td class="text-end text-danger">-₱${parseFloat(p.deductions).toLocaleString()}</td>
                <td class="text-end fw-bold text-primary">₱${parseFloat(p.net_amount).toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="deletePayment(${p.pay_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
    });
    $('#payrollTableBody').html(html || '<tr><td colspan="6" class="text-center py-4">No records found</td></tr>');
}
</script>

<?php require '../includes/footer.php'; ?>