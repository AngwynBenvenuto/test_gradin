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
                        <button id="btnBulkCancel" class="btn btn-outline-danger" disabled>
                            <i class="fas fa-ban"></i> Hanguskan Tiket (Terpilih)
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
                                    <th>No. Tiket</th>
                                    <th>No. SPP</th>
                                    <th>Kebun / Petak</th>
                                    <th>Sopir</th>
                                    <th>Tanggal Panen</th>
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
    var BASE_URL = "{{ url('/tiket-interupsi') }}";
    var BULK_CANCEL_URL = "{{ route('admin.harvest_order_cancel.cancel_bulk') }}";
    var selectedIds = new Set();

    $.ct.appTable(TABLE, {
        source: "{{ route('admin.harvest_order_cancel.cancel_fetch') }}",
        columns: [
            { data: 'id', className: 'no-vis', formatter: (id, row) => {
                var checked = selectedIds.has(String(id)) ? ' checked' : '';
                return '<input type="checkbox" class="rowCheck" value="' + id + '"' + checked + '>';
            }},
            { type: 'actions', className: 'no-vis', actions: [
                { label: 'Hanguskan Tiket', class: 'btnCancel', data: row => row }
            ]},
            { data: 'no_tiket', formatter: v => v || '-' },
            { data: 'no_spp', formatter: v => v || '-' },
            { combine: ['kodekebun', 'nopetak'], formatter: (v) => (v.kodekebun || '-') + ' / ' + (v.nopetak || '-') },
            { data: 'sopir_name', formatter: v => v || '-' },
            { data: 'tanggal_panen', formatter: v => v || '-' },
            { data: 'progress_label', formatter: v => v || '-' },
            { data: 'is_cancelled', formatter: v => v
                ? '<span class="badge bg-danger">Dibatalkan</span>'
                : '<span class="badge bg-success">Aktif</span>' },
            { data: 'created_at', formatter: v => v || '-' }
        ],
        responsive: true,
        order: [[9, 'desc']]
    });

    function updateBulkButton() {
        var count = selectedIds.size;
        $('#btnBulkCancel')
            .prop('disabled', count === 0)
            .html('<i class="fas fa-ban"></i> Hanguskan Tiket' + (count > 0 ? ' (' + count + ' dipilih)' : ' (Terpilih)'));
    }

    function syncChkAllState() {
        var eligible = 0;
        var checked = 0;
        $(TABLE).DataTable().rows({ page: 'current' }).every(function () {
            var d = this.data();
            if (d.is_cancelled) return;
            eligible++;
            if (selectedIds.has(String(d.id))) checked++;
        });
        var $chkAll = $('#chkAll');
        $chkAll.prop('checked', eligible > 0 && checked === eligible);
        $chkAll.prop('indeterminate', checked > 0 && checked < eligible);
    }

    $(TABLE).on('draw.dt', function () {
        $(TABLE).DataTable().rows().every(function () {
            var d = this.data();
            var $node = $(this.node());
            if (d.is_cancelled) {
                $node.find('.btnCancel').remove();
                $node.find('.rowCheck').prop('disabled', true).prop('checked', false);
                selectedIds.delete(String(d.id));
            }
        });
        syncChkAllState();
    });

    $('#chkAll').on('change', function () {
        var checked = this.checked;
        $(TABLE).DataTable().rows({ page: 'current' }).every(function () {
            var d = this.data();
            if (d.is_cancelled) return;
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

    $('#btnBulkCancel').on('click', function () {
        if (selectedIds.size === 0) return;
        var ids = Array.from(selectedIds);
        Swal.fire({
            icon: 'warning',
            title: 'Hanguskan ' + ids.length + ' tiket terpilih?',
            text: 'Tiket yang dihanguskan tidak dapat digunakan lagi dan harus dibuat ulang.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hanguskan Semua',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(BULK_CANCEL_URL, { ids: ids }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Selesai', text: res.message || 'Tiket terpilih berhasil dihanguskan.' });
                    selectedIds.clear();
                    updateBulkButton();
                    reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menghanguskan tiket terpilih.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menghanguskan tiket terpilih.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });

    $(TABLE).on('click', '.btnCancel', function () {
        var d = $(this).data();
        Swal.fire({
            icon: 'warning',
            title: 'Hanguskan tiket ' + (d.no_tiket || '') + '?',
            text: 'Tiket yang dihanguskan tidak dapat digunakan lagi dan harus dibuat ulang.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hanguskan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post(BASE_URL + '/' + d.id + '/cancel', {}, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message || 'Tiket berhasil dihanguskan.' });
                    selectedIds.delete(String(d.id));
                    updateBulkButton();
                    reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.errMsg || 'Gagal menghanguskan tiket.' });
                }
            }, 'json').fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errMsg) || 'Gagal menghanguskan tiket.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });
});
</script>
@endsection
