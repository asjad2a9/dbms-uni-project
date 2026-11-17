@extends('layouts.admin')
@section('title', 'Add User')
@section('content')
<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    @include('admin.users._form')
</form>
@endsection
