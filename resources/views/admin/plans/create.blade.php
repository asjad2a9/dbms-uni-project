@extends('layouts.admin')
@section('title', 'New Plan')
@section('content')
<form method="POST" action="{{ route('admin.plans.store') }}">
    @csrf
    @include('admin.plans._form')
</form>
@endsection
