@extends('layouts.admin')
@section('title', 'Edit Expense')
@section('actions')
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">Expense Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
            @csrf
            @method('PUT')
            @include('admin.expenses._form')
        </form>
    </div>
</div>
@endsection
