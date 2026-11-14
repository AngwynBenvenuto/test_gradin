@extends('errors.layout')

@section('title', __('429 Too Many Request'))
@section('code', '429')
@section('message', __('Too Many Request'))
@section('note')
Upss.. try to break a second. Our server have too much request.
@endsection