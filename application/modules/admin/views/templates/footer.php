        <!-- Footer -->
        <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
            <div class="row">
                <div class="col-md-6">
                    &copy; <?= date('Y') ?> Sistem Tracer Study. All rights reserved.
                </div>
                <div class="col-md-6 text-end">
                    Version 3.1
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Initialize DataTables if exists
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('table.data-table').DataTable();
            }
        });
    </script>

    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= base_url($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
