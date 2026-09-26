@extends('errors.layout', ['code' => 401])

@section('code', '401')
@section('heading', 'Unauthorized')
@section('message', $exception->getMessage() ?: 'You need to sign in to access this page.')
