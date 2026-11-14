@extends('layouts.admin')
@section('title', '500 Internal Server Error')
@section('content')    
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="well">
                            <h1>Ups.., an internal error has occured.</h1>
                            <p>Try checking the URL for error, then hit the refresh button 
                                on your browser or try found something else in our app.</p>
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