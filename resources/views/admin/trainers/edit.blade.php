@extends('layouts.admin')
@section('title', 'Edit Trainer')
@section('subtitle', $trainer->name)
@section('content')
<form method="POST" action="{{ route('admin.trainers.update', $trainer) }}">
    @csrf @method('PUT')
    @include('admin.trainers._form')
</form>
@endsection
