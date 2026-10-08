document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            alert.classList.remove('show');
            setTimeout(function() { alert.remove(); }, 150);
        }, 5000);
    });
});
