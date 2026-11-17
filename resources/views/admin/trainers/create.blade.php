@extends('layouts.admin')
@section('title', 'Add Trainer')
@section('content')
<form method="POST" action="{{ route('admin.trainers.store') }}">
    @csrf
    @include('admin.trainers._form')
</form>
@endsection
