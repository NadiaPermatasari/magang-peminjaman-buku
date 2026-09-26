@extends('errors.layout', ['code' => 500])

@section('code', '500')
@section('heading', 'Server error')
@section('message', $exception->getMessage() ?: 'Something went wrong on our side. Please try again later.')
