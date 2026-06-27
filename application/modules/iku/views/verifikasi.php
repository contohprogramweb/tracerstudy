<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-check-circle"></i> <?= $page_title ?></h2>
            
            <a href="<?= site_url('iku/dashboard') ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline">
                <div class="form-group mr-3">
                    <label for="status" class="mr-2">Status:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="kohort_id" class="mr-2">Kohort:</label>
                    <select name="kohort_id" id="kohort_id" class="form-control">
                        <option value="">Semua Kohort</option>
                        <?php foreach ($kohorts as $kohort): ?>
                            <option value="<?= $kohort['id'] ?>" <?= $kohort_id == $kohort['id'] ? 'selected' : '' ?>>
                                <?= $kohort['nama'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <!-- Verification List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Verifikasi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Request</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Jenis Verifikasi</th>
                            <th>Status</th>
                            <th>Verifikator</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($verifications)): ?>
                            <?php foreach ($verifications as $idx => $verif): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($verif['created_at'])) ?></td>
                                    <td><code><?= $verif['nim'] ?></code></td>
                                    <td><?= $verif['nama_lengkap'] ?></td>
                                    <td>
                                        <span class="badge badge-secondary"><?= ucfirst(str_replace('_', ' ', $verif['jenis_verifikasi'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $verif['status'] === 'approved' ? 'success' : ($verif['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($verif['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= $verif['verifikator_nama'] ?: '-' ?></td>
                                    <td><?= $verif['catatan'] ?: '-' ?></td>
                                    <td>
                                        <?php if ($verif['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success btn-approve" 
                                                    data-id="<?= $verif['id'] ?>"
                                                    data-alumni="<?= $verif['alumni_id'] ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-reject" 
                                                    data-id="<?= $verif['id'] ?>"
                                                    data-alumni="<?= $verif['alumni_id'] ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data verifikasi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Catatan -->
<div class="modal fade" id="verificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Verifikasi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="verifId">
                <input type="hidden" id="verifAction">
                <div class="form-group">
                    <label for="catatan">Catatan (opsional):</label>
                    <textarea class="form-control" id="catatan" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnConfirm">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Handle Approve button
        $('.btn-approve').click(function() {
            $('#verifId').val($(this).data('id'));
            $('#verifAction').val('approve');
            $('#verificationModal').modal('show');
        });

        // Handle Reject button
        $('.btn-reject').click(function() {
            $('#verifId').val($(this).data('id'));
            $('#verifAction').val('reject');
            $('#verificationModal').modal('show');
        });

        // Handle Confirm button
        $('#btnConfirm').click(function() {
            const verifId = $('#verifId').val();
            const action = $('#verifAction').val();
            const catatan = $('#catatan').val();

            $.post('<?= site_url('iku/approveVerification') ?>', {
                action: action,
                catatan: catatan
            }, function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json');
        });
    });
</script>
