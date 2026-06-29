<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="fas fa-shield-alt me-2"></i>Audit Trail System</h5>
                <small class="text-muted">Monitoring Aktivitas Sistem - Immutable Log</small>
            </div>
            <div>
                <button class="btn btn-success btn-sm" onclick="exportData('excel')">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-folder me-1"></i> Module</label>
                    <select class="form-select form-select-sm" id="filter_module">
                        <option value="">All Modules</option>
                        <?php foreach ($modules as $module): ?>
                        <option value="<?php echo htmlspecialchars($module); ?>"><?php echo ucfirst(htmlspecialchars($module)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-tasks me-1"></i> Action</label>
                    <select class="form-select form-select-sm" id="filter_action">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $action): ?>
                        <option value="<?php echo htmlspecialchars($action); ?>"><?php echo strtoupper(htmlspecialchars($action)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="fas fa-calendar-alt me-1"></i> Date Range</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="text" class="form-control" id="date_range" placeholder="Select Date Range">
                    </div>
                    <input type="hidden" id="date_from">
                    <input type="hidden" id="date_to">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm w-100" onclick="reloadTable()">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div>
                    <strong>Business Rule BR-SEC-001:</strong> Activity log tidak dapat dihapus oleh siapapun. 
                    Semua aktivitas sistem tercatat secara permanen untuk keperluan audit dan compliance.
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="audit_table" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="15%"><i class="fas fa-clock me-1"></i> Timestamp</th>
                            <th width="15%"><i class="fas fa-user me-1"></i> User</th>
                            <th width="10%"><i class="fas fa-tasks me-1"></i> Action</th>
                            <th width="10%"><i class="fas fa-database me-1"></i> Module</th>
                            <th width="35%"><i class="fas fa-align-left me-1"></i> Description</th>
                            <th width="15%" class="text-center"><i class="fas fa-cog me-1"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-search-plus me-2"></i>Audit Log Detail
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_body">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Include DataTables & Dependencies -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

<script>
$(document).ready(function() {
    // Init Date Range Picker
    $('#date_range').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: "Apply",
            cancelLabel: "Cancel",
            customRangeLabel: "Custom Range"
        }
    }, function(start, end) {
        $('#date_from').val(start.format('YYYY-MM-DD'));
        $('#date_to').val(end.format('YYYY-MM-DD'));
    });

    // Set default range (Last 30 Days)
    $('#date_range').data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
    $('#date_range').data('daterangepicker').setEndDate(moment());
    $('#date_from').val(moment().subtract(29, 'days').format('YYYY-MM-DD'));
    $('#date_to').val(moment().format('YYYY-MM-DD'));

    // Init DataTables with Server-Side Processing
    var table = $('#audit_table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?php echo site_url("admin/audit/get_data"); ?>',
            type: 'GET',
            data: function(d) {
                d.module = $('#filter_module').val();
                d.action = $('#filter_action').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        columns: [
            { data: 0, orderable: true },
            { data: 1, orderable: true },
            { data: 2, orderable: true },
            { data: 3, orderable: true },
            { data: 4, orderable: false },
            { data: 5, orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            zeroRecords: 'Tidak ada data audit trail ditemukan',
            emptyTable: 'Belum ada aktivitas yang tercatat'
        },
        drawCallback: function() {
            // Re-initialize tooltips after table draw
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Reload Table Function
    window.reloadTable = function() {
        table.ajax.reload();
    };

    // Export Function
    window.exportData = function(format) {
        var params = '?format=' + format;
        if($('#filter_module').val()) params += '&module=' + encodeURIComponent($('#filter_module').val());
        if($('#filter_action').val()) params += '&action=' + encodeURIComponent($('#filter_action').val());
        if($('#date_from').val()) params += '&date_from=' + encodeURIComponent($('#date_from').val());
        if($('#date_to').val()) params += '&date_to=' + encodeURIComponent($('#date_to').val());
        
        window.location.href = '<?php echo site_url("admin/audit/export"); ?>' + params;
    };

    // View Detail Handler
    $(document).on('click', '.view-log', function() {
        var id = $(this).data('id');
        
        $('#modal_body').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        $('#detailModal').modal('show');
        
        $.ajax({
            url: '<?php echo site_url("admin/audit/view"); ?>/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    var data = res.data;
                    var html = `
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <tbody>
                                    <tr class="table-light">
                                        <th width="30%"><i class="fas fa-clock me-2"></i>Timestamp</th>
                                        <td><strong>${escapeHtml(data.created_at)}</strong></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-user me-2"></i>User</th>
                                        <td>${escapeHtml(data.username)} <span class="badge bg-info">${escapeHtml(data.role)}</span></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-globe me-2"></i>IP Address</th>
                                        <td><code>${escapeHtml(data.ip_address)}</code></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-tasks me-2"></i>Action Type</th>
                                        <td><span class="badge bg-${getBadgeColor(data.action)}">${escapeHtml(data.action).toUpperCase()}</span></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-database me-2"></i>Module</th>
                                        <td>${escapeHtml(data.table_name || '-')}</td>
                                    </tr>
                                    ${data.record_id ? `
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>Record ID</th>
                                        <td>${escapeHtml(data.record_id)}</td>
                                    </tr>
                                    ` : ''}
                                    <tr>
                                        <th><i class="fas fa-align-left me-2"></i>Description</th>
                                        <td><pre class="bg-light p-3 mb-0 rounded" style="max-height: 200px; overflow-y: auto;">${escapeHtml(data.description)}</pre></td>
                                    </tr>
                                    ${data.old_values ? `
                                    <tr class="table-warning">
                                        <th><i class="fas fa-history me-2"></i>Old Values</th>
                                        <td><pre class="bg-warning bg-opacity-10 p-3 mb-0 rounded" style="max-height: 300px; overflow-y: auto;">${syntaxHighlight(data.old_values)}</pre></td>
                                    </tr>
                                    ` : ''}
                                    ${data.new_values ? `
                                    <tr class="table-success">
                                        <th><i class="fas fa-arrow-right me-2"></i>New Values</th>
                                        <td><pre class="bg-success bg-opacity-10 p-3 mb-0 rounded" style="max-height: 300px; overflow-y: auto;">${syntaxHighlight(data.new_values)}</pre></td>
                                    </tr>
                                    ` : ''}
                                    <tr>
                                        <th><i class="fas fa-desktop me-2"></i>User Agent</th>
                                        <td><small class="text-muted">${escapeHtml(data.user_agent || '-')}</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                    $('#modal_body').html(html);
                } else {
                    $('#modal_body').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' + escapeHtml(res.message) + '</div>');
                }
            },
            error: function() {
                $('#modal_body').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to load log detail</div>');
            }
        });
    });
    
    // Helper Functions
    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function getBadgeColor(action) {
        var colors = {
            'create': 'success',
            'insert': 'success',
            'update': 'warning',
            'edit': 'warning',
            'delete': 'danger',
            'login': 'info',
            'logout': 'secondary',
            'export': 'primary',
            'import': 'primary',
            'sync': 'dark'
        };
        return colors[action.toLowerCase()] || 'light';
    }
    
    function syntaxHighlight(json) {
        try {
            var obj = JSON.parse(json);
            json = JSON.stringify(obj, null, 2);
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                var cls = 'text-primary';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'text-danger fw-bold';
                    } else {
                        cls = 'text-success';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'text-primary fw-bold';
                } else if (/null/.test(match)) {
                    cls = 'text-muted';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        } catch (e) {
            return escapeHtml(json);
        }
    }
});
</script>

<style>
.daterangepicker {
    z-index: 9999 !important;
}
.table thead th {
    vertical-align: middle;
    font-weight: 600;
}
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    font-size: 0.85rem;
}
</style>

    </div>
</div>

<script>
// Toggle sidebar untuk mobile
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});
</script>
