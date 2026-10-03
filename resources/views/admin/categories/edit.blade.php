@extends('admin.layouts.admin')

@section('title', 'Edit category')

@section('content')
    <x-admin.page-header :title="'Edit ' . $category->name_en" subtitle="Update details, bilingual content, icon and image." icon="shapes">
        <a href="{{ route('categories.show', $category->slug) }}" target="_blank" class="btn btn-outline"><i class="ph ph-arrow-square-out"></i> View on storefront</a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back</a>
    </x-admin.page-header>

    @include('admin.categories._form', ['category' => $category])
@endsection
