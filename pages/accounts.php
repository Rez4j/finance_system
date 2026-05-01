<?php
/**
 * header.php must be included first to load jQuery 3.7.1 and define '$'.
 */
require '../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Chart of Accounts</h1>
    <button class="btn btn-primary shadow-sm" onclick="showAddModal()">
        <i class="bi bi-plus-lg"></i> Add Account
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted text-uppercase">Filter by Category</label>
                <select id="typeFilter" class="form-select" onchange="loadAccounts()">
                    <option value="">All Account Types</option>
                    <option value="Asset">Asset</option>
                    <option value="Liability">Liability</option>
                    <option value="Revenue">Revenue</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="10%">ID</th>
                        <th>Account Name</th>
                        <th>Category</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody id="accountsTableBody">
                    <tr><td colspan="4" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2 text-primary"></div> Connecting to API...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Account Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="accountForm" onsubmit="event.preventDefault(); saveAccount();">
                <div class="modal-body">
                    <input type="hidden" id="account_id" name="account_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Account Name</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., Accounts Receivable">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Account Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Category</option>
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Revenue">Revenue</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow text-center p-4">
            <i class="bi bi-exclamation-triangle text-danger display-4 mb-3 d-block"></i>
            <h5 class="fw-bold">Confirm Deletion</h5>
            <p class="text-muted small">Are you sure? This action is permanent and will remove the account from all records.</p>
            <div class="d-flex gap-2 justify-content-center mt-4">
                <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger px-3" onclick="confirmDelete()">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
const API_ENDPOINT = '../api/accounts'; 
const API_KEY = 'fin_sys_2024';
let currentDeleteId = null;

$(document).ready(function() {
    loadAccounts();
});

/**
 * FETCH: GET /api/accounts
 */
function loadAccounts() {
    const typeFilter = $('#typeFilter').val();
    $.ajax({
        url: `${API_ENDPOINT}?api_key=${API_KEY}`,
        method: 'GET',
        data: { type: typeFilter },
        dataType: 'json',
        success: function(response) {
            if (response.success) renderTable(response.data);
        },
        error: function(xhr) {
            $('#accountsTableBody').html(`<tr><td colspan="4" class="text-center text-danger">Connection Error (${xhr.status})</td></tr>`);
        }
    });
}

/**
 * SAVE DISPATCHER: Decides between POST and PUT
 */
function saveAccount() {
    const id = $('#account_id').val();
    const data = {
        name: $('#name').val(),
        type: $('#type').val()
    };

    if (id) {
        updateAccount(id, data);
    } else {
        createAccount(data);
    }
}

/**
 * CREATE: POST /api/accounts
 */
function createAccount(data) {
    $.ajax({
        url: `${API_ENDPOINT}?api_key=${API_KEY}`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data), // API expects $input from body
        success: function(response) {
            handleActionSuccess(response, 'Account created');
        }
    });
}

/**
 * UPDATE: PUT /api/accounts?id={id}
 */
function updateAccount(id, data) {
    $.ajax({
        url: `${API_ENDPOINT}?api_key=${API_KEY}&id=${id}`, // Passing id to match API $id variable
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            handleActionSuccess(response, 'Account updated');
        }
    });
}

/**
 * DELETE: DELETE /api/accounts?id={id}
 */
function showDeleteModal(id) {
    currentDeleteId = id;
    $('#deleteModal').modal('show');
}

function confirmDelete() {
    $.ajax({
        url: `${API_ENDPOINT}?api_key=${API_KEY}&id=${currentDeleteId}`,
        method: 'DELETE',
        success: function(response) {
            $('#deleteModal').modal('hide');
            loadAccounts();
            showToast('Deleted', response.message, 'success');
        }
    });
}

/**
 * UI HELPERS
 */
function handleActionSuccess(response, defaultMsg) {
    if (response.success) {
        $('#accountModal').modal('hide');
        loadAccounts();
        showToast('Success', response.message || defaultMsg, 'success');
    } else {
        showToast('Error', response.message, 'danger');
    }
}

function showAddModal() {
    $('#accountForm')[0].reset();
    $('#account_id').val('');
    $('#modalTitle').text('Add Account');
    $('#accountModal').modal('show');
}

function showEditModal(id, name, type) {
    $('#account_id').val(id);
    $('#name').val(name);
    $('#type').val(type);
    $('#modalTitle').text('Edit Account');
    $('#accountModal').modal('show');
}

function renderTable(data) {
    let html = '';
    data.forEach(account => {
        const safeName = account.name.replace(/'/g, "\\'");
        html += `
            <tr>
                <td>${account.account_id}</td>
                <td class="fw-bold">${account.name}</td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary">${account.type}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-warning border-0" onclick="showEditModal(${account.account_id}, '${safeName}', '${account.type}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="showDeleteModal(${account.account_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
    });
    $('#accountsTableBody').html(html || '<tr><td colspan="4" class="text-center text-muted">No accounts found</td></tr>');
}
</script>

<?php require_once '../includes/footer.php'; ?>