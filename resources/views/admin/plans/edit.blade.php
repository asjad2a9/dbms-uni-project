@extends('layouts.admin')
@section('title', 'Edit Plan')
@section('subtitle', $plan->name)
@section('content')
<form method="POST" action="{{ route('admin.plans.update', $plan) }}">
    @csrf @method('PUT')
    @include('admin.plans._form')
</form>
@endsection
