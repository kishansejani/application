@extends('admin.layouts.admin')

@section('title', 'Add role')

@section('content')
    <x-admin.page-header title="Add role" subtitle="Name the role and choose which admin modules it can access." icon="shield-plus">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Roles</a>
    </x-admin.page-header>

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        @include('admin.roles.partials.form', ['role' => null, 'rolePermissions' => []])
    </form>
@endsection
