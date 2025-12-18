const CACHE_NAME = 'crm-kiosk-v1';
const OFFLINE_URL = '/offline.html';

// Assets to cache immediately
const PRECACHE_ASSETS = [
    '/admin/exhibition-kiosk',
    '/offline.html',
    '/css/app.css',
    '/js/app.js',
];

// Install event - cache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[ServiceWorker] Pre-caching offline page');
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[ServiceWorker] Removing old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - Network first, fallback to cache
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip Chrome extensions and other schemes
    if (!event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone the response before caching
                const responseToCache = response.clone();

                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseToCache);
                });

                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }

                    // If navigating to a page, show offline page
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }

                    // For other requests, return a basic response
                    return new Response('Offline - Resource not available', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: new Headers({
                            'Content-Type': 'text/plain'
                        })
                    });
                });
            })
    );
});

// Handle background sync for offline form submissions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-leads') {
        event.waitUntil(syncLeads());
    }
});

async function syncLeads() {
    try {
        const db = await openDB();
        const leads = await getAllPendingLeads(db);

        for (const lead of leads) {
            try {
                const response = await fetch('/admin/exhibition-kiosk/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': lead.csrfToken,
                    },
                    body: JSON.stringify(lead.data)
                });

                if (response.ok) {
                    await deleteLead(db, lead.id);
                    console.log('[ServiceWorker] Synced lead:', lead.id);
                }
            } catch (error) {
                console.error('[ServiceWorker] Failed to sync lead:', error);
            }
        }
    } catch (error) {
        console.error('[ServiceWorker] Sync failed:', error);
    }
}

// IndexedDB helpers for offline storage
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('KioskDB', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('leads')) {
                db.createObjectStore('leads', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getAllPendingLeads(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['leads'], 'readonly');
        const store = transaction.objectStore('leads');
        const request = store.getAll();

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function deleteLead(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['leads'], 'readwrite');
        const store = transaction.objectStore('leads');
        const request = store.delete(id);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}
