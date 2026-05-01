<?php
require '../includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Student Payments</h1>
    <button class="btn btn-primary shadow-sm" onclick="showAddModal()">
        <i class="bi bi-plus-lg"></i> Record Payment
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Student Name</th>
                        <th>Description</th>
                        <th class="text-end">Amount Paid</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Loading payment records...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" onsubmit="event.preventDefault(); savePayment();">
                <div class="modal-body">
                    <input type="hidden" id="payment_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Student</label>
                        <select class="form-select" id="student_id" required>
                            <option value="">Select Student...</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Date</label>
                            <input type="date" class="form-control" id="pay_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="amount" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" class="form-control" id="description" placeholder="e.g. Tuition Fee - 1st Semester" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Post Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API_PAYMENTS = '../api/student_payments.php';
const API_STUDENTS = '../api/students.php'; // Required to populate student selection
const API_KEY = 'fin_sys_2024';

$(document).ready(function() {
    loadPayments();
    loadStudentsForSelect();
});

function loadPayments() {
    $.ajax({
        url: `${API_PAYMENTS}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) renderTable(response.data);
        }
    });
}

function loadStudentsForSelect() {
    $.ajax({
        url: `${API_STUDENTS}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Student...</option>';
                response.data.forEach(s => {
                    options += `<option value="${s.student_id}">${s.last_name}, ${s.first_name}</option>`;
                });
                $('#student_id').html(options);
            }
        }
    });
}

function savePayment() {
    const id = $('#payment_id').val();
    const data = {
        student_id: $('#student_id').val(),
        pay_date: $('#pay_date').val(),
        amount: $('#amount').val(),
        description: $('#description').val()
    };

    if (id) {
        $.ajax({
            url: `${API_PAYMENTS}?api_key=${API_KEY}&id=${id}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: handleResponse
        });
    } else {
        $.ajax({
            url: `${API_PAYMENTS}?api_key=${API_KEY}`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: handleResponse
        });
    }
}

function handleResponse(response) {
    if (response.success) {
        $('#paymentModal').modal('hide');
        loadPayments();
        showToast('Success', response.message, 'success');
    }
}

function showAddModal() {
    $('#paymentForm')[0].reset();
    $('#payment_id').val('');
    $('#modalTitle').text('Record New Payment');
    $('#paymentModal').modal('show');
}

function renderTable(data) {
    let html = '';
    data.forEach(p => {
        html += `
            <tr>
                <td class="text-muted small">${p.pay_date}</td>
                <td class="fw-bold text-dark">${p.student_name}</td>
                <td>${p.description}</td>
                <td class="text-end fw-bold">₱${parseFloat(p.amount).toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="deletePayment(${p.payment_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
    });
    $('#paymentsTableBody').html(html || '<tr><td colspan="5" class="text-center py-4">No payment history found</td></tr>');
}
</script>

<?php require '../includes/footer.php'; ?>