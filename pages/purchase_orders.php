<?php
require '../includes/header.php'; 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Purchase Orders</h1>
    <button class="btn btn-primary shadow-sm" onclick="showAddModal()">
        <i class="bi bi-cart-plus"></i> Create Order
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>PO #</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th class="text-end">Total Amount</th>
                        <th>Status</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody id="poTableBody">
                    <tr><td colspan="6" class="text-center py-5 text-muted">Loading purchase orders...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PO Modal -->
<div class="modal fade" id="poModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="poForm" onsubmit="event.preventDefault(); savePO();">
                <div class="modal-body">
                    <input type="hidden" id="po_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier</label>
                        <select class="form-select" id="supplier_id" required>
                            <option value="">Select Supplier...</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Order Date</label>
                            <input type="date" class="form-control" id="order_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" required>
                                <option value="Draft">Draft</option>
                                <option value="Ordered">Ordered</option>
                                <option value="Received">Received</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Estimated Total</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="total_amount" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const API_POS = '../api/purchase_orders.php';
const API_SUPPLIERS = '../api/suppliers.php';
const API_KEY = 'fin_sys_2024';

$(document).ready(function() {
    loadPOs();
    loadSuppliersForSelect();
});

function loadPOs() {
    $.ajax({
        url: `${API_POS}?api_key=${API_KEY}`,
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

function savePO() {
    const id = $('#po_id').val();
    const data = {
        supplier_id: $('#supplier_id').val(),
        order_date: $('#order_date').val(),
        total_amount: $('#total_amount').val(),
        status: $('#status').val()
    };

    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_POS}?api_key=${API_KEY}&id=${id}` : `${API_POS}?api_key=${API_KEY}`;

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                $('#poModal').modal('hide');
                loadPOs();
                showToast('Success', response.message, 'success');
            }
        }
    });
}

function showAddModal() {
    $('#poForm')[0].reset();
    $('#po_id').val('');
    $('#modalTitle').text('New Purchase Order');
    $('#poModal').modal('show');
}

function showEditModal(id, supId, date, amount, status) {
    $('#po_id').val(id);
    $('#supplier_id').val(supId);
    $('#order_date').val(date);
    $('#total_amount').val(amount);
    $('#status').val(status);
    $('#modalTitle').text('Edit Purchase Order');
    $('#poModal').modal('show');
}

/**
 * 1. APPROVE ORDER (Moves to 'Ordered')
 */
function approveOrder(id) {
    if (!confirm('Approve this purchase order?')) return;

    $.ajax({
        url: `${API_POS}?api_key=${API_KEY}&id=${id}`,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'approve' }),
        success: function(response) {
            if (response.success) {
                loadPOs();
                showToast('Success', response.message, 'success');
            }
        }
    });
}

/**
 * 2. CANCEL ORDER (Moves to 'Cancelled')
 */
function cancelOrder(id) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    $.ajax({
        url: `${API_POS}?api_key=${API_KEY}&id=${id}`,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'cancel' }),
        success: function(response) {
            if (response.success) {
                loadPOs();
                showToast('Cancelled', response.message, 'warning');
            }
        }
    });
}

/**
 * 3. RENDER TABLE (With Conditional Buttons)
 */
function renderTable(data) {
    let html = '';
    data.forEach(po => {
        let statusBadge = '';
        switch(po.status) {
            case 'Draft': statusBadge = 'bg-secondary'; break;
            case 'Ordered': statusBadge = 'bg-info text-dark'; break;
            case 'Received': statusBadge = 'bg-success'; break;
            case 'Cancelled': statusBadge = 'bg-danger'; break;
        }

        // Action Logic: Only show Approve/Cancel if status is 'Draft'[cite: 1]
        let actionButtons = '';
        if (po.status === 'Draft') {
            actionButtons = `
                <button class="btn btn-sm btn-success border-0" title="Approve" onclick="approveOrder(${po.po_id})">
                    <i class="bi bi-check-circle"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger border-0" title="Cancel" onclick="cancelOrder(${po.po_id})">
                    <i class="bi bi-x-circle"></i>
                </button>
            `;
        }

        html += `
            <tr>
                <td><span class="text-muted small">#${po.po_id}</span></td>
                <td>${po.order_date}</td>
                <td class="fw-bold">${po.supplier_name}</td>
                <td class="text-end">₱${parseFloat(po.total_amount).toLocaleString()}</td>
                <td><span class="badge ${statusBadge}">${po.status}</span></td>
                <td>
                    <div class="btn-group">
                        ${actionButtons}
                        <button class="btn btn-sm btn-light border-0" onclick="showEditModal(${po.po_id}, ${po.supplier_id}, '${po.order_date}', ${po.total_amount}, '${po.status}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
    });
    $('#poTableBody').html(html || '<tr><td colspan="6" class="text-center py-4">No purchase orders found</td></tr>');

}
</script>

<?php require '../includes/footer.php'; ?>