@extends('admin.layouts.admin')

@section('title', 'Add slider banner')

@section('content')
    <x-admin.page-header title="Add slider banner" subtitle="Upload a promotional banner for the home page with English and Gujarati text." icon="slideshow">
        <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Back to sliders</a>
    </x-admin.page-header>

    @include('admin.sliders._form', ['categories' => $categories ?? collect(), 'products' => $products ?? collect(), 'offers' => $offers ?? collect()])
@endsection
