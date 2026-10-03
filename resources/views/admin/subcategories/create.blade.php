@extends('admin.layouts.admin')

@section('title', 'Add subcategory')

@section('content')
    <x-admin.page-header title="Add subcategory" subtitle="Create a subcategory under a parent category with bilingual names and an image." icon="tree-structure">
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back to subcategories</a>
    </x-admin.page-header>

    @include('admin.subcategories._form', ['categories' => $categories])
@endsection
