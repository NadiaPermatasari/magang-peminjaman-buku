@extends('errors.layout', ['code' => 404])

@section('code', '404')
@section('heading', 'Page not found')
@section('message', $exception->getMessage() ?: 'The page you are looking for doesn't exist or has been moved.')
