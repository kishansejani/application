@extends('admin.layouts.admin')

@section('title', 'Add product')

@section('content')
    <x-admin.page-header title="Add product" subtitle="Create a product with bilingual content, pricing, stock and an image gallery." icon="package">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back to products</a>
    </x-admin.page-header>

    @include('admin.products._form', ['categories' => $categories])
@endsection
