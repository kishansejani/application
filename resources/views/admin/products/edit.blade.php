@extends('admin.layouts.admin')

@section('title', 'Edit product')

@section('content')
    <x-admin.page-header :title="'Edit ' . $product->name_en" :subtitle="'SKU ' . $product->sku . ' · update pricing, stock, images and bilingual content.'" icon="package">
        <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-outline"><i class="ph ph-arrow-square-out"></i> View on storefront</a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back</a>
    </x-admin.page-header>

    @include('admin.products._form', ['categories' => $categories, 'subCategories' => $subCategories, 'product' => $product])
@endsection
