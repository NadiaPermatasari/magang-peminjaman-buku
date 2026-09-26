@extends('errors.layout', ['code' => 429])

@section('code', '429')
@section('heading', 'Too many requests')
@section('message', $exception->getMessage() ?: 'Slow down! You have sent too many requests. Please try again in a moment.')
