@extends('admin.layouts.admin')

@section('title', 'Edit user - ' . $user->name)

@section('content')
    <x-admin.page-header :title="'Edit user: '.$user->name" subtitle="Update profile details, role assignment and credentials." icon="user-gear">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Users</a>
    </x-admin.page-header>

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.users.partials.form', ['user' => $user])
    </form>
@endsection
