@extends('layouts.admin')
@section('title', '429 Too Many Request')
@section('content')    
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="well">
                            <h1>Sorry, an error has occured</h1>
                            <p>Whoops.. Server has too much request!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection