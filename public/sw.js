const CACHE = 'apg-v1';
self.addEventListener('install', e => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(caches.keys().then(k => Promise.all(k.filter(x => x !== CACHE).map(x => caches.delete(x))))));
self.addEventListener('fetch', e => {
  const p = new URL(e.request.url).pathname;
  if (e.request.method !== 'GET' || !/^\/(build|images|fonts|motion|storage\/thumbs)\//.test(p)) return; // never cache pages (account, lessons, admin)
  e.respondWith(fetch(e.request).then(r => { const c = r.clone(); caches.open(CACHE).then(x => x.put(e.request, c)); return r; }).catch(() => caches.match(e.request)));
});
self.addEventListener('push', e => { const d = e.data ? e.data.json() : {}; e.waitUntil(self.registration.showNotification(d.title || 'APG', { body: d.body || '' })); });
