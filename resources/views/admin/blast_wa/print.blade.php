@php
    $logoRelative = 'media/img/logo_cetak.jpeg';
    $logoExists = file_exists(base_path($logoRelative));
    // dompdf punya enable_remote=false (config/vendor default), jadi PDF harus
    // dikasih path file lokal langsung, bukan URL http -- browser sebaliknya
    // butuh URL biasa lewat asset(). Logo yang sama dipakai untuk logo header
    // kiri atas dan watermark background.
    $logoSrc = ($isPdf ?? false)
        ? str_replace('\\', '/', base_path($logoRelative))
        : asset($logoRelative);

@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cetakan Timbangan - {{ $ticket->no_tiket }}</title>
    <style>
        @page { size: A5; margin: 8mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #222; margin: 8mm; position: relative; max-width: 480px; }
        .watermark-wrap { position: absolute; top: 10px; left: 0; width: 100%; text-align: center; }
        .watermark-wrap img { width: 300px; opacity: 0.06; }
        .header, .section, .footer-note, .btn-print { position: relative; }
        .header { display: flex; align-items: center; margin-bottom: 10px; }
        .header-logo { width: 50px; height: 50px; object-fit: contain; margin-right: 8px; }
        .header-text { flex: 1; text-align: center; }
        .header-text h2 { margin: 0; font-size: 22px; }
        .header-text p { margin: 0; font-size: 9px; }
        hr { border: none; border-top: 1px solid #333; margin: 6px 0 10px 0; }
        .section { margin-bottom: 10px; }
        .section-title { font-weight: bold; font-size: 11px; border-bottom: 1px solid #999; padding-bottom: 2px; margin-bottom: 4px; }
        .grid2 { display: flex; flex-wrap: wrap; }
        .grid2 .line { width: 50%; box-sizing: border-box; padding: 2px 6px 2px 0; }
        .cols { display: flex; }
        .cols .col { width: 50%; box-sizing: border-box; padding-right: 6px; }
        .line .label { color: #555; }
        .footer-note { margin-top: 12px; font-size: 9px; color: #555; }
        .btn-print { margin-bottom: 10px; }
        @media print {
            .btn-print { display: none; }
            body { margin: 0 auto; }
        }
    </style>
</head>
<body>
    @if ($logoExists)
    <div class="watermark-wrap">
        <img src="{{ $logoSrc }}" alt="">
    </div>
    @endif

    <div class="header">
        @if ($logoExists)
        <img src="{{ $logoSrc }}" class="header-logo" alt="">
        @endif
        <div class="header-text">
            <h2>Hasil Timbangan Tebu</h2>
        </div>
    </div>
    <hr>

    <div class="section">
        <div class="cols">
            <div class="col">
                <div class="line"><span class="label">No Tiket</span>: {{ $ticket->no_tiket ?: '-' }}</div>
                <div class="line"><span class="label">Kode Kebun</span>: {{ $ticket->kodekebun ?: '-' }}</div>
                <div class="line"><span class="label">Nama Petani</span>: {{ $ticket->nama_petani ?: '-' }}</div>
            </div>
            <div class="col">
                <div class="line"><span class="label">No Polisi</span>: {{ $ticket->no_polisi ?: '-' }}</div>
                <div class="line"><span class="label">Nama Sopir</span>: {{ $ticket->nama_sopir ?: '-' }}</div>
                <div class="line"><span class="label">Tgl Masuk</span>: {{ $ticket->tanggal_masuk?->format('d-m-Y H:i') ?: '-' }}</div>
                {{-- <div class="line"><span class="label">Tgl Keluar</span>: {{ $ticket->tanggal_keluar?->format('d-m-Y H:i') ?: '-' }}</div> --}}
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Berat Tebu</div>
        <div class="cols">
            <div class="col">
                <div class="line"><span class="label">Berat Kotor</span>: {{ number_format($ticket->bruto ?? 0, 0, ',', '.') }} KG</div>
                <div class="line"><span class="label">Berat Truck</span>: {{ number_format($ticket->tarra ?? 0, 0, ',', '.') }} KG</div>
                <div class="line"><span class="label">Berat Bersih</span>: {{ number_format($ticket->netto ?? 0, 0, ',', '.') }} KG</div>
            </div>
            <div class="col">
                <div class="line"><span class="label">Kotoran Tebu (%)</span>: {{ number_format($ticket->trash_persen ?? 0, 0, ',', '.') }} %</div>
                <div class="line"><span class="label">Kotoran Tebu</span>: {{ number_format($ticket->trash ?? 0, 0, ',', '.') }} KG</div>
                <div class="line"><span class="label">Berat Bersih - Kotoran Tebu</span>: {{ number_format($ticket->berat_bersih ?? 0, 0, ',', '.') }} KG</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="line"><span class="label">Rendemen</span>: <strong>{{ $ticket->rendemen !== null ? $ticket->rendemen . ' %' : '-' }}</strong></div>
    </div>
    <div class="section">
        {{-- <div class="grid2">
            <div class="line"><span class="label">THP</span>: <strong>{{ $ticket->thp !== null ? 'Rp ' . number_format((float) $ticket->thp, 0, ',', '.') : '-' }}</strong></div>
        </div> --}}
        <div class="line"><span class="label">Harga Rp/Ton</span>: <strong>{{ $ticket->thp_per_ton !== null ? 'Rp ' . number_format((float) $ticket->thp_per_ton, 0, ',', '.') : '-' }}</strong></div>
        <div class="line"><span class="label">Pendapatan</span>: <strong>{{ $ticket->thp !== null ? 'Rp ' . number_format((float) $ticket->thp, 0, ',', '.') : '-' }}</strong></div>
    </div>
    <div class="footer-note">
        <?php if(date('Y') == 2026): ?>
            <strong>Catatan:</strong> Khusus tahun <?php echo date('Y'); ?>, kotoran tebu dibawah 12%, tidak dipotong ke berat bersih tebu, tetapi mulai tahun <?php echo date('Y') + 1; ?> kotoran tebu akan dipotong ke berat bersih tebu.
        <?php else: ?>
        <?php endif; ?>
    </div>

    <script>
        // Auto window.print() cuma kalau dibuka lewat tombol Print (action per
        // baris di tabel, yang menambahkan ?autoprint=1) -- BUKAN kalau halaman
        // ini dimuat di iframe tersembunyi buat screenshot bulk "Download Gambar",
        // supaya tidak muncul dialog print berkali-kali di tengah proses bulk.
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.print();
        }
    </script>
</body>
</html>
