<div class="container mt-4">
    <h2><?= $title ?></h2>
    <form method="POST" class="card p-4 shadow-sm">
        <h5>Channel Preferensi</h5>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="channel_email" id="ch_email" value="1" <?= ($settings->channel_email ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="ch_email">Email</label>
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="channel_whatsapp" id="ch_wa" value="1" <?= ($settings->channel_whatsapp ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label" for="ch_wa">WhatsApp (Requires valid phone)</label>
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="channel_telegram" id="ch_tg" value="1" <?= ($settings->channel_telegram ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label" for="ch_tg">Telegram Bot</label>
        </div>

        <hr>
        <h5>Jenis Notifikasi</h5>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="reminder_survey" id="rem_survey" value="1" <?= ($settings->reminder_survey ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="rem_survey">Reminder Survey (H-7, H-3, H-1)</label>
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="alert_iku" id="alt_iku" value="1" <?= ($settings->alert_iku ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="alt_iku">Alert IKU Tidak Tercapai</label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Simpan Pengaturan</button>
        <a href="<?= site_url('notification/test?channel=email') ?>" class="btn btn-outline-secondary mt-3">Test Email</a>
        <a href="<?= site_url('notification/test?channel=wa') ?>" class="btn btn-outline-success mt-3">Test WA</a>
    </form>
</div>
