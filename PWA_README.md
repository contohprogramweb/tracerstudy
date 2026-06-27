# Progressive Web App (PWA) - Survey Tracer Study

## 📱 Overview

Aplikasi Survey Tracer Study kini dilengkapi dengan fitur **Progressive Web App (PWA)** yang memungkinkan:
- Installasi langsung ke perangkat (tanpa app store)
- Penggunaan offline dengan penyimpanan data lokal
- Sinkronisasi otomatis saat kembali online
- Notifikasi push untuk reminder survey

---

## 📁 Struktur File

```
/workspace
├── public/
│   ├── manifest.json          # PWA Manifest (konfigurasi aplikasi)
│   ├── sw.js                  # Service Worker (offline & caching)
│   ├── pwa.js                 # jQuery wrapper untuk PWA functionality
│   └── offline.php            # Halaman fallback saat offline
│
├── application/
│   ├── controllers/
│   │   └── Pwa.php            # Controller untuk PWA endpoints
│   ├── models/
│   │   └── Survey_model.php   # Model untuk operasi survey
│   └── views/
│       └── pwa/
│           └── install.php    # Panduan instalasi PWA
│
└── database/
    └── migrations/            # Database migrations (jika ada)
```

---

## 🔧 Fitur Utama

### 1. **Installable (Add to Home Screen)**
- User dapat menginstall aplikasi langsung dari browser
- Tampil seperti aplikasi native di home screen
- Mendukung Chrome, Edge, Safari, Firefox

**Cara Install:**
- **Chrome/Edge**: Menu ⋮ > "Install App" atau "Add to Home Screen"
- **Safari (iOS)**: Share > "Add to Home Screen"
- **Firefox**: Menu ☰ > "Install"

### 2. **Offline Support**
- Form survey dapat diisi tanpa koneksi internet
- Data disimpan di localStorage browser
- Antrian request offline untuk sinkronisasi otomatis

**localStorage Schema:**
```javascript
// Progress survey
ts_survey_{survey_id}_progress: {
  answers: {...},      // Jawaban user
  timestamp: 1234567890,
  last_question: 5     // Pertanyaan terakhir
}

// Status survey
ts_survey_{survey_id}_status: 'in_progress' | 'completed'

// Token API
ts_user_token: "bearer_token_here"

// Antrian offline
ts_offline_queue: [
  {
    data: { url, method, payload },
    timestamp: 1234567890,
    attempts: 0
  }
]
```

### 3. **Background Sync**
- Sinkronisasi otomatis saat kembali online
- Retry otomatis jika gagal (max 3x percobaan)
- Notifikasi sukses/gagal sync

**Sync Logic:**
```
Online → Cek localStorage → Sync via AJAX → Clear localStorage → Show success
Offline → Simpan di localStorage → Queue untuk sync nanti
Gagal → Tetap di localStorage → Retry next time
```

### 4. **Push Notification**
- Notifikasi untuk survey baru
- Reminder untuk survey yang belum selesai
- Integrasi dengan OneSignal atau custom VAPID

---

## 🚀 Cara Menggunakan

### Di Main View (Survey Page)

Tambahkan script berikut di halaman survey Anda:

```html
<!-- Load PWA scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/public/pwa.js"></script>

<!-- Optional: Install button -->
<button data-install-pwa class="btn btn-primary">
  <i class="fas fa-download"></i> Install App
</button>

<!-- Optional: Manual sync button -->
<button data-sync-data class="btn btn-success">
  <i class="fas fa-sync"></i> Sync Data
</button>
```

### Contoh Penggunaan PWAManager

```javascript
// Save survey progress
PWAManager.saveSurveyProgress(surveyId, {
  answers: { question1: 'answer1', question2: 'answer2' },
  last_question: 2
});

// Get saved progress
const progress = PWAManager.getSurveyProgress(surveyId);
console.log(progress.answers);

// Mark as completed
PWAManager.completeSurvey(surveyId);

// Add to offline queue
PWAManager.addToOfflineQueue({
  url: '/pwa/submit',
  method: 'POST',
  payload: { survey_id: 1, answers: {...} }
});

// Request notification permission
PWAManager.requestNotificationPermission();

// Check if installed
if (PWAManager.isInstalled()) {
  console.log('App is installed!');
}
```

---

## 🛠️ Konfigurasi

### manifest.json

Edit `/public/manifest.json` sesuai kebutuhan:

```json
{
  "name": "Tracer Study Survey",
  "short_name": "Survey",
  "start_url": "/survey_builder/survey",
  "display": "standalone",
  "theme_color": "#4A90D9",
  "icons": [
    {
      "src": "/public/assets/templates/icon-192.png",
      "sizes": "192x192"
    },
    {
      "src": "/public/assets/templates/icon-512.png",
      "sizes": "512x512"
    }
  ]
}
```

### Service Worker Registration

Service Worker otomatis terdaftar saat halaman dimuat melalui `pwa.js`. Untuk manual:

```javascript
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/public/sw.js')
    .then(reg => console.log('SW registered:', reg))
    .catch(err => console.error('SW registration failed:', err));
}
```

---

## 📡 API Endpoints

### PWA Controller (`/pwa/*`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/pwa/install` | GET | Halaman panduan instalasi |
| `/pwa/submit` | POST | Submit survey response |
| `/pwa/sync` | POST | Sync offline data |
| `/pwa/get_offline_queue` | GET | Cek status antrian offline |
| `/pwa/sw_scope` | GET | Info Service Worker scope |

### Contoh Request Submit

```javascript
$.ajax({
  url: '/pwa/submit',
  type: 'POST',
  data: {
    survey_id: 1,
    respondent_id: 'user123',
    answers: {
      q1: 'answer1',
      q2: 'answer2'
    }
  },
  headers: {
    'Authorization': 'Bearer ' + PWAManager.getUserToken()
  },
  success: function(response) {
    console.log('Submitted:', response);
  }
});
```

---

## 🔔 Push Notification Setup

### Opsi 1: OneSignal (Recommended)

1. Daftar di [OneSignal](https://onesignal.com)
2. Buat app baru, pilih "Web Push"
3. Dapatkan App ID dan API Key
4. Integrasikan:

```html
<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>
<script>
  var OneSignal = window.OneSignal || [];
  OneSignal.push(function() {
    OneSignal.init({
      appId: "YOUR_ONESIGNAL_APP_ID",
    });
  });
</script>
```

### Opsi 2: Custom VAPID

1. Generate VAPID keys:
```bash
npx web-push generate-vapid-keys
```

2. Update di `sw.js`:
```javascript
applicationServerKey: 'YOUR_VAPID_PUBLIC_KEY'
```

3. Send notification dari server:
```php
use Minishlink\WebPush\WebPush;

$webPush = new WebPush([
  'VAPID' => [
    'subject' => 'mailto:admin@example.com',
    'publicKey' => 'YOUR_PUBLIC_KEY',
    'privateKey' => 'YOUR_PRIVATE_KEY'
  ]
]);

$webPush->sendNotification($subscription, 'Survey baru tersedia!');
```

---

## 🧪 Testing

### Test Offline Mode

1. Buka DevTools (F12)
2. Tab Application > Service Workers > Check "Offline"
3. Atau Network tab > Select "Offline"
4. Refresh halaman
5. Aplikasi tetap berfungsi dengan data cached

### Test Install Prompt

1. Pastikan HTTPS (atau localhost)
2. Service Worker harus aktif
3. Manifest.json valid
4. Klik tombol install atau menu browser

### Test Background Sync

```javascript
// Trigger manual sync
PWAManager.syncData();

// Or via Service Worker
navigator.serviceWorker.ready.then(reg => {
  reg.sync.register('sync-survey-data');
});
```

---

## 🐛 Troubleshooting

### Service Worker tidak terdaftar
- Pastikan diakses via HTTPS atau localhost
- Cek console untuk error
- Clear browser cache dan reload

### Install prompt tidak muncul
- User sudah install app
- Browser tidak support PWA
- Manifest.json tidak valid
- Service Worker tidak aktif

### Data tidak tersimpan offline
- localStorage penuh
- Browser membatasi storage
- Private/Incognito mode

### Sync gagal
- Token expired
- Server endpoint error
- Network masih offline

---

## 📝 Best Practices

1. **Cache Strategy**: Gunakan network-first untuk API, cache-first untuk static assets
2. **Storage Limit**: Monitor localStorage usage (max ~5-10MB)
3. **Error Handling**: Selalu handle failure case di sync
4. **User Feedback**: Beritahu user status online/offline
5. **Security**: Validate data sebelum sync ke server

---

## 📄 License

Proprietary - Survey Tracer Study

---

## 👨‍💻 Support

Untuk pertanyaan atau issue, hubungi tim development.
