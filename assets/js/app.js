document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const toggleBtn = document.getElementById('toggle-sidebar');
    const sidebar = document.getElementById('sidebar-wrapper');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        });
    }

    // Auto-dismiss alerts
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            alert.classList.remove('show');
            setTimeout(function() { alert.remove(); }, 150);
        }, 5000);
    });
});
