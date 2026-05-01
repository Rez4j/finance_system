<?php
require_once '../includes/header.php';

// Fetch all transactions on page load
$stmt = $pdo->query("SELECT t.*, a.name as account_name, a.type as account_type 
                     FROM transactions t 
                     JOIN accounts a ON t.account_id = a.account_id 
                     ORDER BY t.trans_date DESC, t.transaction_id DESC");
$transactions = $stmt->fetchAll();

// Fetch running balance
$balanceStmt = $pdo->query("SELECT a.account_id, a.name, a.type,
                            COALESCE(SUM(CASE WHEN t.type = 'Debit' THEN t.amount ELSE 0 END), 0) as total_debit,
                            COALESCE(SUM(CASE WHEN t.type = 'Credit' THEN t.amount ELSE 0 END), 0) as total_credit
                            FROM accounts a
                            LEFT JOIN transactions t ON a.account_id = t.account_id
                            GROUP BY a.account_id
                            ORDER BY a.type, a.name");
$balances = $balanceStmt->fetchAll();

// Fetch accounts for dropdown
$accountsStmt = $pdo->query("SELECT * FROM accounts ORDER BY name");
$allAccounts = $accountsStmt->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-dark">Journal Entries</h1>
        <p class="text-muted mb-0">Record and track all financial transactions</p>
    </div>
    <button class="btn btn-primary" onclick="showAddModal()">
        <i class="bi bi-plus-lg"></i> Add Transaction
    </button>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Account Type</label>
                <select id="accountTypeFilter" class="form-select" onchange="filterTransactions()">
                    <option value="">All Account Types</option>
                    <option value="Asset">Asset</option>
                    <option value="Liability">Liability</option>
                    <option value="Revenue">Revenue</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">From Date</label>
                <input type="date" id="dateFrom" class="form-control" onchange="filterTransactions()">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">To Date</label>
                <input type="date" id="dateTo" class="form-control" onchange="filterTransactions()">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Transaction Type</label>
                <select id="transTypeFilter" class="form-select" onchange="filterTransactions()">
                    <option value="">All Types</option>
                    <option value="Debit">Debit</option>
                    <option value="Credit">Credit</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="transactionsTableBody">
                    <?php foreach($transactions as $trans): ?>
                    <tr>
                        <td><?php echo $trans['transaction_id']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($trans['trans_date'])); ?></td>
                        <td><?php echo htmlspecialchars($trans['account_name']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $trans['type'] == 'Debit' ? 'danger' : 'success'; ?> bg-opacity-10 text-<?php echo $trans['type'] == 'Debit' ? 'danger' : 'success'; ?>">
                                <?php echo $trans['type']; ?>
                            </span>
                        </td>
                        <td class="fw-semibold">₱<?php echo number_format($trans['amount'], 2); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($trans['description'] ?? ''); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="showEditModal(<?php echo $trans['transaction_id']; ?>, <?php echo $trans['account_id']; ?>, '<?php echo $trans['trans_date']; ?>', <?php echo $trans['amount']; ?>, '<?php echo $trans['type']; ?>', '<?php echo htmlspecialchars($trans['description'] ?? '', ENT_QUOTES); ?>')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="showDeleteModal(<?php echo $trans['transaction_id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($transactions) == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No transactions found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <nav>
            <ul class="pagination" id="pagination"></ul>
        </nav>
    </div>
</div>

<!-- Running Balance -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold">
            <i class="bi bi-calculator text-primary me-2"></i>Running Balance Summary
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Type</th>
                        <th>Total Debit</th>
                        <th>Total Credit</th>
                        <th>Net Balance</th>
                    </tr>
                </thead>
                <tbody id="balanceTableBody">
                    <?php foreach($balances as $balance): 
                        $netBalance = $balance['total_credit'] - $balance['total_debit'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($balance['name']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo $balance['type']; ?></span></td>
                        <td class="text-danger">₱<?php echo number_format($balance['total_debit'], 2); ?></td>
                        <td class="text-success">₱<?php echo number_format($balance['total_credit'], 2); ?></td>
                        <td class="<?php echo $netBalance >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                            ₱<?php echo number_format($netBalance, 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transaction Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="transactionForm">
                    <input type="hidden" id="transaction_id" name="transaction_id">
                    <div class="mb-3">
                        <label for="account_id" class="form-label">Account</label>
                        <select class="form-select" id="account_id" name="account_id" required>
                            <option value="">Select Account</option>
                            <?php foreach($allAccounts as $acc): ?>
                            <option value="<?php echo $acc['account_id']; ?>">
                                <?php echo htmlspecialchars($acc['name']); ?> (<?php echo $acc['type']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="trans_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="trans_date" name="trans_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Debit">Debit</option>
                            <option value="Credit">Credit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTransaction()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this transaction?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteTransaction()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentTransactionId = null;

function filterTransactions() {
    const filters = {
        action: 'fetch_all',
        account_type: $('#accountTypeFilter').val(),
        date_from: $('#dateFrom').val(),
        date_to: $('#dateTo').val(),
        trans_type: $('#transTypeFilter').val()
    };
    
    $.ajax({
        url: '../ajax/transactions.php',
        method: 'POST',
        data: filters,
        success: function(response) {
            if (response.success) {
                let html = '';
                if (response.data.length === 0) {
                    html = '<tr><td colspan="7" class="text-center text-muted py-4">No transactions found</td></tr>';
                } else {
                    response.data.forEach(trans => {
                        html += `
                            <tr>
                                <td>${trans.transaction_id}</td>
                                <td>${new Date(trans.trans_date).toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric'})}</td>
                                <td>${trans.account_name}</td>
                                <td><span class="badge bg-${trans.type === 'Debit' ? 'danger' : 'success'} bg-opacity-10 text-${trans.type === 'Debit' ? 'danger' : 'success'}">${trans.type}</span></td>
                                <td class="fw-semibold">₱${parseFloat(trans.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="text-muted">${trans.description || ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="showEditModal(${trans.transaction_id}, ${trans.account_id}, '${trans.trans_date}', ${trans.amount}, '${trans.type}', '${(trans.description || '').replace(/'/g, "\\'")}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="showDeleteModal(${trans.transaction_id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                }
                $('#transactionsTableBody').html(html);
            }
        }
    });
    
    // Update running balance
    $.ajax({
        url: '../ajax/transactions.php',
        method: 'POST',
        data: { action: 'fetch_running_balance' },
        success: function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(balance => {
                    const netBalance = balance.total_credit - balance.total_debit;
                    html += `
                        <tr>
                            <td>${balance.name}</td>
                            <td><span class="badge bg-secondary">${balance.type}</span></td>
                            <td class="text-danger">₱${parseFloat(balance.total_debit).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            <td class="text-success">₱${parseFloat(balance.total_credit).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            <td class="${netBalance >= 0 ? 'text-success' : 'text-danger'} fw-bold">
                                ₱${netBalance.toLocaleString('en-US', {minimumFractionDigits: 2})}
                            </td>
                        </tr>`;
                });
                $('#balanceTableBody').html(html);
            }
        }
    });
}

function showAddModal() {
    $('#transactionForm')[0].reset();
    $('#transaction_id').val('');
    $('#modalTitle').text('Add Transaction');
    $('#transactionModal').modal('show');
}

function showEditModal(id, accountId, date, amount, type, description) {
    $('#transaction_id').val(id);
    $('#account_id').val(accountId);
    $('#trans_date').val(date);
    $('#amount').val(amount);
    $('#type').val(type);
    $('#description').val(description);
    $('#modalTitle').text('Edit Transaction');
    $('#transactionModal').modal('show');
}

function saveTransaction() {
    const id = $('#transaction_id').val();
    const action = id ? 'edit' : 'add';
    const data = {
        action: action,
        account_id: $('#account_id').val(),
        trans_date: $('#trans_date').val(),
        amount: $('#amount').val(),
        type: $('#type').val(),
        description: $('#description').val()
    };
    if (id) data.transaction_id = id;
    
    $.ajax({
        url: '../ajax/transactions.php',
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                $('#transactionModal').modal('hide');
                location.reload();
                showToast('Success', response.message, 'success');
            } else {
                showToast('Error', response.message, 'error');
            }
        }
    });
}

function showDeleteModal(id) {
    currentTransactionId = id;
    $('#deleteModal').modal('show');
}

function deleteTransaction() {
    $.ajax({
        url: '../ajax/transactions.php',
        method: 'POST',
        data: {
            action: 'delete',
            transaction_id: currentTransactionId
        },
        success: function(response) {
            if (response.success) {
                $('#deleteModal').modal('hide');
                location.reload();
                showToast('Success', response.message, 'success');
            } else {
                showToast('Error', response.message, 'error');
            }
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>