@extends('layouts.admin')
@section('title', '403 Forbidden')
@section('content')    
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="well">
                            <h1>Sorry, this page is forbidden</h1>
                            <p>You have requested page that is forbidden!</p><br>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-home"></i> Take me to home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection