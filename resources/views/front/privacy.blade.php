<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi - WGI Driver</title>
    <link rel="icon" href="{{ asset('media/img/favicon/favicon.ico') }}" type="image/x-icon">
    <style>
        :root { --wgi-green: #189e31; --wgi-blue: #1a73e8; --text: #1f2937; --muted: #6b7280; }
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--text); background: #f7f8fa; line-height: 1.65;
        }
        .header {
            background: linear-gradient(135deg, var(--wgi-green), #0f7a24);
            color: #fff; padding: 36px 20px 44px;
        }
        .header .inner { max-width: 760px; margin: 0 auto; }
        .header h1 { margin: 0 0 6px; font-size: 26px; }
        .header p { margin: 0; opacity: .92; font-size: 14px; }
        .wrap { max-width: 760px; margin: -24px auto 60px; padding: 0 20px; }
        .card {
            background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,.06);
            padding: 32px clamp(20px, 5vw, 44px);
        }
        h2 { font-size: 18px; margin: 32px 0 10px; color: var(--wgi-green); }
        h2:first-child { margin-top: 0; }
        p, li { font-size: 14.5px; color: #374151; }
        p.subjudul { color:#fff }
        ul { padding-left: 20px; margin: 8px 0; }
        li { margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0 4px; font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #eef0f2; vertical-align: top; }
        th { color: var(--muted); font-weight: 600; width: 32%; }
        .muted { color: var(--muted); font-size: 13px; }
        a { color: var(--wgi-blue); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .badge {
            display: inline-block; background: rgba(255,255,255,.18); border-radius: 20px;
            padding: 3px 12px; font-size: 12px; margin-bottom: 12px;
        }
        footer { text-align: center; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="inner">
            <span class="badge">WGI Driver &amp; WGI Tracking</span>
            <h1>Kebijakan Privasi</h1>
            <p class="subjudul">PT. Wahana Gula Investama (WGI) &middot; Berlaku sejak {{ \Carbon\Carbon::parse('2026-08-22')->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <div class="wrap">
        <div class="card">
            <p align="justify">
                Kebijakan Privasi ini menjelaskan bagaimana PT. Wahana Gula Investama ("<strong>WGI</strong>", "kami")
                mengumpulkan, menggunakan, dan melindungi data saat Anda menggunakan aplikasi <strong>WGI Driver</strong>
                (aplikasi mobile untuk sopir angkutan tebu) dan sistem <strong>WGI Tracking</strong> yang menyertainya.
                Dengan menggunakan aplikasi/layanan ini, Anda menyetujui pengumpulan dan penggunaan data sesuai
                kebijakan ini.
            </p>

            <h2>1. Data yang Kami Kumpulkan</h2>
            <table>
                <tr><th>Lokasi GPS</th><td>Koordinat (latitude/longitude), kecepatan, dan arah kendaraan, dikirim secara berkala selama sesi pengangkutan/tracking sedang berlangsung untuk memantau perjalanan tiket panen dari kebun ke pabrik.</td></tr>
                <tr><th>Foto</th><td>Foto lahan/kebun yang diambil sopir dari aplikasi saat memulai tracking, sebagai bukti dokumentasi pengangkutan.</td></tr>
                <tr><th>Data kendaraan &amp; sopir</th><td>Nomor polisi kendaraan dan nama sopir, dikaitkan ke tiket panen (harvest order) yang sedang berjalan.</td></tr>
                <tr><th>Data akun</th><td>Nama pengguna, nomor telepon/WhatsApp, dan kredensial login yang dipakai untuk masuk ke aplikasi.</td></tr>
                <tr><th>ID perangkat/instalasi</th><td>ID unik yang digenerate dan disimpan lokal di aplikasi (bukan IMEI) untuk mengenali sesi/permintaan dari instalasi aplikasi yang sama, mis. mencegah pembuatan tiket ganda akibat tap berulang.</td></tr>
                <tr><th>Data teknis</th><td>Informasi standar seperti alamat IP dan waktu akses, yang tercatat otomatis oleh server saat aplikasi berkomunikasi dengan sistem kami.</td></tr>
            </table>

            <h2>2. Bagaimana Data Digunakan</h2>
            <ul>
                <li>Melacak posisi kendaraan secara realtime untuk keperluan operasional pengangkutan tebu (estimasi waktu/jarak tempuh ke pabrik, monitoring armada).</li>
                <li>Memverifikasi dan mengelola tiket panen (harvest order), termasuk pembuatan tiket, validasi QR, dan pencatatan progres pengangkutan.</li>
                <li>Dokumentasi &amp; audit operasional (foto lahan, riwayat lokasi).</li>
                <li>Komunikasi terkait tiket/pengangkutan lewat WhatsApp (mis. notifikasi QR tiket, pengingat).</li>
                <li>Menjaga keamanan sistem dan mencegah penyalahgunaan (mis. pembuatan data ganda/spam).</li>
            </ul>

            <h2>3. Berbagi Data dengan Pihak Ketiga</h2>
            <p align="justify">
                Kami <strong>tidak menjual</strong> data pribadi Anda kepada pihak manapun. Data hanya dibagikan
                dengan pihak ketiga sebatas yang diperlukan untuk menjalankan layanan, yaitu:
            </p>
            <ul>
                <li><strong>Mapbox</strong> &mdash; layanan peta &amp; perhitungan rute/jarak tempuh, menerima koordinat lokasi kendaraan secara terbatas untuk keperluan tersebut.</li>
                <li><strong>Penyedia layanan pesan WhatsApp</strong> &mdash; untuk mengirim notifikasi tiket/QR terkait pengangkutan.</li>
            </ul>
            <p align="justify">Data dapat diungkapkan lebih lanjut apabila diwajibkan oleh hukum yang berlaku.</p>

            <h2>4. Penyimpanan &amp; Keamanan Data</h2>
            <p align="justify">
                Data disimpan di server milik/dikelola PT. Wahana Gula Investama selama diperlukan untuk keperluan
                operasional, audit, dan kewajiban hukum. Kami menerapkan langkah keamanan yang wajar untuk melindungi
                data dari akses, perubahan, atau pengungkapan yang tidak sah.
            </p>

            <h2>5. Hak Anda</h2>
            <p align="justify">
                Anda dapat menghubungi kami (lihat bagian Kontak) untuk meminta informasi mengenai data yang kami
                simpan terkait Anda, permintaan koreksi data, atau pertanyaan lain seputar privasi.
            </p>

            <h2>6. Perubahan Kebijakan Ini</h2>
            <p align="justify">
                Kebijakan ini dapat diperbarui sewaktu-waktu mengikuti perubahan layanan atau ketentuan hukum yang
                berlaku. Perubahan akan tercermin lewat tanggal "Berlaku sejak" di atas.
            </p>

            <h2>7. Kontak</h2>
            <p align="justify">
                Pertanyaan seputar Kebijakan Privasi ini dapat disampaikan ke:<br>
                PT. Wahana Gula Investama &middot; Pemalang, Jawa Tengah<br>
                Email: <a href="mailto:informationtechwgi@gmail.com">informationtechwgi@gmail.com</a>
            </p>
        </div>
        <footer class="muted">&copy; {{ date('Y') }} PT. Wahana Gula Investama. Semua hak dilindungi.</footer>
    </div>
</body>
</html>
