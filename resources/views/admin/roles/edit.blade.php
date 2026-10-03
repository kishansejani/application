@extends('admin.layouts.admin')

@section('title', 'Edit role - ' . $role->display_name)

@section('content')
    <x-admin.page-header :title="'Edit role: '.$role->display_name" subtitle="Update the display name, description and permission matrix." icon="shield-check">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Roles</a>
    </x-admin.page-header>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.roles.partials.form', ['role' => $role, 'rolePermissions' => $rolePermissions])
    </form>
@endsection
