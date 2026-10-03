@extends('admin.layouts.admin')

@section('title', 'Edit subcategory')

@section('content')
    <x-admin.page-header :title="'Edit ' . $subcategory->name_en" subtitle="Update the parent category, bilingual names and image." icon="tree-structure">
        <a href="{{ route('subcategories.show', $subcategory->slug) }}" target="_blank" class="btn btn-outline"><i class="ph ph-arrow-square-out"></i> View on storefront</a>
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back</a>
    </x-admin.page-header>

    @include('admin.subcategories._form', ['categories' => $categories, 'subcategory' => $subcategory])
@endsection
