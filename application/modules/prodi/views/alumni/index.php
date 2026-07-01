<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-dark fw-bold mb-1">
                        <i class="fas fa-users me-2"></i><?= htmlspecialchars($page_title) ?>
                    </h2>
                    <p class="text-muted mb-0"><?= htmlspecialchars($page_subtitle) ?></p>
                </div>
                <div>
                    <a href="<?= site_url('prodi/alumni/add') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Alumni
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" method="get" action="<?= site_url('prodi/alumni') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Kohort</label>
                        <select class="form-select form-select-sm" name="kohort_id" id="filterKohort">
                            <option value="">Semua Kohort</option>
                            <?php foreach ($kohorts as $kohort): ?>
                            <option value="<?= $kohort['id'] ?>"><?= htmlspecialchars($kohort['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status Kerja</label>
                        <select class="form-select form-select-sm" name="status_kerja" id="filterStatus">
                            <?php foreach ($status_kerja_options as $value => $label): ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Pencarian</label>
                        <input type="text" class="form-control form-control-sm" name="search" id="filterSearch" placeholder="NIM atau Nama...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="alumniTable" class="table table-hover align-middle" style="width: 100%;">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">NIM</th>
                            <th width="20%">Nama</th>
                            <th width="15%">Program Studi</th>
                            <th width="10%">Tahun Lulus</th>
                            <th width="15%">Status Kerja</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus alumni ini?</p>
                <p class="text-muted small">Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;

$(document).ready(function() {
    // Initialize DataTable
    const table = $('#alumniTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('prodi/alumni/get_data') ?>',
            type: 'GET',
            data: function(d) {
                d.kohort_id = $('#filterKohort').val();
                d.status_kerja = $('#filterStatus').val();
                d.search = $('#filterSearch').val();
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5, className: 'text-center' }
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'Tidak ada data alumni',
            zeroRecords: 'Tidak ditemukan data yang sesuai'
        },
        order: [[0, 'asc']]
    });

    // Re-draw table on filter change
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });
});

// Delete function
function deleteAlumni(id) {
    deleteId = id;
    $('#deleteModal').modal('show');
}

// Confirm delete
$('#confirmDelete').on('click', function() {
    if (deleteId) {
        $.ajax({
            url: '<?= site_url('prodi/alumni/delete') ?>/' + deleteId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#deleteModal').modal('hide');
                    $('#alumniTable').DataTable().ajax.reload();
                    
                    // Show success toast
                    const toastHtml = `
                        <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">${response.message}</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                    `;
                    $('body').append(toastHtml);
                    setTimeout(() => $('.toast').remove(), 3000);
                } else {
                    alert(response.message || 'Gagal menghapus alumni');
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat menghapus alumni');
            }
        });
    }
});
</script>

<style>
.dataTables_wrapper .dataTables_processing {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    z-index: 9999;
}
</style>
