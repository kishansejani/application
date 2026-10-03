@extends('admin.layouts.admin')

@section('title', 'Add category')

@section('content')
    <x-admin.page-header title="Add category" subtitle="Create a top-level category with bilingual names, an icon and an image." icon="shapes">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back to categories</a>
    </x-admin.page-header>

    @include('admin.categories._form')
@endsection
