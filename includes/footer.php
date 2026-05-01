    </div><!-- /main-content -->


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
    // Mobile sidebar toggle
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Toast notification function
    function showToast(title, message, type = 'success') {
        $('#toastTitle').text(title);
        $('#toastMessage').text(message);
        const toastEl = $('#notificationToast');
        
        // Reset classes
        toastEl.removeClass('bg-success bg-danger bg-warning text-white');
        
        if (type === 'success') {
            toastEl.addClass('bg-success text-white');
        } else if (type === 'error') {
            toastEl.addClass('bg-danger text-white');
        } else if (type === 'warning') {
            toastEl.addClass('bg-warning');
        }
        
        const toast = new bootstrap.Toast(toastEl[0]);
        toast.show();
    }

    // Currency formatter
    function formatCurrency(amount) {
        return '₱' + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Handle sidebar active state
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            if (currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>