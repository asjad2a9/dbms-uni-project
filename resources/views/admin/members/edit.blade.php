@extends('layouts.admin')
@section('title', 'Edit Member')
@section('subtitle', $member->name . ' · ' . $member->member_code)
@section('actions')
    <a href="{{ route('admin.members.show', $member) }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back</span></a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.members.update', $member) }}">
    @csrf
    @method('PUT')
    @include('admin.members._form')

    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <a href="{{ route('admin.members.show', $member) }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Update Member</button>
    </div>
</form>
@endsection
