@extends('admin.layouts.admin')

@section('title', 'Edit slider banner')

@section('content')
    <x-admin.page-header :title="'Edit ' . ($slider->title_en ?: 'slider banner')" subtitle="Update banner content, image, link destination and order." icon="slideshow">
        <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back to sliders</a>
    </x-admin.page-header>

    @include('admin.sliders._form', ['categories' => $categories ?? collect(), 'products' => $products ?? collect(), 'offers' => $offers ?? collect(), 'slider' => $slider])
@endsection
