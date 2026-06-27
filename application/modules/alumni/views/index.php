<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 text-primary"><i class="fas fa-graduation-cap me-2"></i><?php echo $page_title; ?></h5>
                <small class="text-muted"><?php echo $page_subtitle; ?></small>
            </div>
            <div>
                <a href="<?php echo site_url('alumni/create'); ?>" class="btn btn-primary btn-sm me-1">
                    <i class="fas fa-plus me-1"></i> Tambah Alumni
                </a>
                <a href="<?php echo site_url('alumni/import'); ?>" class="btn btn-success btn-sm me-1">
                    <i class="fas fa-file-import me-1"></i> Import Excel
                </a>
                <button class="btn btn-info btn-sm text-white" onclick="exportData()">
                    <i class="fas fa-file-export me-1"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters Sidebar -->
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-users me-1"></i> Kohort</label>
                    <select class="form-select form-select-sm" id="filter_kohort">
                        <option value="">Semua Kohort</option>
                        <?php foreach ($kohorts as $kohort): ?>
                        <option value="<?php echo $kohort['id']; ?>"><?php echo htmlspecialchars($kohort['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-university me-1"></i> Program Studi</label>
                    <select class="form-select form-select-sm" id="filter_prodi">
                        <option value="">Semua Prodi</option>
                        <?php foreach ($prodis as $prodi): ?>
                        <option value="<?php echo $prodi['id']; ?>"><?php echo htmlspecialchars($prodi['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="fas fa-briefcase me-1"></i> Status Kerja</label>
                    <select class="form-select form-select-sm" id="filter_status">
                        <?php foreach ($status_kerja_options as $value => $label): ?>
                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="fas fa-money-bill me-1"></i> Gaji Min</label>
                    <input type="number" class="form-control form-control-sm" id="filter_gaji_min" placeholder="Min">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="fas fa-search me-1"></i> Search</label>
                    <input type="text" class="form-control form-control-sm" id="filter_search" placeholder="NIM/Nama/Email">
                </div>
                <div class="col-md-12 text-end">
                    <button class="btn btn-primary btn-sm" onclick="reloadTable()">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times me-1"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="alumni_table" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="3%"><input type="checkbox" id="select_all"></th>
                            <th width="8%">NIM</th>
                            <th width="20%">Nama</th>
                            <th width="15%">Prodi</th>
                            <th width="8%">Tahun Lulus</th>
                            <th width="10%">Status Kerja</th>
                            <th width="10%">Gaji</th>
                            <th width="8%">Masa Tunggu</th>
                            <th width="18%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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

<script>
$(document).ready(function() {
    // Init DataTables with Server-Side Processing
    var table = $('#alumni_table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?php echo site_url("alumni/get_data"); ?>',
            type: 'GET',
            data: function(d) {
                d.kohort_id = $('#filter_kohort').val();
                d.prodi_id = $('#filter_prodi').val();
                d.status_kerja = $('#filter_status').val();
                d.gaji_min = $('#filter_gaji_min').val();
                d.search = $('#filter_search').val();
            }
        },
        columns: [
            { data: 0, orderable: false, className: 'text-center' },
            { data: 1, orderable: true },
            { data: 2, orderable: true },
            { data: 3, orderable: true },
            { data: 4, orderable: true },
            { data: 5, orderable: true },
            { data: 6, orderable: false },
            { data: 7, orderable: false },
            { data: 8, orderable: false, className: 'text-center' }
        ],
        order: [[4, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            zeroRecords: 'Tidak ada data alumni ditemukan',
            emptyTable: 'Belum ada data alumni'
        }
    });

    // Reload Table Function
    window.reloadTable = function() {
        table.ajax.reload();
    };

    // Clear Filters Function
    window.clearFilters = function() {
        $('#filter_kohort').val('');
        $('#filter_prodi').val('');
        $('#filter_status').val('');
        $('#filter_gaji_min').val('');
        $('#filter_search').val('');
        table.ajax.reload();
    };

    // Export Function
    window.exportData = function() {
        var params = '?kohort_id=' + encodeURIComponent($('#filter_kohort').val()) +
                     '&prodi_id=' + encodeURIComponent($('#filter_prodi').val()) +
                     '&status_kerja=' + encodeURIComponent($('#filter_status').val()) +
                     '&search=' + encodeURIComponent($('#filter_search').val());
        window.location.href = '<?php echo site_url("alumni/export"); ?>' + params;
    };

    // Delete Alumni Function
    window.deleteAlumni = function(id) {
        if (confirm('Apakah Anda yakin ingin menghapus alumni ini? Data akan dihapus secara soft delete.')) {
            $.ajax({
                url: '<?php echo site_url("alumni/delete"); ?>/' + id,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        alert('Alumni berhasil dihapus');
                        table.ajax.reload();
                    } else {
                        alert('Error: ' + res.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus alumni');
                }
            });
        }
    };

    // Select All Checkbox
    $('#select_all').on('click', function() {
        $('.alumni-checkbox').prop('checked', this.checked);
    });
});
</script>
