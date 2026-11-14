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
                        <button id="btnAdd" class="btn btn-primary lnj-color">Add Kebun</button>
                        <button id="btnBulkMarkSynced" class="btn btn-outline-primary" disabled>
                            <i class="fas fa-sync"></i> Sync ke LAN (Terpilih)
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
                                    <th>Kode Kebun</th>
                                    <th>Nama Kebun</th>
                                    <th>Petani</th>
                                    <th>Desa / Kecamatan</th>
                                    <th>Komoditas</th>
                                    <th>Luas (Ha)</th>
                                    <th>Status</th>
                                    <th>Sumber</th>
                                    <th>Disetujui</th>
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

{{-- Modal Detail Kebun --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Kebun</h5>
        <button type="button" class="btn-close btnClose" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-borderless mb-3">
          <tr><td width="30%">Kode Kebun</td><td id="d_garden_code">-</td></tr>
          <tr><td>Nama Kebun</td><td id="d_garden_name">-</td></tr>
          <tr><td>Petani</td><td id="d_farmer_name">-</td></tr>
          <tr><td>Alamat</td><td id="d_address">-</td></tr>
          <tr><td>Desa / Kecamatan</td><td id="d_village">-</td></tr>
          <tr><td>Komoditas</td><td id="d_commodity">-</td></tr>
          <tr><td>Kepemilikan Lahan</td><td id="d_land_ownership">-</td></tr>
          <tr><td>Luas (Ha)</td><td id="d_area_ha">-</td></tr>
          <tr><td>Status</td><td id="d_status">-</td></tr>
          <tr><td>Kode Wilayah / Section / Jenis Lahan</td><td id="d_code_segments">-</td></tr>
          <tr><td>Sumber Data Survei</td><td id="d_source">-</td></tr>
          <tr><td>Sumber Data Kebun</td><td id="d_garden_source">-</td></tr>
          <tr><td>Tersinkron ke LAN</td><td id="d_synced_to_lan_at">-</td></tr>
          <tr><td>Tanggal Disetujui</td><td id="d_approved_at">-</td></tr>
        </table>
        <hr>
        <h6>Polygon Koordinat</h6>
        <div id="detailMap" style="height:320px;border:1px solid #ddd;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btnClose" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Add/Edit Kebun --}}
<div class="modal fade" id="mModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="mForm">
        @csrf
        <input type="hidden" id="f_id" name="id">
        <input type="hidden" id="f_points" name="points" value="[]">
        <div class="modal-header">
          <h5 class="modal-title" id="mTitle">Add Kebun</h5>
          <button type="button" class="btn-close btnCloseM" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Company <span style="color:red">*</span></label>
                <select class="form-control select2" name="company_code" style="width:100%">
                  @foreach($companies as $company)
                    <option value="{{ $company->company_code }}">{{ $company->company_name }} ({{ $company->company_code }})</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Petani</label>
                <select class="form-control select2" name="farmer_id" style="width:100%">
                  <option value="">- Tanpa petani (kebun HGU) -</option>
                  @foreach($farmers as $farmer)
                    <option value="{{ $farmer->id }}">{{ $farmer->farmer_code ?: '('.$farmer->id.')' }} - {{ $farmer->full_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-12"><hr class="mt-0 mb-2"><label class="control-label mb-1 fw-bold">Kode Kebun (format PLOT ID HGU)</label></div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Tipe Lahan <span style="color:red">*</span></label>
                <select class="form-control form-control-sm" name="land_type">
                  <option value="HG">HG (HGU)</option>
                  <option value="PL">PL (Plasma)</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">No Petak <span style="color:red">*</span></label>
                <input class="form-control form-control-sm" type="number" min="0" max="99" name="petak_code" placeholder="10">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-2">
                <label class="control-label">Arah 1 <span style="color:red">*</span></label>
                <select class="form-control form-control-sm" name="dir1">
                  <option value="T">T (Timur)</option>
                  <option value="B">B (Barat)</option>
                  <option value="U">U (Utara)</option>
                  <option value="S">S (Selatan)</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-2">
                <label class="control-label">Jarak 1 (km) <span style="color:red">*</span></label>
                <input class="form-control form-control-sm" type="number" min="0" max="99" name="dist1" placeholder="23">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-2">
                <label class="control-label">Arah 2 <span style="color:red">*</span></label>
                <select class="form-control form-control-sm" name="dir2">
                  <option value="S">S (Selatan)</option>
                  <option value="U">U (Utara)</option>
                  <option value="T">T (Timur)</option>
                  <option value="B">B (Barat)</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-2">
                <label class="control-label">Jarak 2 (km) <span style="color:red">*</span></label>
                <input class="form-control form-control-sm" type="number" min="0" max="99" name="dist2" placeholder="24">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Periode Tanam <span style="color:red">*</span></label>
                <select class="form-control form-control-sm" name="periode_code">
                  <option value="">-- Pilih --</option>
                  @foreach($plantingPeriods as $period)
                    <option value="{{ $period->code }}">{{ $period->code }} - {{ $period->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Preview Kode Kebun</label>
                <input class="form-control form-control-sm" type="text" id="f_garden_code_preview" readonly placeholder="-">
              </div>
            </div>
            <div class="col-md-12"><hr class="mt-0 mb-2"></div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Nama Kebun <span style="color:red">*</span></label>
                <input class="form-control" type="text" name="garden_name" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Desa <span style="color:red">*</span></label>
                <input class="form-control" type="text" name="village" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Kecamatan <span style="color:red">*</span></label>
                <input class="form-control" type="text" name="district" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Komoditas</label>
                <input class="form-control" type="text" name="commodity" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Kepemilikan Lahan <span style="color:red">*</span></label>
                <input class="form-control" type="text" name="land_ownership" autocomplete="off" placeholder="mis. SHM, HGU, SEWA">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Luas (Ha) <span style="color:red">*</span></label>
                <input class="form-control" type="number" step="0.0001" min="0" name="area_ha" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Status</label>
                <select class="form-control" name="status">
                  <option value="active">Aktif</option>
                  <option value="inactive">Tidak aktif</option>
                  <option value="blocked">Diblokir</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group mb-2">
                <label class="control-label">Alamat</label>
                <textarea class="form-control" name="address" rows="2"></textarea>
              </div>
            </div>
            <div class="col-md-12">
              <hr>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="control-label mb-0">Koordinat Polygon (minimal 3 titik, opsional)</label>
                <button type="button" id="btnAddPoint" class="btn btn-sm btn-outline-primary">+ Tambah Titik</button>
              </div>
              <div id="pointsWrap">
                <table class="table table-sm table-bordered mb-0" id="pointsTable">
                  <thead>
                    <tr>
                      <th width="5%">#</th>
                      <th>Latitude</th>
                      <th>Longitude</th>
                      <th width="5%"></th>
                    </tr>
                  </thead>
                  <tbody id="pointsBody"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btnCloseM" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save</button>
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
@endsection

@section('footer_js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': AppHelper.csrfToken } });

    var TABLE = '#tbl';
    var BASE_URL = "{{ url('/kebun') }}";
    var BULK_MARK_SYNCED_URL = "{{ route('admin.garden.mark_synced_bulk') }}";
    var SAVE_URL = "{{ route('admin.garden.save') }}";
    var DELETE_URL = "{{ route('admin.garden.delete') }}";
    var FIELDS = [
        'company_code', 'farmer_id', 'garden_name', 'address',
        'village', 'district', 'commodity', 'land_ownership', 'area_ha', 'status',
        'land_type', 'dir1', 'dist1', 'dir2', 'dist2', 'petak_code', 'periode_code'
    ];
    var selectedIds = new Set();

    function statusBadge(status) {
        var map = { active: 'bg-success', inactive: 'bg-secondary', blocked: 'bg-danger' };
        var label = { active: 'Aktif', inactive: 'Tidak Aktif', blocked: 'Diblokir' };
        return '<span class="badge ' + (map[status] || 'bg-secondary') + '">' + (label[status] || status) + '</span>';
    }

    function sourceBadge(source, label) {
        var map = { CLOUD: 'bg-secondary', LAN1: 'bg-info', LAN2: 'bg-primary' };
        return '<span class="badge ' + (map[source] || 'bg-secondary') + '">' + (label || source || '-') + '</span>';
    }

    $.ct.appTable(TABLE, {
        source: "{{ route('admin.garden.fetch') }}",
        columns: [
            { data: 'id', className: 'no-vis', formatter: (id, row) => {
                var checked = selectedIds.has(String(id)) ? ' checked' : '';
                return '<input type="checkbox" class="rowCheck" value="' + id + '"' + checked + '>';
            }},
            { type: 'actions', className: 'no-vis', actions: [
                { label: 'Detail', class: 'btnDetail', data: row => row },
                { label: 'Edit', class: 'btnEdit', data: row => ({ id: row.id }) },
                { label: 'Delete', class: 'btnDelete', data: row => ({ id: row.id }) }
            ]},
            { data: 'garden_code' },
            { data: 'garden_name' },
            { data: 'farmer_name' },
            { combine: ['village', 'district'], formatter: (v) => (v.village || '') + ' / ' + (v.district || '') },
            { data: 'commodity', formatter: v => v || '-' },
            { data: 'area_ha' },
            { data: 'status', formatter: v => statusBadge(v) },
            { combine: ['source', 'source_label'], formatter: (v) => sourceBadge(v.source, v.source_label) },
            { data: 'approved_at', formatter: v => v || '-' }
        ],
        responsive: true,
        order: [[2, 'asc']]
    });

    function updateBulkButton() {
        var count = selectedIds.size;
        $('#btnBulkMarkSynced')
            .prop('disabled', count === 0)
            .html('<i class="fas fa-sync"></i> Sync ke LAN' + (count > 0 ? ' (' + count + ' dipilih)' : ' (Terpilih)'));
    }

    function syncChkAllState() {
        var eligible = 0;
        var checked = 0;
        $(TABLE).DataTable().rows({ page: 'current' }).every(function () {
            var d = this.data();
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

    function reload() { $(TABLE).DataTable().ajax.reload(null, false); }

    function promptTargetLan() {
        return Swal.fire({
            icon: 'question',
            title: 'Sync ke LAN mana?',
            input: 'select',
            inputOptions: { LAN1: 'LAN 1', LAN2: 'LAN 2' },
            inputPlaceholder: 'Pilih LAN tujuan',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sync',
            cancelButtonText: 'Batal',
            inputValidator: (value) => !value ? 'Pilih LAN tujuan terlebih dahulu.' : undefined
        });
    }

    $('#btnBulkMarkSynced').on('click', function () {
        if (selectedIds.size === 0) return;
        var ids = Array.from(selectedIds);
        promptTargetLan().then(function (result) {
            if (!result.isConfirmed) return;
            $.post(BULK_MARK_SYNCED_URL, { ids: ids, target_lan: result.value }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Selesai', text: res.message || 'Kebun terpilih berhasil ditandai tersinkron.' });
                    selectedIds.clear();
                    updateBulkButton();
                    reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menandai kebun terpilih.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menandai kebun terpilih.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });

    var detailMap = null;
    var detailPolygon = null;

    function ensureDetailMap() {
        if (detailMap) return;
        detailMap = L.map('detailMap').setView([-6.8726682, 109.3512368], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 19
        }).addTo(detailMap);
    }

    function showModal() { var el = document.getElementById('detailModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }

    $(document).on('click', '.btnClose', function () {
        var el = document.getElementById('detailModal');
        (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide');
    });

    $(TABLE).on('click', '.btnDetail', function () {
        var d = $(this).data();

        $('#d_garden_code').text(d.garden_code || '-');
        $('#d_garden_name').text(d.garden_name || '-');
        $('#d_farmer_name').text(d.farmer_name || '-');
        $('#d_address').text('-');
        $('#d_village').text((d.village || '-') + ' / ' + (d.district || '-'));
        $('#d_commodity').text(d.commodity || '-');
        $('#d_land_ownership').text(d.land_ownership || '-');
        $('#d_area_ha').text(d.area_ha || '-');
        $('#d_status').html(statusBadge(d.status));
        $('#d_code_segments').text('-');
        $('#d_source').text('-');
        $('#d_garden_source').html(sourceBadge(d.source, d.source_label));
        $('#d_synced_to_lan_at').text(d.synced_to_lan_at || 'Belum tersinkron');
        $('#d_approved_at').text(d.approved_at || '-');

        showModal();

        $.get(BASE_URL + '/' + d.id, function (res) {
            if (res.status !== 'success') return;
            var data = res.data;
            $('#d_address').text(data.address || '-');
            $('#d_code_segments').text((data.region_code || '-') + ' / ' + (data.section_code || '-') + ' / ' + (data.land_type || '-'));
            $('#d_source').text(data.survey ? (data.survey.source_label || data.survey.source) : '-');
            $('#d_garden_source').html(sourceBadge(data.source, data.source_label));
            $('#d_synced_to_lan_at').text(data.synced_to_lan_at || 'Belum tersinkron');

            ensureDetailMap();
            setTimeout(function () {
                detailMap.invalidateSize();

                if (detailPolygon) { detailMap.removeLayer(detailPolygon); detailPolygon = null; }

                var points = data.points || [];
                if (points.length >= 3) {
                    var latlngs = points.map(function (p) { return [p.latitude, p.longitude]; });
                    detailPolygon = L.polygon(latlngs, { color: '#1a73e8', weight: 2, fillOpacity: 0.15 }).addTo(detailMap);
                    detailMap.fitBounds(detailPolygon.getBounds(), { padding: [20, 20] });
                } else if (points.length > 0) {
                    var group = L.featureGroup(points.map(function (p) { return L.circleMarker([p.latitude, p.longitude]); }));
                    detailMap.fitBounds(group.getBounds().pad(0.5));
                }
            }, 200);
        }, 'json');
    });

    // --- Add / Edit / Delete ---

    function showAddEditModal() { var el = document.getElementById('mModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
    function hideAddEditModal() { var el = document.getElementById('mModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }

    function addPointRow(lat, lng) {
        var idx = $('#pointsBody tr').length + 1;
        var $row = $(
            '<tr>' +
                '<td class="pt-no">' + idx + '</td>' +
                '<td><input type="number" step="any" class="form-control form-control-sm pt-lat" value="' + (lat != null ? lat : '') + '"></td>' +
                '<td><input type="number" step="any" class="form-control form-control-sm pt-lng" value="' + (lng != null ? lng : '') + '"></td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger btnRemovePoint">&times;</button></td>' +
            '</tr>'
        );
        $('#pointsBody').append($row);
    }

    function renumberPoints() {
        $('#pointsBody tr').each(function (i) { $(this).find('.pt-no').text(i + 1); });
    }

    function clearPoints() { $('#pointsBody').empty(); }

    function loadPoints(points) {
        clearPoints();
        (points || []).forEach(function (p) { addPointRow(p.latitude, p.longitude); });
    }

    $('#btnAddPoint').on('click', function () { addPointRow(null, null); });
    $(document).on('click', '.btnRemovePoint', function () {
        $(this).closest('tr').remove();
        renumberPoints();
    });

    // Preview kode kebun format PLOT ID HGU: {TIPE}.{ARAH1}{JARAK1}{ARAH2}{JARAK2}{PETAK}{PERIODE}.
    function updateGardenCodePreview() {
        var landType = $('#mForm [name="land_type"]').val();
        var dir1 = $('#mForm [name="dir1"]').val();
        var dist1 = $('#mForm [name="dist1"]').val();
        var dir2 = $('#mForm [name="dir2"]').val();
        var dist2 = $('#mForm [name="dist2"]').val();
        var petak = $('#mForm [name="petak_code"]').val();
        var periode = $('#mForm [name="periode_code"]').val();

        var complete = landType && dir1 && dist1 !== '' && dir2 && dist2 !== '' && petak !== '' && periode;

        if (!complete) {
            $('#f_garden_code_preview').val('');
            return;
        }

        var pad2 = function (v) { return String(v).padStart(2, '0'); };
        var code = landType + '.' + dir1 + pad2(dist1) + dir2 + pad2(dist2) + pad2(petak) + pad2(periode);
        $('#f_garden_code_preview').val(code);
    }

    $(document).on('input change', '#mForm [name="land_type"], #mForm [name="dir1"], #mForm [name="dist1"], #mForm [name="dir2"], #mForm [name="dist2"], #mForm [name="petak_code"], #mForm [name="periode_code"]', updateGardenCodePreview);

    function clearMForm() {
        document.getElementById('mForm').reset();
        $('#f_id').val('');
        clearPoints();
        $('#mForm [name="company_code"]').trigger('change');
        $('#mForm [name="farmer_id"]').trigger('change');
        updateGardenCodePreview();
    }

    $('#btnAdd').on('click', function () {
        clearMForm();
        $('#mTitle').text('Add Kebun');
        showAddEditModal();
    });

    $(document).on('click', '.btnCloseM', hideAddEditModal);

    $(TABLE).on('click', '.btnEdit', function () {
        var id = $(this).data('id');
        $.get(BASE_URL + '/' + id, function (res) {
            if (res.status !== 'success') { alert(res.errMsg || 'Gagal memuat data.'); return; }
            var d = res.data;
            clearMForm();
            $('#f_id').val(d.id);
            FIELDS.forEach(function (f) { $('#mForm [name="' + f + '"]').val(d[f] != null ? d[f] : '').trigger('change'); });

            if (d.region_code && d.region_code.length === 6) {
                $('#mForm [name="dir1"]').val(d.region_code.substr(0, 1)).trigger('change');
                $('#mForm [name="dist1"]').val(parseInt(d.region_code.substr(1, 2), 10));
                $('#mForm [name="dir2"]').val(d.region_code.substr(3, 1)).trigger('change');
                $('#mForm [name="dist2"]').val(parseInt(d.region_code.substr(4, 2), 10));
            }
            if (d.section_code && d.section_code.length === 4) {
                $('#mForm [name="petak_code"]').val(parseInt(d.section_code.substr(0, 2), 10));
                $('#mForm [name="periode_code"]').val(d.section_code.substr(2, 2)).trigger('change');
            }
            updateGardenCodePreview();

            loadPoints(d.points);
            $('#mTitle').text('Edit Kebun');
            showAddEditModal();
        }, 'json').fail(function () { alert('Gagal memuat data.'); });
    });

    $(TABLE).on('click', '.btnDelete', function () {
        var id = $(this).data('id');
        if (!confirm('Hapus data ini beserta koordinatnya?')) return;
        $.post(DELETE_URL, { id: id }, function (res) {
            if (res.status === 'success') reload(); else alert(res.errMsg || 'Gagal menghapus');
        }, 'json');
    });

    $('#mForm').on('submit', function (e) {
        e.preventDefault();

        var points = [];
        $('#pointsBody tr').each(function () {
            var lat = $(this).find('.pt-lat').val();
            var lng = $(this).find('.pt-lng').val();
            if (lat !== '' && lng !== '') {
                points.push({ latitude: parseFloat(lat), longitude: parseFloat(lng) });
            }
        });
        $('#f_points').val(JSON.stringify(points));

        $.post(SAVE_URL, $(this).serialize(), function (res) {
            if (res.status === 'success') { hideAddEditModal(); reload(); }
            else alert(res.errMsg || 'Gagal menyimpan');
        }, 'json');
    });
});
</script>
@endsection
