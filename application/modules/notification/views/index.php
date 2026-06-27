<div class="container mt-4">
    <h2><?= $title ?></h2>
    <div class="list-group">
        <?php if(empty($notifications)): ?>
            <div class="alert alert-info">Belum ada notifikasi.</div>
        <?php else: ?>
            <?php foreach($notifications as $notif): ?>
                <div class="list-group-item list-group-item-action flex-column align-items-start <?= $notif->status == 'sent' ? 'list-group-item-light' : 'list-group-item-warning' ?>">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">
                            <span class="badge badge-<?= $notif->status == 'sent' ? 'success' : ($notif->status == 'failed' ? 'danger' : 'warning') ?>">
                                <?= strtoupper($notif->status) ?>
                            </span>
                            <?= ucfirst($notif->type) ?>
                        </h5>
                        <small><?= date('d M Y H:i', strtotime($notif->created_at)) ?></small>
                    </div>
                    <p class="mb-1"><?= character_limiter(strip_tags($notif->message), 100) ?></p>
                    <small>Channel: <?= strtoupper($notif->channel) ?> | Recipient: <?= $notif->recipient ?></small>
                    <?php if($notif->error_message): ?>
                        <br><small class="text-danger">Error: <?= $notif->error_message ?></small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
