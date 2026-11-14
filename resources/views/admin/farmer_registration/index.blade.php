@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="content-wrapper" id="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs mb-3" id="petaniTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-pendaftaran-btn" data-bs-toggle="tab" data-bs-target="#tab-pendaftaran" type="button" role="tab">
                        Pendaftaran
                        <span class="badge bg-danger ms-1{{ $pendingCount > 0 ? '' : ' d-none' }}" id="pendingBadge">{{ $pendingCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-petani-btn" data-bs-toggle="tab" data-bs-target="#tab-petani" type="button" role="tab">
                        Master Petani
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="petaniTabsContent">

                {{-- ================= TAB: PENDAFTARAN ================= --}}
                <div class="tab-pane fade show active" id="tab-pendaftaran" role="tabpanel">
                    <div class="widget-box">
                        <div class="widget-title clearfix">
                            <h1>Pendaftaran Petani</h1>
                        </div>
                        <div class="widget-content clearfix">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tblReg" cellspacing="0" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th width="5%">Action</th>
                                            <th>Nama</th>
                                            <th>Telepon</th>
                                            <th>Desa / Kecamatan</th>
                                            <th>Nama Kebun</th>
                                            <th>Komoditas</th>
                                            <th>Luas (Ha)</th>
                                            <th>Status</th>
                                            <th>Tanggal Daftar</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= TAB: MASTER PETANI ================= --}}
                <div class="tab-pane fade" id="tab-petani" role="tabpanel">
                    <div class="widget-box">
                        <div class="widget-title clearfix">
                            <h1>Master Petani</h1>
                            <div class="buttons">
                                <button id="btnAddFarmer" class="btn btn-primary lnj-color">Add Petani</button>
                            </div>
                        </div>
                        <div class="widget-content clearfix">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tblFarmer" cellspacing="0" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th width="5%">Action</th>
                                            <th>Kode Petani</th>
                                            <th>Nama</th>
                                            <th>NIK</th>
                                            <th>Telepon</th>
                                            <th>Company</th>
                                            <th>Kebun</th>
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
    </div>
</div>

{{-- Modal Detail Pendaftaran --}}
<div class="modal fade" id="regDetailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail</h5>
        <button type="button" class="btn-close btnCloseReg" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Data Petani</h6>
            <table class="table table-sm table-borderless mb-3">
                <tr><td width="40%">Nama</td><td id="d_full_name">-</td></tr>
                <tr><td>NIK</td><td id="d_nik">-</td></tr>
                <tr><td>Telepon</td><td id="d_phone">-</td></tr>
                <tr><td>Email</td><td id="d_email">-</td></tr>
                <tr><td>Jenis Kelamin</td><td id="d_gender">-</td></tr>
                <tr><td>Tempat, Tanggal Lahir</td><td id="d_birth">-</td></tr>
                <tr><td>Agama</td><td id="d_religion">-</td></tr>
                <tr><td>Status Perkawinan</td><td id="d_marital_status">-</td></tr>
                <tr><td>Alamat</td><td id="d_address">-</td></tr>
                <tr><td>Status</td><td id="d_status">-</td></tr>
                <tr><td>Tanggal Daftar</td><td id="d_submitted_at">-</td></tr>
            </table>
            <h6>Rekening</h6>
            <table class="table table-sm table-borderless mb-3">
                <tr><td width="40%">Nama Bank</td><td id="d_reg_bank_name">-</td></tr>
                <tr><td>No. Rekening</td><td id="d_reg_bank_rek">-</td></tr>
                <tr><td>Atas Nama</td><td id="d_reg_bank_account_name">-</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6>Data Kebun</h6>
            <table class="table table-sm table-borderless mb-3">
                <tr><td width="40%">Nama Kebun</td><td id="d_farm_name">-</td></tr>
                <tr><td>Alamat Kebun</td><td id="d_farm_address">-</td></tr>
                <tr><td>Desa</td><td id="d_village">-</td></tr>
                <tr><td>Kecamatan</td><td id="d_district">-</td></tr>
                <tr><td>Komoditas</td><td id="d_commodity">-</td></tr>
                <tr><td>Status Lahan</td><td id="d_land_ownership">-</td></tr>
                <tr><td>Luas (Ha)</td><td id="d_land_area_ha">-</td></tr>
                <tr><td>Koordinat</td><td id="d_coordinate">-</td></tr>
                <tr><td>Status</td><td id="d_garden_status">-</td></tr>
            </table>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <h6>Foto</h6>
            <div class="d-flex flex-wrap gap-2 mb-3" id="d_photos"></div>
          </div>
        </div>
        <div class="row mb-3" id="d_notes_row">
          <div class="col-12">
            <h6>Catatan</h6>
            <p id="d_notes">-</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btnCloseReg" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-success" id="btnApproveModal"><i class="fas fa-check"></i> Setujui</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Add/Edit Petani --}}
<div class="modal fade" id="farmerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="farmerForm">
        @csrf
        <input type="hidden" id="f_id" name="id">
        <div class="modal-header">
          <h5 class="modal-title" id="farmerModalTitle">Add Petani</h5>
          <button type="button" class="btn-close btnCloseFarmer" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <label class="control-label">Nama Lengkap <span style="color:red">*</span></label>
                <input class="form-control" type="text" name="full_name" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">NIK</label>
                <input class="form-control" type="text" name="nik" maxlength="16" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Telepon <span style="color:red">*</span></label>
                <input class="form-control" type="text" name="phone" autocomplete="off">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="control-label">Jenis Kelamin</label>
                <select class="form-control" name="gender">
                  <option value="">-</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="control-label">Status Perkawinan</label>
                <input class="form-control" type="text" name="marital_status" autocomplete="off">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="control-label">Agama</label>
                <input class="form-control" type="text" name="religion" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Tempat Lahir</label>
                <input class="form-control" type="text" name="birth_place" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Tanggal Lahir</label>
                <input class="form-control" type="date" name="birth_date" autocomplete="off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-2">
                <label class="control-label">Email</label>
                <input class="form-control" type="email" name="email" autocomplete="off">
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
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="control-label">Bank</label>
                <input class="form-control" type="text" name="bank_name" autocomplete="off">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="control-label">No. Rekening</label>
                <input class="form-control" type="text" name="bank_account_number" autocomplete="off">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mb-2">
                <label class="control-label">Nama Pemilik Rekening</label>
                <input class="form-control" type="text" name="bank_account_holder" autocomplete="off">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btnCloseFarmer" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Daftar Kebun --}}
<div class="modal fade" id="farmerGardensModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Daftar Kebun <span id="g_farmer_name"></span></h5>
        <button type="button" class="btn-close btnCloseFarmerG" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead>
              <tr>
                <th>Kode Kebun</th>
                <th>Nama Kebun</th>
                <th>Desa / Kecamatan</th>
                <th>Luas (Ha)</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="g_gardens_body"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btnCloseFarmerG" data-bs-dismiss="modal">Tutup</button>
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

    var pendingCount = {{ (int) $pendingCount }};

    function updatePendingBadge() {
        var $b = $('#pendingBadge');
        if (pendingCount > 0) { $b.text(pendingCount).removeClass('d-none'); }
        else { $b.addClass('d-none'); }
    }

    // =====================================================================
    // TAB: Pendaftaran
    // =====================================================================
    var TABLE_REG = '#tblReg';

    function statusBadgeReg(status) {
        var map = {
            submitted: ['bg-warning', 'Menunggu Persetujuan'],
            approved: ['bg-success', 'Disetujui'],
        };
        var s = map[status] || ['bg-secondary', status];
        return '<span class="badge ' + s[0] + '">' + s[1] + '</span>';
    }

    $.ct.appTable(TABLE_REG, {
        source: "{{ route('admin.farmer_registration.fetch') }}",
        columns: [
            { type: 'actions', className: 'no-vis', actions: [
                { label: 'Detail', class: 'btnDetailReg', data: row => ({ id: row.id }) },
                { label: 'Setujui', class: 'btnApproveReg', data: row => ({ id: row.id, status: row.status }) }
            ]},
            { data: 'full_name' },
            { data: 'phone' },
            { combine: ['village', 'district'], formatter: (v) => (v.village || '') + ' / ' + (v.district || '') },
            { data: 'farm_name' },
            { data: 'commodity' },
            { data: 'land_area_ha' },
            { data: 'status', formatter: v => statusBadgeReg(v) },
            { data: 'submitted_at' }
        ],
        responsive: true,
        order: [[8, 'desc']]
    });

    function reloadReg() { $(TABLE_REG).DataTable().ajax.reload(null, false); }
    function showRegModal() { var el = document.getElementById('regDetailModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
    function hideRegModal() { var el = document.getElementById('regDetailModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }

    $(document).on('click', '.btnCloseReg', hideRegModal);

    function loadDetail(id) {
        $.get("{{ url('/farmer-registration') }}/" + id, function (res) {
            if (res.status !== 'success') return;
            var d = res.data;

            $('#d_full_name').text(d.full_name || '-');
            $('#d_nik').text(d.nik || '-');
            $('#d_phone').text(d.phone || '-');
            $('#d_email').text(d.email || '-');
            $('#d_gender').text(d.gender || '-');
            $('#d_birth').text((d.birth_place || d.birth_date) ? ((d.birth_place || '-') + ', ' + (d.birth_date || '-')) : '-');
            $('#d_religion').text(d.religion || '-');
            $('#d_marital_status').text(d.marital_status || '-');
            $('#d_address').text(d.address || '-');
            $('#d_status').html(statusBadgeReg(d.status));
            $('#d_submitted_at').text(d.submitted_at || '-');

            $('#d_reg_bank_name').text(d.bank_name || '-');
            $('#d_reg_bank_rek').text(d.bank_rek || '-');
            $('#d_reg_bank_account_name').text(d.bank_account_name || '-');

            $('#d_farm_name').text(d.farm_name || '-');
            $('#d_farm_address').text(d.farm_address || '-');
            $('#d_village').text(d.village || '-');
            $('#d_district').text(d.district || '-');
            $('#d_commodity').text(d.commodity || '-');
            $('#d_land_ownership').text(d.land_ownership || '-');
            $('#d_land_area_ha').text(d.land_area_ha || '-');
            $('#d_coordinate').text(d.latitude && d.longitude ? (d.latitude + ', ' + d.longitude) : '-');
            $('#d_garden_status').text(d.garden_status_label || '-');

            $('#d_notes').text(d.notes || '-');

            var $photos = $('#d_photos').empty();
            var labels = { ktp: 'KTP', selfie: 'Foto Diri', npwp: 'NPWP', farm: 'Foto Kebun', bank_account: 'Buku Rekening' };
            Object.keys(labels).forEach(function (type) {
                var url = d.photos ? d.photos[type] : null;
                if (!url) return;
                $photos.append(
                    '<a href="' + url + '" target="_blank" class="text-center">' +
                        '<img src="' + url + '" style="width:120px;height:120px;object-fit:cover;border:1px solid #ddd;border-radius:4px;">' +
                        '<div class="small">' + labels[type] + '</div>' +
                    '</a>'
                );
            });
            if (!$photos.children().length) $photos.html('<span class="text-muted">Tidak ada foto</span>');

            $('#btnApproveModal').data('id', d.id).toggleClass('d-none', d.status === 'approved');

            showRegModal();
        }, 'json');
    }

    function approve(id, onSuccess) {
        $.post("{{ url('/farmer-registration') }}/" + id + "/approve", {}, function (res) {
            if (res.status === 'success') {
                var farmer = res.data.farmer;

                if (res.data.mou_warning) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Disetujui, tapi MOU bermasalah',
                        text: 'Kode Petani: ' + farmer.farmer_code + '. ' + res.data.mou_warning
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Disetujui',
                        text: 'Pendaftaran disetujui. Kode Petani: ' + farmer.farmer_code
                    });
                }

                pendingCount = Math.max(0, pendingCount - 1);
                updatePendingBadge();
                if (farmerTableInitialized) { $(TABLE_FARMER).DataTable().ajax.reload(null, false); }

                if (onSuccess) onSuccess();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menyetujui pendaftaran' });
            }
        }, 'json').fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menyetujui pendaftaran';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        });
    }

    function confirmApprove(id, onSuccess) {
        Swal.fire({
            icon: 'question',
            title: 'Setujui pendaftaran ini?',
            text: 'Data petani dan SPK pengukuran akan dibuat otomatis.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) approve(id, onSuccess);
        });
    }

    $(TABLE_REG).on('click', '.btnDetailReg', function () {
        loadDetail($(this).data('id'));
    });

    $(TABLE_REG).on('click', '.btnApproveReg', function () {
        var d = $(this).data();
        if (d.status === 'approved') {
            Swal.fire({ icon: 'info', title: 'Sudah Disetujui', text: 'Pendaftaran ini sudah disetujui.' });
            return;
        }
        confirmApprove(d.id, reloadReg);
    });

    $('#btnApproveModal').on('click', function () {
        var id = $(this).data('id');
        if (!id) return;
        confirmApprove(id, function () { hideRegModal(); reloadReg(); });
    });

    // =====================================================================
    // TAB: Master Petani (lazy init, DataTables tidak boleh di-init di
    // dalam tab-pane yang masih hidden -- lebar kolomnya berantakan)
    // =====================================================================
    var TABLE_FARMER = '#tblFarmer';
    var farmerTableInitialized = false;

    function initFarmerTable() {
        var SAVE = "{{ route('admin.master.farmer.save') }}";
        var DEL  = "{{ route('admin.master.farmer.delete') }}";
        var GARDENS_URL = "{{ url('/master/farmer') }}";
        var FIELDS = [
            'company_code', 'full_name', 'nik', 'phone', 'gender', 'marital_status',
            'religion', 'birth_place', 'birth_date', 'email', 'address',
            'bank_name', 'bank_account_number', 'bank_account_holder', 'status'
        ];

        $.ct.appTable(TABLE_FARMER, {
            source: "{{ route('admin.master.farmer.fetch') }}",
            columns: [
                { type: 'actions', className: 'no-vis', actions: [
                    { label: 'Edit',   class: 'btnEditFarmer',   data: row => row },
                    { label: 'Unduh MOU', class: 'btnAgreementFarmer', data: row => ({ id: row.id }) },
                    { label: 'Delete', class: 'btnDeleteFarmer', data: row => ({ id: row.id }) }
                ]},
                { data: 'farmer_code' },
                { data: 'full_name' },
                { data: 'nik' },
                { data: 'phone' },
                { data: 'company_code' },
                { data: 'gardens_count', formatter: (v, row) => {
                    var name = String(row.full_name || '').replace(/"/g, '&quot;');
                    return '<a href="javascript:;" class="btnGardens" data-id="' + row.id + '" data-name="' + name + '">' + (v || 0) + '</a>';
                } },
                { data: 'status', formatter: v => {
                    var map = { active: 'success', inactive: 'secondary', blocked: 'danger' };
                    var label = { active: 'Aktif', inactive: 'Tidak aktif', blocked: 'Diblokir' };
                    var cls = map[v] || 'secondary';
                    return '<span class="badge bg-' + cls + '">' + (label[v] || v) + '</span>';
                } }
            ],
            responsive: true,
            order: [[1, 'desc']]
        });

        function reloadFarmer() { $(TABLE_FARMER).DataTable().ajax.reload(null, false); }
        function showFarmerModal() { var el = document.getElementById('farmerModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
        function hideFarmerModal() { var el = document.getElementById('farmerModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }
        function clearFarmerForm() {
            document.getElementById('farmerForm').reset();
            $('#f_id').val('');
            $('#farmerForm [name="company_code"]').trigger('change');
        }

        $('#btnAddFarmer').on('click', function () { clearFarmerForm(); $('#farmerModalTitle').text('Add Petani'); showFarmerModal(); });
        $(document).on('click', '.btnCloseFarmer', hideFarmerModal);

        $(TABLE_FARMER).on('click', '.btnEditFarmer', function () {
            var d = $(this).data();
            clearFarmerForm();
            $('#f_id').val(d.id);
            FIELDS.forEach(function (f) { $('#farmerForm [name="' + f + '"]').val(d[f] != null ? d[f] : '').trigger('change'); });
            $('#farmerModalTitle').text('Edit Petani');
            showFarmerModal();
        });

        $(TABLE_FARMER).on('click', '.btnAgreementFarmer', function () {
            var id = $(this).data('id');
            window.open(GARDENS_URL + '/' + id + '/agreement/download', '_blank');
        });

        $(TABLE_FARMER).on('click', '.btnDeleteFarmer', function () {
            var id = $(this).data('id');
            if (!confirm('Hapus data ini?')) return;
            $.post(DEL, { id: id }, function (res) {
                if (res.status === 'success') reloadFarmer(); else alert(res.errMsg || 'Gagal menghapus');
            }, 'json');
        });

        $('#farmerForm').on('submit', function (e) {
            e.preventDefault();
            $.post(SAVE, $(this).serialize(), function (res) {
                if (res.status === 'success') { hideFarmerModal(); reloadFarmer(); }
                else alert(res.errMsg || 'Gagal menyimpan');
            }, 'json');
        });

        // --- Daftar Kebun ---
        function showGardensModal() { var el = document.getElementById('farmerGardensModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
        function hideGardensModal() { var el = document.getElementById('farmerGardensModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }
        $(document).on('click', '.btnCloseFarmerG', hideGardensModal);

        function gardenStatusBadge(status) {
            var map = { active: 'success', inactive: 'secondary', blocked: 'danger' };
            var label = { active: 'Aktif', inactive: 'Tidak aktif', blocked: 'Diblokir' };
            return '<span class="badge bg-' + (map[status] || 'secondary') + '">' + (label[status] || status || '-') + '</span>';
        }

        $(TABLE_FARMER).on('click', '.btnGardens', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            $('#g_farmer_name').text(name ? '- ' + name : '');
            $('#g_gardens_body').html('<tr><td colspan="5" class="text-center text-muted">Memuat...</td></tr>');
            showGardensModal();

            $.get(GARDENS_URL + '/' + id + '/gardens', function (res) {
                if (res.status !== 'success') {
                    $('#g_gardens_body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data.</td></tr>');
                    return;
                }

                var gardens = res.data.gardens || [];

                if (gardens.length === 0) {
                    $('#g_gardens_body').html('<tr><td colspan="5" class="text-center text-muted">Belum ada kebun.</td></tr>');
                    return;
                }

                var rows = gardens.map(function (g) {
                    return '<tr>' +
                        '<td>' + (g.garden_code || '-') + '</td>' +
                        '<td>' + (g.garden_name || '-') + '</td>' +
                        '<td>' + ((g.village || '-') + ' / ' + (g.district || '-')) + '</td>' +
                        '<td>' + (g.area_ha || '-') + '</td>' +
                        '<td>' + gardenStatusBadge(g.status) + '</td>' +
                        '</tr>';
                }).join('');

                $('#g_gardens_body').html(rows);
            }, 'json').fail(function () {
                $('#g_gardens_body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data.</td></tr>');
            });
        });
    }

    $('#tab-petani-btn').on('shown.bs.tab', function () {
        if (farmerTableInitialized) return;
        farmerTableInitialized = true;
        initFarmerTable();
    });
});
</script>
@endsection
