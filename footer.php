</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Tombol ☰: di layar lebar menyembunyikan sidebar, di layar kecil membukanya
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        document.body.classList.toggle(window.innerWidth < 992 ? 'sidebar-open' : 'sidebar-hidden');
    });
</script>
</body>
</html>