@extends('admin.layouts.admin')

@section('title', 'Add user')

@section('content')
    <x-admin.page-header title="Add user" subtitle="Create an admin, staff member or customer account." icon="user-plus">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Users</a>
    </x-admin.page-header>

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        @include('admin.users.partials.form', ['user' => null])
    </form>
@endsection
