        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
// Sidebar toggle
var sidebar = document.getElementById('sidebar-wrapper');
var overlay = document.getElementById('sidebarOverlay');
var toggleBtn = document.getElementById('toggle-sidebar');

if (toggleBtn) {
    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });
}

if (overlay) {
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
}

// Fermer le sidebar en cliquant sur un lien (mobile)
document.querySelectorAll('.sidebar-link').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth < 992) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
});
</script>
</body>
</html>
