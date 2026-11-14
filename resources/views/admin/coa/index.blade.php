@extends('layouts.admin')
@section('title', $title)
@section('content')
<?php
use Cete\Libraries\CTAdmin;
?>
<div class="content-wrapper" id="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="widget-box top mb-5">
                <div class="widget-title clearfix">
                    <h1>@yield('title')</h1>
                    <div class="buttons">
                        <?php if(CTAdmin::checkPermission('coa_add')) : ?>
                            <button id="btnAdd" class="btn btn-primary btnAdd lnj-color">Add @yield('title')</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="widget-content clearfix">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="coa" cellspacing="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">Action</th>
                                    <th width="15%">Coa Code</th>
                                    <th width="15%">Coa Name</th>
                                    <th>Model</th>
                                    <th>Tipe</th>
                                    <th nowrap>Tipe Detail</th>
                                    <th>Jenis</th>
                                    <th>Deb/Kre</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    var table = $.ct.appTable("#coa", {
        source: "{{ route('admin.coa.fetch') }}",
        columns: [
            {
                type: 'actions',
                className: 'no-vis',
                actions: [
                    {
                        label: 'Edit',
                        class: 'btnEdit',
                        data: row => ({
                            id: row.id
                        })
                    },
                    {
                        label: 'Delete',
                        class: 'btnDelete',
                        data: row => ({
                            id: row.id
                        })
                    }
                ]
            },
            { data: 'coa_code' },
            { 
                data: null,
                formatter: value => ''
            },
            { data: 'coa_model' },
            { data: 'coa_tipe' },
            { data: 'coa_tipe_detail' },
            { data: 'coa_jenis' },
            { data: 'coa_normal' },
            { data: 'note' },
            {
                data: 'status',
                formatter: value =>
                    value == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>'
            }
        ],
        filters: [
            {
                name: 'coa_code',
                label: 'Coa Code',
                type: 'text'
            },
            {
                name: 'coa_name',
                label: 'Coa Name',
                type: 'text'
            },
            {
                name: 'coa_jenis',
                label: 'Jenis',
                type: 'text'
            },
            {
                name: 'coa_normal',
                label: 'D/K',
                type: 'select',
                options: {
                    1: 'D',
                    2: 'K'
                }
            },
            {
                name: 'status',
                label: 'Status',
                type: 'select',
                options: {
                    1: 'Active',
                    0: 'Inactive'
                }
            },
        ],
        responsive: true,
        order: [[1, 'asc']]
    });

    
    
    //
    $("#btnAdd").on('click', function() {
        window.location.href = "{{ route('admin.coa.add') }}";
    });
    $("#coa").on('click', '.btnEdit', function() {
        var id = $(this).data('id');
        var url = '{{ route("admin.coa.edit", ":id") }}';
        url = url.replace(":id", id);
        window.location.href = url;

    });
})
</script>
@endsection