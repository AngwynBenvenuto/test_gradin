(function () {
    'use strict';

    var STORAGE_PREFIX = 'absensiMandiri.';

    function readKey(key) {
        try {
            return window.localStorage.getItem(STORAGE_PREFIX + key);
        } catch (e) {
            return null;
        }
    }

    function writeKey(key, value) {
        try {
            window.localStorage.setItem(STORAGE_PREFIX + key, value);
        } catch (e) {
            // localStorage tidak tersedia (mode privat dsb) -- diamkan,
            // getProfile() akan selalu null dan gate akan muncul lagi.
        }
    }

    /**
     * crypto.randomUUID() cuma ada di secure context (HTTPS atau
     * localhost) -- testing kamera sungguhan biasanya dilakukan dari HP
     * lewat alamat IP LAN (http://, bukan localhost), yaitu insecure
     * context. Tanpa fallback ini, crypto.randomUUID() throw di dalam
     * saveProfile() (dipanggil dari submit form profil) SEBELUM baris
     * yang menyembunyikan #profile-gate -- jadi halaman nyangkut selamanya
     * di form profil, tidak pernah sampai ke tab Absen/kamera.
     */
    function randomId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return 'web-' + crypto.randomUUID().slice(0, 8);
        }
        return 'web-' + Math.random().toString(16).slice(2, 10);
    }

    /**
     * null bila profil belum lengkap (nama/peran belum diisi).
     */
    function getProfile() {
        var officerName = readKey('officerName');
        var role = readKey('role');
        if (!officerName || !role) {
            return null;
        }
        return {
            officerId: readKey('officerId'),
            officerName: officerName,
            role: role,
            deviceId: readKey('deviceId'),
        };
    }

    /**
     * Simpan nama+peran. officerId/deviceId dibuat sekali saja (tidak
     * pernah menimpa id yang sudah ada).
     */
    function saveProfile(name, role) {
        writeKey('officerName', name);
        writeKey('role', role);
        if (!readKey('officerId')) {
            writeKey('officerId', randomId());
        }
        if (!readKey('deviceId')) {
            writeKey('deviceId', randomId());
        }
    }

    function initProfileGate() {
        var gate = document.getElementById('profile-gate');
        if (!gate) return;

        if (getProfile() !== null) {
            gate.classList.add('hidden');
            return;
        }

        gate.classList.remove('hidden');
        var form = document.getElementById('profile-gate-form');
        if (!form) return;

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var name = document.getElementById('profile-name').value.trim();
            var role = document.getElementById('profile-role').value.trim();
            if (!name || !role) return;

            saveProfile(name, role);
            gate.classList.add('hidden');
        });
    }

    function pad2(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function formatDateTime(date) {
        return pad2(date.getDate()) + '-' + pad2(date.getMonth() + 1) + '-' + date.getFullYear()
            + ' ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes()) + ':' + pad2(date.getSeconds());
    }

    function tipeLabel(tipe) {
        return tipe === 'checkIn' ? 'Check In' : 'Check Out';
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * window.AbsensiMandiriBase (diisi blade dari url('/absensi'))
     * selalu dipakai sebagai dasar -- JANGAN pernah path root-relative
     * ("/absensi/...") di sini, karena itu selalu resolve dari
     * root domain di browser, bukan dari subfolder tempat app ini
     * terpasang (lihat AbsensiMandiriPageTest::test_exposes_absolute_base_url_for_api_calls).
     */
    function apiUrl(path) {
        return window.AbsensiMandiriBase + path;
    }

    /**
     * Server tidak selalu balas JSON -- token CSRF yang kedaluwarsa (419)
     * atau error lain (413, 500) balas halaman HTML, dan response.json()
     * pada body itu throw. Tanpa ini, semua error non-JSON cuma muncul
     * sebagai "gagal terhubung ke server" yang menyesatkan (lihat Review
     * Fix: non-JSON error handling).
     */
    function parseJsonResponse(response) {
        if (response.status === 419) {
            return Promise.resolve({
                ok: false,
                message: 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.',
                json: null,
            });
        }
        return response.json().then(function (json) {
            return {
                ok: response.ok,
                message: (json && json.message) || ('Gagal (status ' + response.status + ').'),
                json: json,
            };
        }).catch(function () {
            return { ok: false, message: 'Server error (status ' + response.status + ').', json: null };
        });
    }

    // Sisi terpanjang foto setelah diskalakan, sebelum watermark digambar.
    // Kamera HP modern menghasilkan 3000-4000px (beberapa MB per foto);
    // tanpa ini, download .sql dgn banyak foto base64 bisa membengkak
    // sampai kehabisan memory di server (lihat AttendanceMobileSqlExport).
    var MAX_PHOTO_DIMENSION = 1600;

    /**
     * Gambar `source` (apa pun yang bisa digambar ke canvas: <video>,
     * <img>, canvas lain) ke canvas baru (diskalakan turun bila lebih
     * besar dari MAX_PHOTO_DIMENSION berdasar `sourceWidth`/`sourceHeight`
     * aslinya), lalu timpa dengan `lines` (teks watermark, ditumpuk dari
     * bawah dengan bar gelap semi-transparan di belakangnya supaya tetap
     * terbaca). Hasil di-resolve sebagai Blob JPEG (kualitas 0.85).
     */
    function drawWatermarked(source, sourceWidth, sourceHeight, lines) {
        return new Promise(function (resolve, reject) {
            var scale = Math.min(1, MAX_PHOTO_DIMENSION / Math.max(sourceWidth, sourceHeight));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(sourceWidth * scale);
            canvas.height = Math.round(sourceHeight * scale);
            var ctx = canvas.getContext('2d');
            ctx.drawImage(source, 0, 0, canvas.width, canvas.height);

            var fontSize = Math.max(14, Math.round(canvas.width * 0.025));
            var lineHeight = Math.round(fontSize * 1.6);
            var padding = Math.round(fontSize * 0.5);
            ctx.font = fontSize + 'px sans-serif';
            ctx.textBaseline = 'bottom';

            var barHeight = lines.length * lineHeight + padding;
            ctx.fillStyle = 'rgba(0, 0, 0, 0.55)';
            ctx.fillRect(0, canvas.height - barHeight, canvas.width, barHeight);

            ctx.fillStyle = '#ffffff';
            lines.forEach(function (line, index) {
                var y = canvas.height - padding - (lines.length - 1 - index) * lineHeight;
                ctx.fillText(line, padding, y);
            });

            canvas.toBlob(function (blob) {
                if (blob) {
                    resolve(blob);
                } else {
                    reject(new Error('toBlob gagal'));
                }
            }, 'image/jpeg', 0.85);
        });
    }

    /**
     * Sama seperti drawWatermarked(), tapi sumbernya file hasil
     * <input type="file"> -- dipakai sebagai fallback kalau kamera live
     * (getUserMedia) tidak tersedia di browser.
     */
    function buildWatermarkedPhoto(file, lines) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                drawWatermarked(img, img.naturalWidth, img.naturalHeight, lines).then(resolve, reject);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Gagal memuat foto'));
            };
            img.src = url;
        });
    }

    function requestLocation() {
        return new Promise(function (resolve, reject) {
            if (!('geolocation' in navigator)) {
                reject(new Error('Browser tidak mendukung lokasi'));
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                },
                function () {
                    reject(new Error('Gagal mengambil lokasi. Pastikan izin lokasi diaktifkan.'));
                },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        });
    }

    function renderAbsenTab() {
        var panel = document.getElementById('tab-absen');
        if (!panel) return;

        panel.innerHTML =
            '<div class="absen-type-buttons">' +
            '<button type="button" data-tipe="checkIn">Check In</button> ' +
            '<button type="button" data-tipe="checkOut">Check Out</button>' +
            '</div>' +
            '<form id="absen-form" class="hidden">' +
            '<p id="absen-tipe-label"></p>' +
            '<div id="absen-camera-wrap">' +
            '<video id="absen-video" autoplay playsinline muted></video>' +
            '<button type="button" id="absen-capture">Ambil Foto</button>' +
            '</div>' +
            '<p id="absen-camera-error" style="display:none"></p>' +
            '<input type="file" id="absen-foto" accept="image/*" capture="environment" style="display:none">' +
            '<canvas id="absen-preview" style="display:none;max-width:100%"></canvas>' +
            '<button type="button" id="absen-retake" style="display:none">Ambil Ulang</button>' +
            '<textarea id="absen-activity" placeholder="Kegiatan"></textarea>' +
            '<p id="absen-location-status"></p>' +
            '<button type="button" id="absen-location-retry" style="display:none">Coba lagi ambil lokasi</button>' +
            '<button type="submit" id="absen-submit" disabled>Kirim</button>' +
            '<p id="absen-message"></p>' +
            '</form>';

        var form = panel.querySelector('#absen-form');
        var tipeLabelEl = panel.querySelector('#absen-tipe-label');
        var cameraWrap = panel.querySelector('#absen-camera-wrap');
        var videoEl = panel.querySelector('#absen-video');
        var captureButton = panel.querySelector('#absen-capture');
        var cameraErrorEl = panel.querySelector('#absen-camera-error');
        var fotoInput = panel.querySelector('#absen-foto');
        var preview = panel.querySelector('#absen-preview');
        var retakeButton = panel.querySelector('#absen-retake');
        var activityInput = panel.querySelector('#absen-activity');
        var locationStatus = panel.querySelector('#absen-location-status');
        var locationRetry = panel.querySelector('#absen-location-retry');
        var submitButton = panel.querySelector('#absen-submit');
        var messageEl = panel.querySelector('#absen-message');

        // rawCanvas: frame kamera live yang sudah diambil (belum ada
        // watermark). rawFile: fallback kalau getUserMedia tidak tersedia.
        // Hanya salah satu yang terisi pada satu waktu.
        var state = { tipe: null, location: null, rawCanvas: null, rawFile: null, stream: null };

        function hasPhoto() {
            return !!(state.rawCanvas || state.rawFile);
        }

        function updateSubmitEnabled() {
            submitButton.disabled = !(hasPhoto() && state.location && activityInput.value.trim());
        }

        // `at`: waktu yang dipakai di watermark HARUS sama dengan waktu
        // yang dikirim ke server (formData 'waktu'), jadi dibangun dari
        // Date yang sama persis, bukan "new Date()" baru yang keburu
        // berbeda beberapa detik.
        function watermarkLines(at) {
            var profile = getProfile();
            return [
                profile.officerName + ' (' + profile.officerId + ')',
                tipeLabel(state.tipe) + '  ' + formatDateTime(at),
                'Lat: ' + (state.location ? state.location.lat.toFixed(6) : '-')
                    + '  Lng: ' + (state.location ? state.location.lng.toFixed(6) : '-'),
            ];
        }

        // Bangun ulang foto final (dengan watermark waktu `at`) dari
        // apa pun sumber foto yang tersimpan -- dipanggil utk preview
        // (langsung setelah ambil foto) maupun saat submit (dgn waktu
        // final), supaya watermark tidak pernah "beku" di waktu yg salah.
        function rebuildPhoto(at) {
            if (state.rawCanvas) {
                return drawWatermarked(state.rawCanvas, state.rawCanvas.width, state.rawCanvas.height, watermarkLines(at));
            }
            if (state.rawFile) {
                return buildWatermarkedPhoto(state.rawFile, watermarkLines(at));
            }
            return Promise.reject(new Error('Belum ada foto'));
        }

        function showPreviewBlob(blob) {
            var url = URL.createObjectURL(blob);
            var img = new Image();
            img.onload = function () {
                preview.width = img.naturalWidth;
                preview.height = img.naturalHeight;
                preview.getContext('2d').drawImage(img, 0, 0);
                preview.style.display = '';
                URL.revokeObjectURL(url);
            };
            img.src = url;
        }

        function stopCamera() {
            if (state.stream) {
                state.stream.getTracks().forEach(function (track) { track.stop(); });
                state.stream = null;
            }
            videoEl.srcObject = null;
        }

        function startCamera() {
            cameraErrorEl.style.display = 'none';
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                // Browser lama / insecure context: getUserMedia tidak ada
                // sama sekali -- pakai <input type=file> sebagai fallback
                // (boleh menunjukkan pilihan kamera/galeri OS, tapi tetap
                // lebih baik daripada fitur tidak jalan sama sekali).
                cameraWrap.style.display = 'none';
                fotoInput.style.display = '';
                return;
            }
            cameraWrap.style.display = '';
            fotoInput.style.display = 'none';
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .then(function (stream) {
                    state.stream = stream;
                    videoEl.srcObject = stream;
                    // autoplay+muted+playsinline biasanya cukup, tapi
                    // panggil play() juga sebagai jaga-jaga di browser yang
                    // lebih strict soal autoplay stream kamera.
                    videoEl.play().catch(function () {});
                })
                .catch(function () {
                    cameraErrorEl.textContent = 'Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan, atau pilih foto manual di bawah.';
                    cameraErrorEl.style.display = '';
                    cameraWrap.style.display = 'none';
                    fotoInput.style.display = '';
                });
        }

        function doRequestLocation() {
            locationStatus.textContent = 'Mengambil lokasi...';
            locationRetry.style.display = 'none';
            requestLocation().then(function (loc) {
                state.location = loc;
                locationStatus.textContent = 'Lokasi: ' + loc.lat.toFixed(6) + ', ' + loc.lng.toFixed(6);
                updateSubmitEnabled();
            }).catch(function (err) {
                locationStatus.textContent = err.message;
                locationRetry.style.display = '';
            });
        }

        locationRetry.addEventListener('click', doRequestLocation);

        panel.querySelectorAll('.absen-type-buttons button').forEach(function (button) {
            button.addEventListener('click', function () {
                state.tipe = button.getAttribute('data-tipe');
                tipeLabelEl.textContent = tipeLabel(state.tipe);
                form.classList.remove('hidden');
                messageEl.textContent = '';
                doRequestLocation();
                startCamera();
            });
        });

        captureButton.addEventListener('click', function () {
            var rawCanvas = document.createElement('canvas');
            rawCanvas.width = videoEl.videoWidth;
            rawCanvas.height = videoEl.videoHeight;
            rawCanvas.getContext('2d').drawImage(videoEl, 0, 0);

            state.rawCanvas = rawCanvas;
            stopCamera();
            cameraWrap.style.display = 'none';
            retakeButton.style.display = '';
            updateSubmitEnabled();

            rebuildPhoto(new Date()).then(showPreviewBlob).catch(function (err) {
                messageEl.textContent = err.message;
            });
        });

        retakeButton.addEventListener('click', function () {
            state.rawCanvas = null;
            preview.style.display = 'none';
            retakeButton.style.display = 'none';
            updateSubmitEnabled();
            startCamera();
        });

        // Fallback <input type=file>: cuma aktif/terlihat kalau
        // getUserMedia gagal/tidak tersedia (lihat startCamera()).
        fotoInput.addEventListener('change', function () {
            var file = fotoInput.files && fotoInput.files[0];
            if (!file) return;

            state.rawFile = file;
            updateSubmitEnabled();

            rebuildPhoto(new Date()).then(showPreviewBlob).catch(function (err) {
                messageEl.textContent = err.message;
            });
        });

        activityInput.addEventListener('input', updateSubmitEnabled);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var profile = getProfile();
            if (!profile || !hasPhoto() || !state.location) return;

            submitButton.disabled = true;
            messageEl.textContent = 'Mengirim...';

            var now = new Date();

            rebuildPhoto(now).then(function (photoBlob) {
                var formData = new FormData();
                formData.append('officer_id', profile.officerId);
                formData.append('officer_name', profile.officerName);
                formData.append('device_id', profile.deviceId);
                formData.append('tipe', state.tipe);
                formData.append('waktu', now.toISOString());
                formData.append('activity', activityInput.value.trim());
                formData.append('lat', state.location.lat);
                formData.append('lng', state.location.lng);
                formData.append('foto', photoBlob, 'absen.jpg');

                return fetch(apiUrl('/catat'), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: formData,
                });
            }).then(function (response) {
                return parseJsonResponse(response);
            }).then(function (result) {
                if (result.ok) {
                    messageEl.textContent = 'Absen berhasil tersimpan.';
                    form.classList.add('hidden');
                    form.reset();
                    preview.style.display = 'none';
                    retakeButton.style.display = 'none';
                    state.rawCanvas = null;
                    state.rawFile = null;
                    state.location = null;
                    stopCamera();
                    document.dispatchEvent(new CustomEvent('absensi:recorded'));
                } else {
                    messageEl.textContent = result.message;
                    submitButton.disabled = false;
                }
            }).catch(function () {
                messageEl.textContent = 'Gagal terhubung ke server. Coba lagi.';
                submitButton.disabled = false;
            });
        });
    }

    // Harus sama dengan AttendanceMobile::RETENTION_MONTHS (app/Models/
    // AttendanceMobile.php) -- JS tidak bisa import konstanta PHP, jadi
    // nilainya di-hardcode di sini juga, cuma dipakai untuk membatasi
    // tanggal paling lama yang bisa dipilih di filter (data di luar itu
    // sudah di-nonaktifkan lewat "Bersihkan data lama").
    var RETENTION_MONTHS = 2;

    function toIsoDate(date) {
        return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
    }

    function startOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth(), 1);
    }

    function subMonths(date, months) {
        return new Date(date.getFullYear(), date.getMonth() - months, date.getDate());
    }

    function fetchRecords(from, to) {
        return fetch(apiUrl('/records?from=' + from + '&to=' + to), {
            headers: { 'Accept': 'application/json' },
        })
            .then(function (response) {
                return parseJsonResponse(response).then(function (result) {
                    if (!result.ok) {
                        throw new Error(result.message);
                    }
                    return result.json.data;
                });
            });
    }

    /**
     * Group by officer_name + tanggal kalender (lokal) dari `waktu`.
     * firstIn = checkIn paling awal di hari itu, lastOut = checkOut
     * paling akhir. durationMinutes null bila salah satu tidak ada.
     */
    function buildRecap(records) {
        var groups = {};
        var order = [];

        records.forEach(function (r) {
            var waktu = new Date(r.waktu);
            var dateKey = toIsoDate(waktu);
            var key = r.officer_name + '|' + dateKey;

            if (!groups[key]) {
                groups[key] = {
                    officerName: r.officer_name,
                    date: new Date(waktu.getFullYear(), waktu.getMonth(), waktu.getDate()),
                    firstIn: null,
                    lastOut: null,
                };
                order.push(key);
            }

            var group = groups[key];
            if (r.tipe === 'checkIn' && (!group.firstIn || waktu < group.firstIn)) {
                group.firstIn = waktu;
            }
            if (r.tipe === 'checkOut' && (!group.lastOut || waktu > group.lastOut)) {
                group.lastOut = waktu;
            }
        });

        return order.map(function (key) {
            var g = groups[key];
            return {
                officerName: g.officerName,
                date: g.date,
                firstIn: g.firstIn,
                lastOut: g.lastOut,
                durationMinutes: (g.firstIn && g.lastOut) ? (g.lastOut - g.firstIn) / 60000 : null,
            };
        });
    }

    function renderRiwayatTab() {
        var panel = document.getElementById('tab-riwayat');
        if (!panel) return;

        var today = new Date();
        var defaultFrom = toIsoDate(startOfMonth(today));
        var defaultTo = toIsoDate(today);
        var minFrom = toIsoDate(subMonths(today, RETENTION_MONTHS));

        panel.innerHTML =
            '<div class="riwayat-filters">' +
            '<input type="date" id="riwayat-from" min="' + minFrom + '" value="' + defaultFrom + '">' +
            '<input type="date" id="riwayat-to" value="' + defaultTo + '">' +
            '</div>' +
            '<div class="riwayat-mode">' +
            '<button type="button" id="riwayat-mode-list" class="active">Daftar</button>' +
            '<button type="button" id="riwayat-mode-recap">Rekap</button>' +
            '</div>' +
            '<div id="riwayat-list"></div>' +
            '<div id="riwayat-recap" style="display:none"></div>' +
            '<label><input type="checkbox" id="riwayat-include-photos" checked> Sertakan foto saat download</label>' +
            '<div class="riwayat-actions">' +
            '<button type="button" id="riwayat-download">Download .sql</button>' +
            '<button type="button" id="riwayat-cleanup">Bersihkan data lama</button>' +
            '</div>' +
            '<p id="riwayat-toast"></p>';

        var fromInput = panel.querySelector('#riwayat-from');
        var toInput = panel.querySelector('#riwayat-to');
        var modeListBtn = panel.querySelector('#riwayat-mode-list');
        var modeRecapBtn = panel.querySelector('#riwayat-mode-recap');
        var listEl = panel.querySelector('#riwayat-list');
        var recapEl = panel.querySelector('#riwayat-recap');
        var includePhotos = panel.querySelector('#riwayat-include-photos');
        var downloadBtn = panel.querySelector('#riwayat-download');
        var cleanupBtn = panel.querySelector('#riwayat-cleanup');
        var toastEl = panel.querySelector('#riwayat-toast');

        var currentRecords = [];

        function showDetail(r) {
            var overlay = document.createElement('div');
            overlay.className = 'riwayat-detail-overlay';

            var box = document.createElement('div');
            box.className = 'riwayat-detail-box';

            if (r.foto_url) {
                var img = document.createElement('img');
                img.src = r.foto_url;
                box.appendChild(img);
            }

            var name = document.createElement('strong');
            name.textContent = r.officer_name + ' · ' + tipeLabel(r.tipe);
            var meta = document.createElement('div');
            meta.textContent = formatDateTime(new Date(r.waktu));
            var coords = document.createElement('div');
            coords.textContent = (r.lat != null && r.lng != null)
                ? ('Lat: ' + Number(r.lat).toFixed(6) + '  Lng: ' + Number(r.lng).toFixed(6))
                : 'Lokasi tidak tercatat';
            var activity = document.createElement('div');
            activity.textContent = r.activity || '';
            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.textContent = 'Tutup';
            closeBtn.addEventListener('click', function () { overlay.remove(); });

            box.appendChild(name);
            box.appendChild(meta);
            box.appendChild(coords);
            box.appendChild(activity);
            box.appendChild(closeBtn);
            overlay.appendChild(box);
            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) overlay.remove();
            });
            document.body.appendChild(overlay);
        }

        function renderList() {
            listEl.innerHTML = '';
            currentRecords.forEach(function (r) {
                var row = document.createElement('div');
                row.className = 'riwayat-row';
                row.addEventListener('click', function () { showDetail(r); });

                var name = document.createElement('strong');
                name.textContent = r.officer_name;

                var meta = document.createElement('div');
                meta.textContent = tipeLabel(r.tipe) + ' · ' + formatDateTime(new Date(r.waktu));

                var coords = document.createElement('div');
                coords.textContent = (r.lat != null && r.lng != null)
                    ? ('Lat: ' + Number(r.lat).toFixed(6) + '  Lng: ' + Number(r.lng).toFixed(6))
                    : '';

                var activity = document.createElement('div');
                activity.textContent = r.activity || '';

                row.appendChild(name);
                row.appendChild(meta);
                row.appendChild(coords);
                row.appendChild(activity);

                if (r.foto_url) {
                    var img = document.createElement('img');
                    img.src = r.foto_url;
                    img.width = 48;
                    img.height = 48;
                    row.appendChild(img);
                }

                listEl.appendChild(row);
            });
        }

        function renderRecap() {
            recapEl.innerHTML = '';
            buildRecap(currentRecords).forEach(function (g) {
                var row = document.createElement('div');
                row.className = 'riwayat-row';

                var name = document.createElement('strong');
                name.textContent = g.officerName + ' · ' + toIsoDate(g.date);

                var times = document.createElement('div');
                times.textContent = 'Masuk: ' + (g.firstIn ? formatDateTime(g.firstIn) : '-')
                    + ' / Keluar: ' + (g.lastOut ? formatDateTime(g.lastOut) : '-');

                var duration = document.createElement('div');
                duration.textContent = (g.durationMinutes !== null)
                    ? ('Durasi: ' + Math.round(g.durationMinutes) + ' menit') : '';

                row.appendChild(name);
                row.appendChild(times);
                row.appendChild(duration);
                recapEl.appendChild(row);
            });
        }

        function refresh() {
            fetchRecords(fromInput.value, toInput.value).then(function (data) {
                currentRecords = data;
                renderList();
                renderRecap();
            }).catch(function (err) {
                toastEl.textContent = err.message;
            });
        }

        fromInput.addEventListener('change', refresh);
        toInput.addEventListener('change', refresh);

        modeListBtn.addEventListener('click', function () {
            modeListBtn.classList.add('active');
            modeRecapBtn.classList.remove('active');
            listEl.style.display = '';
            recapEl.style.display = 'none';
        });

        modeRecapBtn.addEventListener('click', function () {
            modeRecapBtn.classList.add('active');
            modeListBtn.classList.remove('active');
            listEl.style.display = 'none';
            recapEl.style.display = '';
        });

        downloadBtn.addEventListener('click', function () {
            var params = 'from=' + fromInput.value + '&to=' + toInput.value
                + '&include_photos=' + (includePhotos.checked ? '1' : '0');
            window.location.href = apiUrl('/download?' + params);
        });

        cleanupBtn.addEventListener('click', function () {
            // Hitung dulu berapa baris yang akan terdampak sebelum nanya
            // konfirmasi, supaya user tidak menekan "Hapus" buta -- pakai
            // endpoint /records yang sudah ada (dari awal zaman sampai
            // sehari sebelum batas retensi), lalu cuma hitung yang
            // officer_id-nya milik fitur ini (sama seperti scope cleanup()
            // di server).
            var cutoff = toIsoDate(subMonths(new Date(), RETENTION_MONTHS));
            fetchRecords('2000-01-01', cutoff).then(function (data) {
                var count = data.filter(function (r) {
                    return r.officer_id && r.officer_id.indexOf('web-') === 0;
                }).length;

                if (!window.confirm('Akan menonaktifkan ' + count + ' data absensi yang sudah lebih dari 2 bulan. Lanjutkan?')) {
                    return;
                }

                return fetch(apiUrl('/bersihkan'), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                }).then(function (response) {
                    return parseJsonResponse(response);
                }).then(function (result) {
                    toastEl.textContent = result.ok
                        ? (result.json.affected + ' data dibersihkan.')
                        : result.message;
                    refresh();
                });
            }).catch(function () {
                toastEl.textContent = 'Gagal terhubung ke server.';
            });
        });

        document.addEventListener('absensi:recorded', refresh);

        refresh();
    }

    function renderProfilTab() {
        var panel = document.getElementById('tab-profil');
        if (!panel) return;

        panel.innerHTML =
            '<form id="profil-form">' +
            '<label>Nama<input type="text" id="profil-name"></label>' +
            '<label>Peran<input type="text" id="profil-role"></label>' +
            '<button type="submit">Simpan</button>' +
            '<p id="profil-message"></p>' +
            '</form>';

        var nameInput = panel.querySelector('#profil-name');
        var roleInput = panel.querySelector('#profil-role');
        var messageEl = panel.querySelector('#profil-message');

        function fill() {
            var profile = getProfile();
            if (!profile) return;
            nameInput.value = profile.officerName;
            roleInput.value = profile.role;
        }
        fill();

        panel.querySelector('#profil-form').addEventListener('submit', function (event) {
            event.preventDefault();
            var name = nameInput.value.trim();
            var role = roleInput.value.trim();
            if (!name || !role) return;

            saveProfile(name, role);
            messageEl.textContent = 'Profil disimpan.';
        });
    }

    function initTabs() {
        var buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = button.getAttribute('data-tab');

                buttons.forEach(function (b) { b.classList.remove('active'); });
                button.classList.add('active');

                document.querySelectorAll('.absensi-panel').forEach(function (panel) {
                    panel.classList.toggle('active', panel.id === targetId);
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initProfileGate();
        initTabs();
        renderAbsenTab();
        renderRiwayatTab();
        renderProfilTab();
    });

    window.AbsensiMandiri = {
        getProfile: getProfile,
        saveProfile: saveProfile,
        buildWatermarkedPhoto: buildWatermarkedPhoto,
        buildRecap: buildRecap,
    };
})();
