const CACHE_NAME = "noric-app-v1.0"; // Ganti versi ini jika ada update kode
const STATIC_ASSETS = [
  "assets/image/logo-noric.png",
  "assets/image/favicon.ico",
  "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap",
  "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css",
  // Jangan cache file PHP dinamis di sini (index.php, dashboard.php) agar data selalu fresh
];

// 1. Install Service Worker & Cache Aset Statis
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("[Service Worker] Caching static assets");
      return cache.addAll(STATIC_ASSETS);
    }),
  );
  self.skipWaiting(); // Langsung aktifkan SW baru tanpa menunggu tutup tab
});

// 2. Activate & Hapus Cache Lama (Penting untuk Update)
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            console.log("[Service Worker] Clearing old cache");
            return caches.delete(cache);
          }
        }),
      );
    }),
  );
  self.clients.claim();
});

// 3. Fetch Strategy: Network First (Untuk Data Akurat), Fallback ke Cache
self.addEventListener("fetch", (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Jika request ke halaman PHP (Data dinamis) -> Network First
  if (url.origin === location.origin && url.pathname.endsWith(".php")) {
    event.respondWith(
      fetch(req)
        .then((response) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(req, response.clone());
            return response;
          });
        })
        .catch(() => caches.match(req)), // Jika offline, ambil dari cache
    );
  }
  // Jika request aset statis (Font, Gambar, CSS) -> Cache First (Lebih Cepat)
  else {
    event.respondWith(
      caches.match(req).then((cachedResponse) => {
        return cachedResponse || fetch(req);
      }),
    );
  }
});
