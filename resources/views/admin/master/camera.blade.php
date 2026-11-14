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
                        <button id="btnAdd" class="btn btn-primary lnj-color">Add @yield('title')</button>
                    </div>
                </div>
                <div class="widget-content clearfix">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tbl" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">Action</th>
                                    <th>Name</th>
                                    <th>IP</th>
                                    <th>Port</th>
                                    <th>Channel</th>
                                    <th>Brand</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Username</th>
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

{{-- Modal Add/Edit --}}
<div class="modal fade" id="mModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="mForm">
        @csrf
        <input type="hidden" id="f_id" name="id">
        <div class="modal-header">
          <h5 class="modal-title" id="mTitle">Add {{ $title }}</h5>
          <button type="button" class="btn-close btnClose" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-2">
            <label class="control-label">Name <span style="color:red">*</span></label>
            <input class="form-control" type="text" name="name" autocomplete="off">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">IP</label>
            <input class="form-control" type="text" name="ip" autocomplete="off" placeholder="IP kamera, atau IP NVR jika lewat NVR">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Port</label>
            <input class="form-control" type="text" name="port" autocomplete="off" placeholder="Kosongkan = 80">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Channel</label>
            <input class="form-control" type="text" name="channel" autocomplete="off" placeholder="Kosongkan = 101. Contoh via NVR: 301, 401">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Brand</label>
            <input class="form-control" type="text" name="brand" autocomplete="off">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Location</label>
            <input class="form-control" type="text" name="location" autocomplete="off">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Type</label>
            <input class="form-control" type="text" name="type" autocomplete="off">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Username</label>
            <input class="form-control" type="text" name="username" autocomplete="off">
          </div>
          <div class="form-group mb-2">
            <label class="control-label">Password</label>
            <input class="form-control" type="password" name="password" autocomplete="new-password" placeholder="(kosongkan jika tidak diubah)">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btnClose" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('footer_js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': AppHelper.csrfToken } });

    var TABLE  = '#tbl';
    var SAVE   = "{{ route('admin.master.camera.save') }}";
    var DEL    = "{{ route('admin.master.camera.delete') }}";
    var FIELDS = ['name', 'ip', 'port', 'channel', 'brand', 'location', 'type', 'username'];

    $.ct.appTable(TABLE, {
        source: "{{ route('admin.master.camera.fetch') }}",
        columns: [
            { type: 'actions', className: 'no-vis', actions: [
                { label: 'Edit',   class: 'btnEdit',   data: row => row },
                { label: 'Delete', class: 'btnDelete', data: row => ({ id: row.id }) }
            ]},
            { data: 'name' },
            { data: 'ip' },
            { data: 'port' },
            { data: 'channel' },
            { data: 'brand' },
            { data: 'location' },
            { data: 'type' },
            { data: 'username' },
            { data: 'status', formatter: v => v == 1
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Inactive</span>' }
        ],
        responsive: true,
        order: [[1, 'asc']]
    });

    function reload() { $(TABLE).DataTable().ajax.reload(null, false); }
    function showModal() { var el = document.getElementById('mModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
    function hideModal() { var el = document.getElementById('mModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }
    function clearForm() { document.getElementById('mForm').reset(); $('#f_id').val(''); }

    $('#btnAdd').on('click', function () { clearForm(); $('#mTitle').text('Add {{ $title }}'); showModal(); });
    $(document).on('click', '.btnClose', hideModal);

    $(TABLE).on('click', '.btnEdit', function () {
        var d = $(this).data();
        clearForm();
        $('#f_id').val(d.id);
        FIELDS.forEach(function (f) { $('#mForm [name="' + f + '"]').val(d[f] != null ? d[f] : ''); });
        $('#mTitle').text('Edit {{ $title }}');
        showModal();
    });

    $(TABLE).on('click', '.btnDelete', function () {
        var id = $(this).data('id');
        if (!confirm('Hapus data ini?')) return;
        $.post(DEL, { id: id }, function (res) {
            if (res.status === 'success') reload(); else alert(res.errMsg || 'Gagal menghapus');
        }, 'json');
    });

    $('#mForm').on('submit', function (e) {
        e.preventDefault();
        $.post(SAVE, $(this).serialize(), function (res) {
            if (res.status === 'success') { hideModal(); reload(); }
            else alert(res.errMsg || 'Gagal menyimpan');
        }, 'json');
    });
});
</script>
@endsection
