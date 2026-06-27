<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Builder - List Survey</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/font-awesome.min.css') ?>">
    <style>
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-draft { background-color: #ffc107; color: #000; }
        .status-published { background-color: #28a745; color: #fff; }
        .status-archived { background-color: #6c757d; color: #fff; }
        .card-hover:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s; }
        .core-count { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="fa fa-clipboard-list"></i> Survey Builder</h2>
                    <a href="<?= site_url('survey_builder/create') ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Buat Survey Baru
                    </a>
                </div>
            </div>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Daftar Survey</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($surveys)): ?>
                            <div class="text-center py-5">
                                <i class="fa fa-clipboard-list fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada survey</h5>
                                <p class="text-muted">Mulai buat survey pertama Anda dengan klik tombol "Buat Survey Baru"</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="30%">Judul Survey</th>
                                            <th width="20%">Deskripsi</th>
                                            <th width="10%">Status</th>
                                            <th width="10%" class="text-center">Pertanyaan</th>
                                            <th width="10%" class="text-center">Inti</th>
                                            <th width="15%">Tanggal Dibuat</th>
                                            <th width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($surveys as $survey): ?>
                                            <tr class="card-hover">
                                                <td><?= $no++ ?></td>
                                                <td><strong><?= htmlspecialchars($survey->title) ?></strong></td>
                                                <td class="text-truncate" style="max-width: 200px;">
                                                    <?= htmlspecialchars(substr($survey->description ?? '', 0, 50)) ?>...
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?= $survey->status ?>">
                                                        <?= ucfirst($survey->status) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center"><?= $survey->total_questions ?? 0 ?></td>
                                                <td class="text-center">
                                                    <span class="<?= ($survey->core_questions ?? 0) >= 20 ? 'text-success' : 'text-danger' ?>">
                                                        <strong><?= $survey->core_questions ?? 0 ?></strong>/20
                                                    </span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($survey->created_at)) ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($survey->status === 'draft'): ?>
                                                            <a href="<?= site_url('survey_builder/edit/' . $survey->id) ?>" 
                                                               class="btn btn-info" title="Edit">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <a href="<?= site_url('survey_builder/questions/' . $survey->id) ?>" 
                                                               class="btn btn-warning" title="Kelola Pertanyaan">
                                                                <i class="fa fa-question-circle"></i>
                                                            </a>
                                                            <a href="<?= site_url('survey_builder/logic/' . $survey->id) ?>" 
                                                               class="btn btn-purple" title="Logic Jump">
                                                                <i class="fa fa-random"></i>
                                                            </a>
                                                            <button onclick="publishSurvey(<?= $survey->id ?>)" 
                                                                    class="btn btn-success" title="Publish">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <a href="<?= site_url('survey_builder/preview/' . $survey->id) ?>" 
                                                           class="btn btn-secondary" title="Preview">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <?php if ($survey->status === 'draft'): ?>
                                                            <a href="<?= site_url('survey_builder/duplicate/' . $survey->id) ?>" 
                                                               class="btn btn-outline-primary" title="Duplikat">
                                                                <i class="fa fa-copy"></i>
                                                            </a>
                                                            <button onclick="deleteSurvey(<?= $survey->id ?>)" 
                                                                    class="btn btn-danger" title="Hapus">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        function publishSurvey(id) {
            if (!confirm('Yakin ingin mempublikasikan survey ini? Pastikan sudah memiliki minimal 20 pertanyaan inti.')) {
                return;
            }

            $.ajax({
                url: '<?= site_url('survey_builder/publish/') ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }

        function deleteSurvey(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus survey ini? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            $.ajax({
                url: '<?= site_url('survey_builder/delete/') ?>' + id,
                type: 'DELETE',
                success: function() {
                    location.reload();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    alert('Error: ' + msg);
                }
            });
        }
    </script>
</body>
</html>
