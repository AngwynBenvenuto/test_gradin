/**
 * Service worker khusus halaman /tracking.
 *
 * Didaftarkan HANYA dari resources/views/front/tracking.blade.php dengan
 * { scope: '<base>/tracking' }. Scope menentukan DOKUMEN mana yang dikontrol,
 * jadi /admin, /login, dsb tidak pernah dikontrol worker ini. Sebaliknya semua
 * request yang berasal DARI halaman tracking (termasuk /media/*) tetap lewat
 * handler fetch di bawah, sehingga CSS/JS/font bisa ikut di-cache.
 *
 * PENTING: file ini dilayani Apache sebagai file statis; PHP TIDAK dieksekusi
 * di sini, jadi jangan pernah menaruh tag PHP di dalamnya -- ia akan terkirim
 * mentah ke browser. Semua URL diturunkan dari self.registration.scope, yang
 * berisi URL absolut halaman tracking (mis. https://contoh.com/tracking).
 */
const CACHE = 'wgi-tracking-v1';

const scopeUrl = () => new URL(self.registration.scope);
// '/wgi/tracking' -> '/wgi/media/' (atau '/tracking' -> '/media/' di root domain)
const mediaPath = () => new URL('./media/', self.registration.scope).pathname;

/**
 * Aset di media/ di-cache-bust dengan ?v=uniqid() yang berubah SETIAP request
 * (lihat app/Classes/config/client_modules.php). Kalau cache di-key dengan URL
 * lengkap, cache tidak pernah kena dan tumbuh tanpa batas. Jadi buang querynya.
 */
const cacheKey = (url) => new Request(url.origin + url.pathname);

const isAsset = (url) =>
    url.origin === scopeUrl().origin && url.pathname.startsWith(mediaPath());

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE);
        // Precache dokumen tracking saja, supaya navigasi tetap membalas 200
        // saat offline (syarat installable Chrome). Aset menyusul lewat runtime
        // cache di handler fetch pada kunjungan pertama.
        await cache.add(new Request(self.registration.scope, { cache: 'reload' }));
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names.map((n) => (n === CACHE ? null : caches.delete(n))));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return; // POST dsb: biarkan lewat apa adanya

    const url = new URL(req.url);

    // 1) Navigasi halaman: jaringan dulu, jatuh ke dokumen ter-cache saat offline.
    if (req.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                return await fetch(req);
            } catch (err) {
                return (await caches.match(self.registration.scope)) || Response.error();
            }
        })());
        return;
    }

    // 2) Aset statis di media/: cache-first, lalu perbarui diam-diam di belakang
    //    (stale-while-revalidate) supaya versi baru terambil pada load berikutnya.
    if (isAsset(url)) {
        event.respondWith((async () => {
            const cache = await caches.open(CACHE);
            const key = cacheKey(url);
            const cached = await cache.match(key);

            const network = fetch(req)
                .then((res) => {
                    if (res && res.ok) cache.put(key, res.clone());
                    return res;
                })
                .catch(() => null);

            if (cached) {
                event.waitUntil(network); // revalidate tanpa menahan respons
                return cached;
            }
            return (await network) || Response.error();
        })());
        return;
    }

    // 3) Sisanya (data GPS live /tracking/trucks & /tracking/route, tile peta,
    //    Mapbox) sengaja tidak disentuh: posisi truk tidak boleh basi.
});
