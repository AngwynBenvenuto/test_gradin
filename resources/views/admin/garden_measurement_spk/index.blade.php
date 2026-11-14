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
                        <button id="btnBulkMarkSynced" class="btn btn-outline-primary" disabled>
                            <i class="fas fa-check-double"></i> Tandai Tersinkron (Terpilih)
                        </button>
                    </div>
                </div>
                <div class="widget-content clearfix">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tbl" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="1%"><input type="checkbox" id="chkAll"></th>
                                    <th width="5%">Action</th>
                                    <th>No. SPK</th>
                                    <th>Petani</th>
                                    <th>Nama Kebun</th>
                                    <th>Desa / Kecamatan</th>
                                    <th>Estimasi Luas (Ha)</th>
                                    <th>Surveyor</th>
                                    <th>Tgl Rencana Survei</th>
                                    <th>Status</th>
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

{{-- Modal Assign Surveyor --}}
<div class="modal fade" id="mModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="mForm">
        @csrf
        <input type="hidden" id="f_id" name="id">
        <div class="modal-header">
          <h5 class="modal-title">Tugaskan Surveyor</h5>
          <button type="button" class="btn-close btnClose" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table table-sm table-borderless mb-3">
            <tr><td width="40%">No. SPK</td><td id="d_spk_number">-</td></tr>
            <tr><td>Petani</td><td id="d_farmer_name">-</td></tr>
            <tr><td>Kebun</td><td id="d_farm_name">-</td></tr>
            <tr><td>Desa / Kecamatan</td><td id="d_village">-</td></tr>
            <tr><td>Estimasi Luas (Ha)</td><td id="d_estimated_area_ha">-</td></tr>
          </table>
          <hr>
          <div class="form-group mb-2">
            <label class="control-label">Surveyor <span style="color:red">*</span></label>
            <select class="form-control" name="surveyor_id" id="f_surveyor_id" required>
                <option value="">-- Pilih Surveyor --</option>
            </select>
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Tanggal Rencana Survei</label>
            <input class="form-control" type="date" name="scheduled_date">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Catatan</label>
            <textarea class="form-control" name="assignment_notes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger" id="btnUnassignModal"><i class="fas fa-times"></i> Batalkan Penugasan</button>
          <button type="button" class="btn btn-secondary btnClose" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Tugaskan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Input / Tinjau Hasil Ukur --}}
<div class="modal fade" id="measurementModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="measurementForm">
        @csrf
        <input type="hidden" id="mf_id" name="id">
        <div class="modal-header">
          <h5 class="modal-title" id="measurementModalTitle">Detail</h5>
          <button type="button" class="btn-close btnClose" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table table-sm table-borderless mb-3">
            <tr><td width="40%">No. SPK</td><td id="mf_spk_number">-</td></tr>
            <tr><td>Petani</td><td id="mf_farmer_name">-</td></tr>
            <tr>
              <td>Surveyor</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-outline-primary btn-sm d-none" id="btnOpenAssign"><i class="fas fa-user-plus"></i> Penugasan</button>
                  <button type="button" class="btn btn-outline-warning btn-sm d-none" id="btnToggleSurvey"><i class="fas fa-play"></i> Mulai Survei</button>
                  <span class="fw-bold d-none" id="mf_survey_timer">00:00</span>
                </div>
              </td>
            </tr>
          </table>
          <hr>
          <h6>Data Kebun</h6>
          <div class="row g-2 mb-2 align-items-center">
            <label class="col-3 col-form-label">Nama Kebun</label>
            <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="text" name="farm_name"></div>
            <label class="col-3 col-form-label">Alamat</label>
            <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="text" name="farm_address"></div>
          </div>
          <div class="row g-2 mb-2 align-items-center">
            <label class="col-3 col-form-label">Desa</label>
            <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="text" name="village"></div>
            <label class="col-3 col-form-label">Kecamatan</label>
            <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="text" name="district"></div>
          </div>
          <div class="row g-2 mb-2 align-items-center">
            <label class="col-3 col-form-label">Komoditas</label>
            <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="text" name="commodity"></div>
          </div>
          <hr class="my-2">
          <div class="row g-2 mb-2 align-items-center">
            <label class="col-3 col-form-label">Kepemilikan Lahan</label>
            <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="text" name="land_ownership"></div>
            <label class="col-3 col-form-label">Estimasi Luas (Ha)</label>
            <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="number" step="0.0001" name="estimated_area_ha"></div>
          </div>
          <div id="mf_code_segments_wrapper">
            <div class="row g-2 mb-2 align-items-center">
              <label class="col-3 col-form-label">Tipe Lahan</label>
              <div class="col-9 col-md-3">
                <select class="form-control form-control-sm" name="land_type" required>
                  <option value="HG">HG (HGU)</option>
                  <option value="PL">PL (Plasma)</option>
                </select>
              </div>
              <label class="col-3 col-form-label">No Petak</label>
              <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="number" min="0" max="99" name="petak_code" placeholder="10" required></div>
            </div>
            <div class="row g-2 mb-2 align-items-center">
              <label class="col-3 col-form-label">Arah 1</label>
              <div class="col-9 col-md-3">
                <select class="form-control form-control-sm" name="dir1" required>
                  <option value="T">T (Timur)</option>
                  <option value="B">B (Barat)</option>
                  <option value="U">U (Utara)</option>
                  <option value="S">S (Selatan)</option>
                </select>
              </div>
              <label class="col-3 col-form-label">Jarak 1 (km)</label>
              <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="number" min="0" max="99" name="dist1" placeholder="23" required></div>
            </div>
            <div class="row g-2 mb-2 align-items-center">
              <label class="col-3 col-form-label">Arah 2</label>
              <div class="col-9 col-md-3">
                <select class="form-control form-control-sm" name="dir2" required>
                  <option value="S">S (Selatan)</option>
                  <option value="U">U (Utara)</option>
                  <option value="T">T (Timur)</option>
                  <option value="B">B (Barat)</option>
                </select>
              </div>
              <label class="col-3 col-form-label">Jarak 2 (km)</label>
              <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="number" min="0" max="99" name="dist2" placeholder="24" required></div>
            </div>
            <div class="row g-2 mb-2 align-items-center">
              <label class="col-3 col-form-label">Periode Tanam</label>
              <div class="col-9 col-md-3">
                <select class="form-control form-control-sm" name="periode_code" required>
                  <option value="">-- Pilih --</option>
                  @foreach($plantingPeriods as $period)
                    <option value="{{ $period->code }}">{{ $period->code }} - {{ $period->name }}</option>
                  @endforeach
                </select>
              </div>
              <label class="col-3 col-form-label">Preview Kode Kebun</label>
              <div class="col-9 col-md-3"><input class="form-control form-control-sm" type="text" id="mf_garden_code_preview" readonly placeholder="-"></div>
            </div>
          </div>
          <hr>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Titik Koordinat Hasil Ukur</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPoint"><i class="fas fa-plus"></i> Tambah Titik</button>
          </div>
          <div class="table-responsive mb-2">
            <table class="table table-sm table-bordered" id="pointsTable">
              <thead>
                <tr><th width="5%">#</th><th>Latitude</th><th>Longitude</th><th width="5%"></th></tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="measurementMap" style="height:300px;border:1px solid #ddd;"></div>
          <div class="mt-2 text-muted">Luas dari koordinat (referensi): <span id="mf_area_preview">-</span> Ha</div>
          <div class="row mt-2">
            <div class="col-md-6 form-group mb-2">
              <label class="control-label">Luas Hasil Ukur (Ha)</label>
              <input class="form-control" type="number" step="0.0001" name="measured_area_ha">
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex align-items-center gap-2">
          {{-- <button type="button" class="btn btn-secondary btnClose" data-bs-dismiss="modal">Batal</button> --}}
          <button type="button" class="btn btn-outline-primary" id="btnSaveDraft"><i class="fas fa-save"></i> Simpan Draft</button>
          <button type="button" class="btn btn-success" id="btnSaveApprove"><i class="fas fa-check-double"></i> Setujui & Jadikan Master Kebun</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('extend_script')
    <link href="{{ asset('media/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('media/plugins/sweetalert/sweetalert.min.js') }}"></script>
    <link href="{{ asset('media/plugins/leafletjs/leaflet.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('media/plugins/leafletjs/leaflet.js') }}"></script>
    <style>
        /* #mModal dibuka di atas #measurementModal (stacked modal) tanpa menutupnya dulu.
           Tema admin set .modal ke z-index:1090 (lihat komentar SweetAlert2 di layouts/admin.blade.php),
           jadi #mModal harus di atas itu (dan backdrop-nya di antara keduanya, lihat JS shown.bs.modal). */
        #mModal { z-index: 1095; }
    </style>
@endsection

@section('footer_js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': AppHelper.csrfToken } });

    var TABLE = '#tbl';
    var BASE_URL = "{{ url('/spk-pengukuran/penugasan') }}";
    var BULK_MARK_SYNCED_URL = "{{ route('admin.spk_pengukuran.mark_synced_bulk') }}";
    var surveyors = [];
    var selectedIds = new Set();
    var currentSpkData = null;
    var surveyTimerInterval = null;
    var surveyTimerStartedAt = null;

    function statusBadge(status, label) {
        var map = {
            queued: 'bg-secondary',
            synced_to_lan: 'bg-info',
            scheduled: 'bg-primary',
            surveying: 'bg-warning',
            measurement_submitted: 'bg-warning',
            completed: 'bg-success',
            rejected: 'bg-danger',
            cancelled: 'bg-secondary'
        };
        return '<span class="badge ' + (map[status] || 'bg-secondary') + '">' + (label || status) + '</span>';
    }

    $.ct.appTable(TABLE, {
        source: "{{ route('admin.spk_pengukuran.assign_fetch') }}",
        columns: [
            { data: 'id', className: 'no-vis', formatter: (id, row) => {
                if (!row.can_mark_synced) return '';
                var checked = selectedIds.has(String(id)) ? ' checked' : '';
                return '<input type="checkbox" class="rowCheck" value="' + id + '"' + checked + '>';
            }},
            { type: 'actions', className: 'no-vis', actions: [
                { label: 'Detail', class: 'btnDetail', data: row => row }
            ]},
            { data: 'spk_number' },
            { data: 'farmer_name' },
            { data: 'farm_name' },
            { combine: ['village', 'district'], formatter: (v) => (v.village || '') + ' / ' + (v.district || '') },
            { data: 'estimated_area_ha' },
            { data: 'surveyor_name', formatter: v => v || '<span class="text-muted">Belum ditugaskan</span>' },
            { data: 'scheduled_date', formatter: v => v || '-' },
            { combine: ['status', 'status_label'], formatter: (v) => statusBadge(v.status, v.status_label) }
        ],
        responsive: true,
        order: [[2, 'desc']]
    });

    function updateBulkButton() {
        var count = selectedIds.size;
        $('#btnBulkMarkSynced')
            .prop('disabled', count === 0)
            .html('<i class="fas fa-check-double"></i> Tandai Tersinkron' + (count > 0 ? ' (' + count + ' dipilih)' : ' (Terpilih)'));
    }

    function syncChkAllState() {
        var eligible = 0;
        var checked = 0;
        $(TABLE).DataTable().rows({ page: 'current' }).every(function () {
            var d = this.data();
            if (!d.can_mark_synced) return;
            eligible++;
            if (selectedIds.has(String(d.id))) checked++;
        });
        var $chkAll = $('#chkAll');
        $chkAll.prop('checked', eligible > 0 && checked === eligible);
        $chkAll.prop('indeterminate', checked > 0 && checked < eligible);
    }

    $(TABLE).on('draw.dt', function () {
        syncChkAllState();
    });

    $('#chkAll').on('change', function () {
        var checked = this.checked;
        $(TABLE).DataTable().rows({ page: 'current' }).every(function () {
            var d = this.data();
            if (!d.can_mark_synced) return;
            if (checked) selectedIds.add(String(d.id)); else selectedIds.delete(String(d.id));
        });
        $(TABLE).DataTable().rows({ page: 'current' }).invalidate('data').draw(false);
        updateBulkButton();
    });

    $(TABLE).on('change', '.rowCheck', function () {
        var id = String($(this).val());
        if (this.checked) selectedIds.add(id); else selectedIds.delete(id);
        syncChkAllState();
        updateBulkButton();
    });

    $('#btnBulkMarkSynced').on('click', function () {
        if (selectedIds.size === 0) return;
        var ids = Array.from(selectedIds);
        Swal.fire({
            icon: 'question',
            title: 'Tandai ' + ids.length + ' SPK terpilih tersinkron ke LAN?',
            text: 'Gunakan ini hanya jika sinkronisasi ke LAN dilakukan secara manual/di luar sistem.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tandai Semua',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(BULK_MARK_SYNCED_URL, { ids: ids }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Selesai', text: res.message || 'SPK terpilih berhasil ditandai tersinkron.' });
                    selectedIds.clear();
                    updateBulkButton();
                    reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menandai SPK terpilih.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menandai SPK terpilih.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });

    function reload() { $(TABLE).DataTable().ajax.reload(null, false); }
    function showModal() { var el = document.getElementById('mModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
    function hideModal() { var el = document.getElementById('mModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }
    function clearForm() { document.getElementById('mForm').reset(); $('#f_id').val(''); }

    $(document).on('click', '.btnClose', function () {
        var modalId = $(this).closest('.modal').attr('id');
        if (modalId === 'mModal') { hideModal(); } else { hideMeasurementModal(); }
    });

    // Modal Penugasan (#mModal) ditumpuk di atas modal Detail (#measurementModal),
    // bukan menggantikannya, supaya admin tidak kehilangan konteks/data yang sedang diisi.
    $('#mModal').on('shown.bs.modal', function () {
        if (!$('#measurementModal').hasClass('show')) return;
        var backdrops = document.querySelectorAll('.modal-backdrop');
        var topBackdrop = backdrops[backdrops.length - 1];
        if (topBackdrop) topBackdrop.style.zIndex = 1092;
    });

    $('#mModal').on('hidden.bs.modal', function () {
        // Bootstrap selalu melepas kunci scroll body saat modal manapun ditutup,
        // padahal modal Detail di baliknya masih terbuka -> kunci lagi kalau begitu.
        if ($('#measurementModal').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    function loadSurveyors(onDone) {
        if (surveyors.length) { if (onDone) onDone(); return; }
        $.get("{{ route('admin.spk_pengukuran.assign_surveyors') }}", function (res) {
            if (res.status !== 'success') return;
            surveyors = res.data;
            var $select = $('#f_surveyor_id');
            surveyors.forEach(function (s) {
                $select.append('<option value="' + s.id + '">' + s.name + (s.role_name ? ' (' + s.role_name + ')' : '') + '</option>');
            });
            if (onDone) onDone();
        }, 'json');
    }

    function openAssignModal(d) {
        clearForm();
        $('#f_id').val(d.id);
        $('#d_spk_number').text(d.spk_number || '-');
        $('#d_farmer_name').text(d.farmer_name || '-');
        $('#d_farm_name').text(d.farm_name || '-');
        $('#d_village').text((d.village || '-') + ' / ' + (d.district || '-'));
        $('#d_estimated_area_ha').text(d.estimated_area_ha || '-');

        loadSurveyors(function () {
            $('#f_surveyor_id').val(d.surveyor_id || '');
        });
        $('[name="scheduled_date"]').val(d.scheduled_date || '');
        $('[name="assignment_notes"]').val(d.assignment_notes || '');

        $('#btnUnassignModal').toggle(d.status === 'scheduled').data('id', d.id);

        showModal();
    }

    $('#btnOpenAssign').on('click', function () {
        var d = currentSpkData;
        if (!d) return;
        if (!d.can_assign) {
            Swal.fire({
                icon: 'info',
                title: 'Tidak dapat ditugaskan',
                text: 'SPK dengan status "' + (d.status_label || d.status) + '" tidak dapat ditugaskan ke surveyor.'
            });
            return;
        }
        openAssignModal(d);
    });

    $('#mForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#f_id').val();
        if (!id) return;
        $.post(BASE_URL + '/' + id + '/assign', $(this).serialize(), function (res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message || 'Surveyor berhasil ditugaskan.' });
                hideModal();
                if (currentSpkData && String(currentSpkData.id) === String(id)) {
                    currentSpkData.surveyor_id = $('#f_surveyor_id').val();
                    $('#btnOpenAssign').toggleClass('d-none', !!currentSpkData.surveyor_id);

                    var surveyDoneNow = ['completed', 'cancelled', 'rejected'].indexOf(currentSpkData.status) !== -1;
                    $('#btnToggleSurvey').toggleClass('d-none', surveyDoneNow || !currentSpkData.surveyor_id);
                }
                reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menugaskan surveyor.' });
            }
        }, 'json').fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menugaskan surveyor.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        });
    });

    $('#btnUnassignModal').on('click', function () {
        var id = $(this).data('id');
        if (!id) return;
        Swal.fire({
            icon: 'question',
            title: 'Batalkan penugasan surveyor?',
            text: 'SPK akan kembali ke status "Terkirim ke LAN".',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tutup'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(BASE_URL + '/' + id + '/unassign', {}, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message || 'Penugasan dibatalkan.' });
                    hideModal();
                    if (currentSpkData && String(currentSpkData.id) === String(id)) {
                        currentSpkData.surveyor_id = null;
                        $('#btnOpenAssign').removeClass('d-none');
                        $('#btnToggleSurvey').addClass('d-none');
                        stopSurveyTimerDisplay();
                        setSurveyToggleState(false);
                    }
                    reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal membatalkan penugasan.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal membatalkan penugasan.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });

    // ---------------------------------------------------------------
    // Mulai Survei (toggle: mulai -> timer jalan -> selesaikan)
    // ---------------------------------------------------------------
    function formatSurveyTimer(totalSeconds) {
        var m = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
        var s = String(totalSeconds % 60).padStart(2, '0');
        return m + ':' + s;
    }

    function startSurveyTimerDisplay() {
        surveyTimerStartedAt = Date.now();
        $('#mf_survey_timer').removeClass('d-none').text('00:00');
        clearInterval(surveyTimerInterval);
        surveyTimerInterval = setInterval(function () {
            var elapsed = Math.floor((Date.now() - surveyTimerStartedAt) / 1000);
            $('#mf_survey_timer').text(formatSurveyTimer(elapsed));
        }, 1000);
    }

    function stopSurveyTimerDisplay() {
        clearInterval(surveyTimerInterval);
        surveyTimerInterval = null;
        surveyTimerStartedAt = null;
        $('#mf_survey_timer').addClass('d-none').text('00:00');
    }

    function setSurveyToggleState(running) {
        var $btn = $('#btnToggleSurvey').data('running', running);
        if (running) {
            $btn.removeClass('btn-outline-warning').addClass('btn-outline-danger')
                .html('<i class="fas fa-stop"></i> Selesaikan Survei');
        } else {
            $btn.removeClass('btn-outline-danger').addClass('btn-outline-warning')
                .html('<i class="fas fa-play"></i> Mulai Survei');
        }
    }

    $('#btnToggleSurvey').on('click', function () {
        var d = currentSpkData;
        if (!d) return;
        var id = $('#mf_id').val();
        if (!id) return;

        if ($(this).data('running')) {
            $.post(BASE_URL + '/' + id + '/finishSurvey', {}, function (res) {
                if (res.success) {
                    currentSpkData.status = 'measurement_submitted';
                    stopSurveyTimerDisplay();
                    setSurveyToggleState(false);
                    $('#measurementModalTitle').text('Tinjau & Setujui Hasil Ukur');
                    reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menyelesaikan survei.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menyelesaikan survei.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
            return;
        }

        $.post(BASE_URL + '/' + id + '/startSurvey', {}, function (res) {
            if (res.success) {
                currentSpkData.status = 'surveying';
                setSurveyToggleState(true);
                startSurveyTimerDisplay();
                reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal memulai survei.' });
            }
        }, 'json').fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal memulai survei.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        });
    });

    // ---------------------------------------------------------------
    // Modal Input / Tinjau Hasil Ukur
    // ---------------------------------------------------------------
    var measurementMap = null;
    var measurementPolygon = null;
    var measurementMarkers = [];
    var MEASUREMENT_DEFAULT_CENTER = [-6.8726682, 109.3512368];

    function ensureMeasurementMap() {
        if (measurementMap) return;
        measurementMap = L.map('measurementMap').setView(MEASUREMENT_DEFAULT_CENTER, 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 19
        }).addTo(measurementMap);
    }

    function pointRowHtml(lat, lng) {
        return '<tr>' +
            '<td class="rowNo"></td>' +
            '<td><input type="number" step="any" class="form-control form-control-sm ptLat" value="' + (lat != null ? lat : '') + '"></td>' +
            '<td><input type="number" step="any" class="form-control form-control-sm ptLng" value="' + (lng != null ? lng : '') + '"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnRemovePoint"><i class="fas fa-times"></i></button></td>' +
            '</tr>';
    }

    function renumberPointRows() {
        $('#pointsTable tbody tr').each(function (i) {
            $(this).find('.rowNo').text(i + 1);
        });
    }

    function addPointRow(lat, lng) {
        $('#pointsTable tbody').append(pointRowHtml(lat, lng));
        renumberPointRows();
    }

    function clearPointRows() {
        $('#pointsTable tbody').empty();
    }

    function getEnteredPoints() {
        var points = [];
        $('#pointsTable tbody tr').each(function () {
            var lat = parseFloat($(this).find('.ptLat').val());
            var lng = parseFloat($(this).find('.ptLng').val());
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                points.push({ latitude: lat, longitude: lng });
            }
        });
        return points;
    }

    // Shoelace formula dengan proyeksi equirectangular sederhana,
    // sama dengan perhitungan di server (lihat calculatePolygonAreaHa
    // di Admin\GardenMeasurementSpkController) supaya preview konsisten.
    function calcAreaHaPreview(points) {
        if (points.length < 3) return null;
        var latSum = 0;
        points.forEach(function (p) { latSum += p.latitude; });
        var avgLatRad = (latSum / points.length) * Math.PI / 180;
        var mPerDegLat = 111132.92 - 559.82 * Math.cos(2 * avgLatRad) + 1.175 * Math.cos(4 * avgLatRad);
        var mPerDegLng = 111412.84 * Math.cos(avgLatRad) - 93.5 * Math.cos(3 * avgLatRad);
        var xy = points.map(function (p) {
            return { x: p.longitude * mPerDegLng, y: p.latitude * mPerDegLat };
        });
        var area = 0;
        for (var i = 0; i < xy.length; i++) {
            var j = (i + 1) % xy.length;
            area += xy[i].x * xy[j].y;
            area -= xy[j].x * xy[i].y;
        }
        return Math.round((Math.abs(area) / 2 / 10000) * 10000) / 10000;
    }

    function recalcMeasurementPreview() {
        ensureMeasurementMap();
        measurementMap.invalidateSize();

        var points = getEnteredPoints();

        if (measurementPolygon) { measurementMap.removeLayer(measurementPolygon); measurementPolygon = null; }
        measurementMarkers.forEach(function (m) { measurementMap.removeLayer(m); });
        measurementMarkers = [];

        points.forEach(function (p) {
            measurementMarkers.push(L.circleMarker([p.latitude, p.longitude], { radius: 4, color: '#1a73e8' }).addTo(measurementMap));
        });

        if (points.length >= 3) {
            var latlngs = points.map(function (p) { return [p.latitude, p.longitude]; });
            measurementPolygon = L.polygon(latlngs, { color: '#1a73e8', weight: 2, fillOpacity: 0.15 }).addTo(measurementMap);
            measurementMap.fitBounds(measurementPolygon.getBounds(), { padding: [20, 20] });
            $('#mf_area_preview').text(calcAreaHaPreview(points));
        } else if (points.length > 0) {
            var group = L.featureGroup(measurementMarkers);
            measurementMap.fitBounds(group.getBounds().pad(0.5));
            $('#mf_area_preview').text('- (minimal 3 titik)');
        } else {
            $('#mf_area_preview').text('-');
        }
    }

    $(document).on('input change', '#pointsTable .ptLat, #pointsTable .ptLng', recalcMeasurementPreview);

    // Preview kode kebun format PLOT ID HGU: {TIPE}.{ARAH1}{JARAK1}{ARAH2}{JARAK2}{PETAK}{PERIODE}.
    function updateGardenCodePreview() {
        var landType = $('[name="land_type"]').val();
        var dir1 = $('[name="dir1"]').val();
        var dist1 = $('[name="dist1"]').val();
        var dir2 = $('[name="dir2"]').val();
        var dist2 = $('[name="dist2"]').val();
        var petak = $('[name="petak_code"]').val();
        var periode = $('[name="periode_code"]').val();

        var complete = landType && dir1 && dist1 !== '' && dir2 && dist2 !== '' && petak && periode;

        if (!complete) {
            $('#mf_garden_code_preview').val('');
            return;
        }

        var pad2 = function (v) { return String(v).padStart(2, '0'); };
        var code = landType + '.' + dir1 + pad2(dist1) + dir2 + pad2(dist2) + pad2(petak) + pad2(periode);
        $('#mf_garden_code_preview').val(code);
    }

    $(document).on('input change', '#mf_code_segments_wrapper [name]', updateGardenCodePreview);

    $('#btnAddPoint').on('click', function () {
        addPointRow(null, null);
    });

    $(document).on('click', '.btnRemovePoint', function () {
        $(this).closest('tr').remove();
        renumberPointRows();
        recalcMeasurementPreview();
    });

    function openMeasurementModal(d) {
        currentSpkData = d;

        $('#measurementForm')[0].reset();
        $('#mf_id').val(d.id);
        $('#mf_spk_number').text(d.spk_number || '-');
        $('#mf_farmer_name').text(d.farmer_name || '-');
        $('#measurementModalTitle').text(d.can_review_measurement ? 'Tinjau & Setujui Hasil Ukur' : 'Input Hasil Ukur');
        $('[name="land_type"]').val('PL');
        updateGardenCodePreview();
        clearPointRows();
        $('#mf_area_preview').text('-');

        stopSurveyTimerDisplay();

        /*
         * Penugasan cuma hilang kalau surveyor-nya sudah diisi, bukan
         * berdasarkan tahap SPK. Tombol survei tetap ada (boleh
         * disurvei berkali-kali) selama kebun ini belum di tahap akhir
         * (selesai/dibatalkan/ditolak).
         */
        $('#btnOpenAssign').toggleClass('d-none', !!d.surveyor_id);

        var surveyDone = ['completed', 'cancelled', 'rejected'].indexOf(d.status) !== -1;
        if (surveyDone || !d.surveyor_id) {
            $('#btnToggleSurvey').addClass('d-none');
        } else {
            $('#btnToggleSurvey').removeClass('d-none');
            if (d.status === 'surveying') {
                setSurveyToggleState(true);
                startSurveyTimerDisplay();
            } else {
                setSurveyToggleState(false);
            }
        }

        $.get(BASE_URL + '/' + d.id + '/measurement', function (res) {
            if (res.status !== 'success') return;
            var data = res.data;
            $('[name="farm_name"]').val(data.garden.farm_name || '');
            $('[name="farm_address"]').val(data.garden.farm_address || '');
            $('[name="village"]').val(data.garden.village || '');
            $('[name="district"]').val(data.garden.district || '');
            $('[name="commodity"]').val(data.garden.commodity || '');
            $('[name="land_ownership"]').val(data.garden.land_ownership || '');
            $('[name="estimated_area_ha"]').val(data.garden.estimated_area_ha || '');
            $('[name="measured_area_ha"]').val((data.spk && data.spk.measured_area_ha) || '');

            clearPointRows();
            if (data.points && data.points.length) {
                data.points.forEach(function (p) { addPointRow(p.latitude, p.longitude); });
            } else {
                addPointRow(null, null);
                addPointRow(null, null);
                addPointRow(null, null);
            }

            showMeasurementModal();
            setTimeout(recalcMeasurementPreview, 200);
        }, 'json').fail(function () {
            addPointRow(null, null);
            addPointRow(null, null);
            addPointRow(null, null);
            showMeasurementModal();
            setTimeout(recalcMeasurementPreview, 200);
        });
    }

    function showMeasurementModal() {
        var el = document.getElementById('measurementModal');
        (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show');
    }

    function hideMeasurementModal() {
        var el = document.getElementById('measurementModal');
        (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide');
    }

    $(TABLE).on('click', '.btnDetail', function () {
        openMeasurementModal($(this).data());
    });

    function submitMeasurement(approve) {
        var id = $('#mf_id').val();
        if (!id) return;

        var points = getEnteredPoints();

        if (approve) {
            if (points.length > 0 && points.length < 3) {
                Swal.fire({ icon: 'warning', title: 'Titik koordinat kurang', text: 'Minimal 3 titik koordinat diperlukan untuk membentuk area kebun, atau kosongkan semua jika belum ada koordinat.' });
                return;
            }

            var codeFields = ['land_type', 'dir1', 'dist1', 'dir2', 'dist2', 'petak_code', 'periode_code'];
            var codeIncomplete = codeFields.some(function (f) { return !$('[name="' + f + '"]').val(); });
            if (codeIncomplete) {
                Swal.fire({ icon: 'warning', title: 'Kode kebun belum lengkap', text: 'Tipe Lahan, Arah/Jarak 1 & 2, No Petak, dan Periode Tanam wajib diisi supaya kode kebun master bisa dibuat.' });
                return;
            }

            var measuredAreaHa = $('[name="measured_area_ha"]').val();
            if (!measuredAreaHa || parseFloat(measuredAreaHa) <= 0) {
                Swal.fire({ icon: 'warning', title: 'Luas hasil ukur belum diisi', text: 'Luas Hasil Ukur (Ha) wajib diisi sebelum hasil ukur bisa disetujui dan dijadikan master kebun.' });
                return;
            }
        }

        var payload = {
            points: points,
            farm_name: $('[name="farm_name"]').val(),
            farm_address: $('[name="farm_address"]').val(),
            village: $('[name="village"]').val(),
            district: $('[name="district"]').val(),
            commodity: $('[name="commodity"]').val(),
            land_ownership: $('[name="land_ownership"]').val(),
            estimated_area_ha: $('[name="estimated_area_ha"]').val(),
            measured_area_ha: $('[name="measured_area_ha"]').val(),
            land_type: $('[name="land_type"]').val(),
            dir1: $('[name="dir1"]').val(),
            dist1: $('[name="dist1"]').val(),
            dir2: $('[name="dir2"]').val(),
            dist2: $('[name="dist2"]').val(),
            petak_code: $('[name="petak_code"]').val(),
            periode_code: $('[name="periode_code"]').val(),
            approve: approve ? 1 : 0,
            _token: AppHelper.csrfToken
        };

        $.post(BASE_URL + '/' + id + '/measurement', payload, function (res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message || 'Hasil ukur berhasil disimpan.' });
                hideMeasurementModal();
                reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menyimpan hasil ukur.' });
            }
        }, 'json').fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menyimpan hasil ukur.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        });
    }

    $('#btnSaveDraft').on('click', function () { submitMeasurement(false); });

    $('#btnSaveApprove').on('click', function () {
        Swal.fire({
            icon: 'question',
            title: 'Setujui hasil ukur ini?',
            text: 'Kebun dan koordinat akan langsung menjadi master kebun.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            submitMeasurement(true);
        });
    });
});
</script>
@endsection
