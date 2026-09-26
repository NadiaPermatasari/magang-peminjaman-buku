@extends('errors.layout', ['code' => 503])

@section('code', '503')
@section('heading', 'Service unavailable')
@section('message', $exception->getMessage() ?: 'We are doing some maintenance. Please check back soon.')
