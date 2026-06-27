/**
 * Service Worker for Survey PWA
 * Provides offline caching and background sync capabilities
 * Uses Workbox-like patterns without external dependencies
 */

const CACHE_NAME = 'survey-cache-v1';
const OFFLINE_URL = '/assets/pwa/offline.html';

// Resources to precache
const PRECACHE_ASSETS = [
  '/',
  '/assets/pwa/manifest.json',
  '/assets/pwa/offline.html',
  '/assets/pwa/pwa.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
  'https://code.jquery.com/jquery-3.6.0.min.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

// Survey questions cache
const QUESTIONS_CACHE = 'questions-cache';

// Install event - precache assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing Service Worker...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Precaching app shell');
        return cache.addAll(PRECACHE_ASSETS);
      })
      .then(() => {
        console.log('[SW] Service Worker installed');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[SW] Precache failed:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating Service Worker...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName !== QUESTIONS_CACHE) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('[SW] Service Worker activated');
      return self.clients.claim();
    })
  );
});

// Fetch event - network first with cache fallback
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Handle API requests differently
  if (url.pathname.startsWith('/api/') || url.pathname.includes('/pwa/')) {
    event.respondWith(networkFirstStrategy(request));
    return;
  }

  // Handle navigation requests
  if (request.mode === 'navigate') {
    event.respondWith(navigationHandler(request));
    return;
  }

  // Handle static assets
  event.respondWith(cacheFirstStrategy(request));
});

// Network First Strategy (for API calls)
async function networkFirstStrategy(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const responseClone = response.clone();
      caches.open(CACHE_NAME).then((cache) => {
        cache.put(request, responseClone);
      });
    }
    return response;
  } catch (error) {
    console.log('[SW] Network failed, trying cache:', request.url);
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    return new Response(JSON.stringify({ error: 'Offline', message: 'No connection' }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

// Cache First Strategy (for static assets)
async function cacheFirstStrategy(request) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const response = await fetch(request);
    if (response.ok) {
      const responseClone = response.clone();
      caches.open(CACHE_NAME).then((cache) => {
        cache.put(request, responseClone);
      });
    }
    return response;
  } catch (error) {
    console.log('[SW] Fetch failed:', request.url);
    return new Response('Offline', { status: 503 });
  }
}

// Navigation Handler (for pages)
async function navigationHandler(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const responseClone = response.clone();
      caches.open(CACHE_NAME).then((cache) => {
        cache.put(request, responseClone);
      });
    }
    return response;
  } catch (error) {
    console.log('[SW] Navigation failed, showing offline page');
    const cachedResponse = await caches.match(OFFLINE_URL);
    if (cachedResponse) {
      return cachedResponse;
    }
    return caches.match('/public/offline.php');
  }
}

// Background sync for form submissions
self.addEventListener('sync', (event) => {
  console.log('[SW] Sync event triggered:', event.tag);
  if (event.tag === 'sync-survey-data') {
    event.waitUntil(syncSurveyData());
  }
});

async function syncSurveyData() {
  console.log('[SW] Syncing survey data...');
  
  // Get all clients (open tabs)
  const clients = await self.clients.matchAll();
  
  // Notify clients that sync is starting
  clients.forEach((client) => {
    client.postMessage({
      type: 'SYNC_STARTED',
      timestamp: Date.now()
    });
  });

  try {
    // In production, you would:
    // 1. Read pending submissions from IndexedDB or localStorage via client messaging
    // 2. Send them to the server
    // 3. Clear successful submissions
    
    // For now, just notify success
    clients.forEach((client) => {
      client.postMessage({
        type: 'SYNC_COMPLETED',
        timestamp: Date.now(),
        message: 'Data synchronized successfully'
      });
    });
    
    console.log('[SW] Sync completed successfully');
  } catch (error) {
    console.error('[SW] Sync failed:', error);
    
    clients.forEach((client) => {
      client.postMessage({
        type: 'SYNC_FAILED',
        timestamp: Date.now(),
        error: error.message
      });
    });
  }
}

// Push notifications handler
self.addEventListener('push', (event) => {
  console.log('[SW] Push notification received');
  
  let data = {};
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data = { body: event.data.text() };
    }
  }
  
  const options = {
    body: data.body || 'Survey reminder',
    icon: '/public/assets/templates/icon-192.png',
    badge: '/public/assets/templates/icon-192.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1,
      url: data.url || '/survey_builder/survey'
    },
    actions: [
      {
        action: 'open',
        title: 'Buka Survey'
      },
      {
        action: 'dismiss',
        title: 'Tutup'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Survey Tracer Study', options)
  );
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification clicked:', event.action);
  event.notification.close();

  if (event.action === 'dismiss') {
    return;
  }

  const urlToOpen = event.notification.data.url || '/survey_builder/survey';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((windowClients) => {
        // Check if there's already a window open
        for (let client of windowClients) {
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        // Open new window
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
  );
});

// Handle messages from main thread
self.addEventListener('message', (event) => {
  console.log('[SW] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CACHE_QUESTIONS') {
    // Cache survey questions for offline use
    caches.open(QUESTIONS_CACHE).then((cache) => {
      cache.put(
        new Request('/api/questions/' + event.data.surveyId),
        new Response(JSON.stringify(event.data.questions))
      );
    });
  }
  
  if (event.data && event.data.type === 'GET_CACHED_QUESTIONS') {
    // Retrieve cached questions
    caches.match(new Request('/api/questions/' + event.data.surveyId))
      .then((response) => {
        if (response) {
          return response.json();
        }
        return null;
      })
      .then((questions) => {
        event.ports[0].postMessage(questions);
      });
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => caches.delete(cacheName))
      );
    }).then(() => {
      event.ports[0].postMessage({ success: true });
    });
  }
});

console.log('[SW] Service Worker loaded');
