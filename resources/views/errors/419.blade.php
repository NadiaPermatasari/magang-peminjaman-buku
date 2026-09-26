@extends('errors.layout', ['code' => 419])

@section('code', '419')
@section('heading', 'Page expired')
@section('message', $exception->getMessage() ?: 'Your session has expired. Please refresh the page and try again.')
