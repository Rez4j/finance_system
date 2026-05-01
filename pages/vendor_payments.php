<?php
require '../includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Vendor Payments</h1>
    <button class="btn btn-primary shadow-sm" onclick="showAddModal()">
        <i class="bi bi-plus-lg"></i> Record Disbursement
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Vendor/Supplier</th>
                        <th>PO Reference</th>
                        <th class="text-end">Amount Paid</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody id="vendorPaymentsTableBody">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Loading disbursements...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Vendor Payment Modal -->
<div class="modal fade" id="vendorPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Disbursement Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="vendorPaymentForm" onsubmit="event.preventDefault(); savePayment();">
                <div class="modal-body">
                    <input type="hidden" id="payment_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier</label>
                        <select class="form-select" id="supplier_id" required onchange="loadPOsForSupplier()">
                            <option value="">Select Supplier...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Purchase Order (Optional)</label>
                        <select class="form-select" id="po_id">
                            <option value="">No PO Reference</option>
                        </select>
                        <div class="form-text text-muted small">Link this payment to a specific order.</div>
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
const API_VENDOR_PAY = '../api/vendor_payments.php';
const API_SUPPLIERS = '../api/suppliers.php';
const API_POS = '../api/purchase_orders.php';
const API_KEY = 'fin_sys_2024';

$(document).ready(function() {
    loadVendorPayments();
    loadSuppliersForSelect();
});

function loadVendorPayments() {
    $.ajax({
        url: `${API_VENDOR_PAY}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) renderTable(response.data);
        }
    });
}

function loadSuppliersForSelect() {
    $.ajax({
        url: `${API_SUPPLIERS}?api_key=${API_KEY}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Supplier...</option>';
                response.data.forEach(s => {
                    options += `<option value="${s.supplier_id}">${s.name}</option>`;
                });
                $('#supplier_id').html(options);
            }
        }
    });
}

/**
 * Dynamically loads POs belonging to the selected supplier.
 */
function loadPOsForSupplier() {
    const supplierId = $('#supplier_id').val();
    if (!supplierId) {
        $('#po_id').html('<option value="">No PO Reference</option>');
        return;
    }

    $.ajax({
        url: `${API_POS}?api_key=${API_KEY}&supplier_id=${supplierId}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">No PO Reference</option>';
                response.data.forEach(po => {
                    options += `<option value="${po.po_id}">PO #${po.po_id} - ₱${parseFloat(po.total_amount).toLocaleString()}</option>`;
                });
                $('#po_id').html(options);
            }
        }
    });
}

function savePayment() {
    const id = $('#payment_id').val();
    const data = {
        supplier_id: $('#supplier_id').val(),
        po_id: $('#po_id').val() || null,
        pay_date: $('#pay_date').val(),
        amount: $('#amount').val()
    };

    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_VENDOR_PAY}?api_key=${API_KEY}&id=${id}` : `${API_VENDOR_PAY}?api_key=${API_KEY}`;

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                $('#vendorPaymentModal').modal('hide');
                loadVendorPayments();
                showToast('Success', response.message, 'success');
            }
        }
    });
}

function showAddModal() {
    $('#vendorPaymentForm')[0].reset();
    $('#payment_id').val('');
    $('#modalTitle').text('Record Vendor Disbursement');
    $('#vendorPaymentModal').modal('show');
}

function renderTable(data) {
    let html = '';
    data.forEach(p => {
        const poRef = p.po_id ? `PO #${p.po_id}` : '<span class="text-muted small">N/A</span>';
        html += `
            <tr>
                <td class="text-muted small">${p.pay_date}</td>
                <td class="fw-bold text-dark">${p.supplier_name}</td>
                <td>${poRef}</td>
                <td class="text-end fw-bold">₱${parseFloat(p.amount).toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="deletePayment(${p.payment_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
    });
    $('#vendorPaymentsTableBody').html(html || '<tr><td colspan="5" class="text-center py-4">No disbursements found</td></tr>');
}
</script>

<?php require '../includes/footer.php'; ?>