@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="ml-2 mb-4">
                <h2 class="mb-2">Dashboard</h2>
                <h5 class="text-body-tertiary fw-semibold">Here’s what’s going on at your business right now</h5>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="input-group input-daterange" id="periode">
                    <input type="text" id="start_date" class="form-control" autocomplete="off">
                    <span class="input-group-addon">to</span>
                    <input type="text" id="end_date" class="form-control" autocomplete="off">
                    <div class="input-group-addon" style="display: none">
                        <span class="fa fa-calendar"></span>
                    </div>
                    <button type="button" href="javascript:;" class="btn btn-primary lnj-color btnFilter" style="padding:5px 12px;margin-left:10px">
                        <i class="icon icon- fas fa-check  fas fa-check"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row mt-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between">
                    <div class="fa fa-folder display-4 text-success"></div>
                    <div class="ml-3">
                        <div class="text-muted small">Product</div>
                        <div class="text-large txtProduct">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between">
                    <div class="ri-user-2-line display-4 text-success"></div>
                    <div class="ml-3">
                        <div class="text-muted small">Employee</div>
                        <div class="text-large txtKaryawan">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="card card-custom p-3">
                <div class="d-flex justify-content-between">
                    <div class="uil-users-alt display-4 text-success"></div>
                    <div class="ml-3">
                        <div class="text-muted small">Customer</div>
                        <div class="text-large txtCustomer">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboards-row clearfix row mt-2">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <a href="javascript:;" class="white-link">
                <div id="js-clock-in-out" class="card dashboard-icon-widget clock-in-out-card">
                    <div class="card-body">
                        <div class="widget-icon bg-info"><i class="fa fa-clock"></i></div>
                        <div class="widget-details">
                            <a href="#" class="btn btn-light text-primary" 
                                title="Clock Out" id="timecard-clock-out"
                                data-act="ajax-modal" data-title="Clock Out" 
                                data-action-url="javascript:;">
                                Clock Out
                            </a>
                            <div class="mt5 bg-transparent-white" title="">Clock started at : &nbsp;</div>    
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <a href="javascript:;" class="white-link">
                <div class="card dashboard-icon-widget">
                    <div class="card-body">
                        <div class="widget-icon bg-primary"><i class="fa fa-list"></i></div>
                        <div class="widget-details">
                            <h1>&nbsp;</h1>
                            <span class="bg-transparent-white">My open tasks</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <a href="javascript:;" class="white-link">
                <div class="card dashboard-icon-widget">
                    <div class="card-body">
                        <div class="widget-icon bg-success"><i class="fa fa-calendar"></i></div>
                        <div class="widget-details">
                            <h1>&nbsp;</h1>
                            <span class="bg-transparent-white">Events Today</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <a href="javascript:;" class="white-link">
                <div class="card dashboard-icon-widget">
                    <div class="card-body">
                        <div class="widget-icon bg-danger"><i class="fa fa-compass"></i></div>
                        <div class="widget-details">
                            <h1>&nbsp;</h1>
                            <span class="bg-transparent-white">Due</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="card card-statistic-2">
                <div class="card-header">
                    
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="card card-statistic-2">
                <div class="card-header">
                    
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="card card-statistic-2">
                <div class="card-header">
                    
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>
    </div> --}}
</div>
<script>
$(document).ready(function() {
    $('#start_date').datepicker('setDate', 'new Date()').on('changeDate', function(selected){
        var minDate = new Date(selected.date.valueOf());
        $('#end_date').datepicker('setStartDate', minDate);
        $('#end_date').datepicker('update', minDate);
    });
    $(function(selected){
        var minDate = $('#start_date').val();
        $('#end_date').datepicker('setStartDate', minDate);
    });
    $('#end_date').datepicker('setDate', 'new Date()').on('changeDate', function(selected){
    });

    //
    $("#start_date").val(moment().subtract('30','days').format("MM/DD/YYYY"));
    $("#end_date").val(moment().format("MM/DD/YYYY"));

    //
    $(".btnFilter").on('click', function() {

    });


    //
    function loadAll() {

    }
});
</script>
@endsection
