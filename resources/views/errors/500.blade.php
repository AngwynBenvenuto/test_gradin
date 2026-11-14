@extends('errors.layout')

@section('title', __('500 Internal Server Error'))
@section('code', '500')
@section('message', __('Internal Server Error'))
@section('note')
Upss.. Sorry the Server currently error.<br>
Try checking the URL for error, then hit the refresh button on your browser 
or try found something else in our app.
@endsection