@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="content-wrapper" id="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="widget-box">
                <div class="widget-title clearfix">
                    <h1>@yield('title')</h1>
                    <div class="buttons">
                        <button id="btnSyncLan" class="btn btn-outline-primary">
                            <i class="fas fa-sync"></i> Sync ke LAN
                        </button>
                    </div>
                </div>
                <div class="widget-content clearfix">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tbl" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="8%">Action</th>
                                    <th>No. Tiket</th>
                                    <th>No. SPP</th>
                                    <th>Kebun / Petak</th>
                                    <th>Sopir</th>
                                    <th>Tanggal Panen</th>
                                    <th>Rit / Tonase</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Tiket --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Tiket</h5>
        <button type="button" class="btn-close btnClose" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="d_progress_steps" class="mb-3 d-flex flex-wrap gap-1"></div>
        <div id="d_photos_box" class="mb-3" style="display:none;">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <strong>Foto Tracking</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnDownloadPhotos">
              <i class="fas fa-download"></i> Download Foto
            </button>
          </div>
          <div id="d_photos_gallery" class="d-flex flex-wrap gap-2"></div>
        </div>
        <table class="table table-sm table-borderless mb-0">
          <tr><td width="30%">No. Tiket</td><td id="d_no_tiket">-</td></tr>
          <tr><td>No. SPP</td><td id="d_no_spp">-</td></tr>
          <tr><td>Kebun / Petak</td><td id="d_kebun_petak">-</td></tr>
          <tr><td>Sopir</td><td id="d_sopir_name">-</td></tr>
          <tr><td>Petani</td><td id="d_petani_name">-</td></tr>
          <tr><td>Kepala Kerja</td><td id="d_kepalakerja_name">-</td></tr>
          <tr><td>Officer</td><td id="d_officer_name">-</td></tr>
          <tr><td>Tanggal Panen</td><td id="d_tanggal_panen">-</td></tr>
          <tr><td>Tanggal Tanam</td><td id="d_tanggal_tanam">-</td></tr>
          <tr><td>Jumlah Rit / Tonase</td><td id="d_rit_tonase">-</td></tr>
          <tr><td>TCH</td><td id="d_tch">-</td></tr>
          <tr><td>Zona</td><td id="d_zona">-</td></tr>
          <tr><td>Luas (Ha / Net)</td><td id="d_luas">-</td></tr>
          <tr><td>Jenis Tebu / Tebang</td><td id="d_jenis">-</td></tr>
          <tr><td>Rendemen</td><td id="d_rendemen">-</td></tr>
          <tr><td>Trash</td><td id="d_trash">-</td></tr>
          <tr><td>Keterangan</td><td id="d_keterangan">-</td></tr>
          <tr><td>Progress</td><td id="d_progress_label">-</td></tr>
          <tr><td>Status</td><td id="d_status">-</td></tr>
          <tr><td>Dibuat / Diperbarui</td><td id="d_timestamps">-</td></tr>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btnClose" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('extend_script')
    <link href="{{ asset('media/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('media/plugins/sweetalert/sweetalert.min.js') }}"></script>
@endsection

@section('footer_js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': AppHelper.csrfToken } });

    var TABLE = '#tbl';
    var BASE_URL = "{{ url('/tiket') }}";
    var SYNC_LAN_URL = "{{ route('admin.harvest_order.sync_lan') }}";

    function progressBadge(label, progress) {
        var map = { 0: 'bg-secondary', 7: 'bg-success' };
        var cls = map[progress] || 'bg-info';
        return '<span class="badge ' + cls + '">' + (label || '-') + '</span>';
    }

    function statusBadge(row) {
        if (row.is_cancelled) return '<span class="badge bg-danger">Batal</span>';
        if (row.is_completed) return '<span class="badge bg-success">Selesai</span>';
        if (!row.status) return '<span class="badge bg-secondary">Nonaktif</span>';
        return '<span class="badge bg-primary">Aktif</span>';
    }

    $.ct.appTable(TABLE, {
        source: "{{ route('admin.harvest_order.fetch') }}",
        columns: [
            { type: 'actions', className: 'no-vis', actions: [
                { label: 'Kirim QR', class: 'btnSendQr', data: row => row },
                { label: 'Detail', class: 'btnDetail', data: row => row },
                { label: 'Download Foto', class: 'btnDownloadPhotosRow', data: row => row }
            ]},
            { data: 'no_tiket', formatter: v => v || '-' },
            { data: 'no_spp', formatter: v => v || '-' },
            { combine: ['kodekebun', 'nopetak'], formatter: (v) => (v.kodekebun || '-') + ' / ' + (v.nopetak || '-') },
            { data: 'sopir_name', formatter: v => v || '-' },
            { data: 'tanggal_panen', formatter: v => v || '-' },
            { combine: ['jumlah_rit', 'jumlah_tonase'], formatter: (v) => (v.jumlah_rit || '-') + ' / ' + (v.jumlah_tonase || '-') },
            { combine: ['progress_label', 'progress'], formatter: (v) => progressBadge(v.progress_label, v.progress) },
            { combine: ['is_cancelled', 'is_completed', 'status'], formatter: (v, row) => statusBadge(row) },
            { data: 'created_at', formatter: v => v || '-' }
        ],
        responsive: true,
        order: [[9, 'desc']]
    });

    function showModal(id) { var el = document.getElementById(id); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
    function hideModal(id) { var el = document.getElementById(id); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }

    $(document).on('click', '.btnClose', function () {
        hideModal('detailModal');
    });

    $(TABLE).on('click', '.btnSendQr', function () {
        var d = $(this).data();
        if (!d.has_wa_target) {
            Swal.fire({ icon: 'warning', title: 'Tidak Dapat Mengirim', text: 'Nomor WhatsApp sopir untuk tiket ini tidak ditemukan.' });
            return;
        }
        Swal.fire({
            icon: 'question',
            title: 'Kirim QR tiket ' + (d.no_tiket || '') + '?',
            text: 'QR akan dikirim ke WhatsApp sopir ' + (d.sopir_name || '-') + '.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(BASE_URL + '/' + d.id + '/sendQr', {}, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Terkirim', text: res.message || 'QR berhasil dikirim.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal mengirim QR.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal mengirim QR.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });

    var detailTicketId = null; // id tiket yang lagi dibuka di modal Detail, dipakai tombol Download Foto

    $(TABLE).on('click', '.btnDetail', function () {
        var d = $(this).data();
        detailTicketId = d.id;

        $('#d_no_tiket').text(d.no_tiket || '-');
        $('#d_no_spp').text(d.no_spp || '-');
        $('#d_kebun_petak').text((d.kodekebun || '-') + ' / ' + (d.nopetak || '-'));
        $('#d_sopir_name').text(d.sopir_name || '-');
        $('#d_tanggal_panen').text(d.tanggal_panen || '-');
        $('#d_rit_tonase').text((d.jumlah_rit || '-') + ' / ' + (d.jumlah_tonase || '-'));
        $('#d_progress_label').html(progressBadge(d.progress_label, d.progress));
        $('#d_status').html(statusBadge(d));
        $('#d_photos_box').hide();
        $('#d_photos_gallery').empty();

        showModal('detailModal');

        $.get(BASE_URL + '/' + d.id, function (res) {
            if (res.status !== 'success') return;
            var data = res.data;

            $('#d_petani_name').text(data.petani_name || '-');
            $('#d_kepalakerja_name').text(data.kepalakerja_name || '-');
            $('#d_officer_name').text(data.officer_name || '-');
            $('#d_tanggal_tanam').text(data.tanggal_tanam || '-');
            $('#d_tch').text(data.tch || '-');
            $('#d_zona').text(data.zona || '-');
            $('#d_luas').text((data.luas_ha || '-') + ' / ' + (data.luas_net || '-'));
            $('#d_jenis').text((data.jenis_tebu || '-') + ' / ' + (data.jenis_tebang || '-'));
            $('#d_rendemen').text(data.rendemen || '-');
            $('#d_trash').text(data.trash || '-');
            $('#d_keterangan').text(data.keterangan || '-');
            $('#d_timestamps').text((data.created_at || '-') + ' / ' + (data.updated_at || '-'));

            var steps = data.progress_steps || [];
            var stepsHtml = steps.map(function (s) {
                var cls = s.state === 'done' ? 'bg-success' : (s.state === 'current' ? 'bg-primary' : 'bg-secondary');
                return '<span class="badge ' + cls + '">' + s.step + '. ' + s.label + '</span>';
            }).join(' ');
            $('#d_progress_steps').html(stepsHtml);

            var photos = data.photos || [];
            if (photos.length) {
                var gallery = photos.map(function (p) {
                    return '<a href="' + p.url + '" target="_blank" title="' + (p.captured_at || '') + '">' +
                        '<img src="' + p.url + '" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">' +
                        '</a>';
                }).join('');
                $('#d_photos_gallery').html(gallery);
                $('#d_photos_box').show();
            }
        }, 'json');
    });

    function downloadTicketPhotos(id, noTiket) {
        window.open(BASE_URL + '/' + id + '/downloadPhotos', '_blank');
    }

    $(TABLE).on('click', '.btnDownloadPhotosRow', function () {
        var d = $(this).data();
        if (!d.has_photos) {
            Swal.fire({ icon: 'warning', title: 'Belum Ada Foto', text: 'Tiket ini belum punya foto tracking.' });
            return;
        }
        downloadTicketPhotos(d.id, d.no_tiket);
    });

    $('#btnDownloadPhotos').on('click', function () {
        if (!detailTicketId) return;
        downloadTicketPhotos(detailTicketId);
    });

    $('#btnSyncLan').on('click', function () {
        Swal.fire({
            icon: 'question',
            title: 'Sinkronisasi data ke LAN?',
            text: 'Proses sinkronisasi seluruh data harvest order ke jaringan LAN.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sync Sekarang',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(SYNC_LAN_URL, {}, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Selesai', text: res.message || 'Sinkronisasi berhasil.' });
                } else {
                    Swal.fire({ icon: 'info', title: 'Info', text: res.errMsg || 'Sinkronisasi belum tersedia.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Sinkronisasi belum tersedia.';
                Swal.fire({ icon: 'info', title: 'Info', text: msg });
            });
        });
    });
});
</script>
@endsection
