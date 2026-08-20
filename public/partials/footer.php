<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    if (!toggle || !sidebar || !backdrop) return;

    function closeSidebar() {
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    });
    backdrop.addEventListener('click', closeSidebar);
})();
</script>
</body>
</html>
