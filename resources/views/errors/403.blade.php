@extends('errors.layout', ['code' => 403])

@section('code', '403')
@section('heading', 'Forbidden')
@section('message', $exception->getMessage() ?: 'You don't have permission to access this page.')
