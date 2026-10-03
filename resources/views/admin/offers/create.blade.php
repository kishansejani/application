@extends('admin.layouts.admin')

@section('title', 'Create offer')

@section('content')
    <x-admin.page-header title="Create offer" subtitle="Configure a promo code, its discount, minimum order and validity." icon="ticket">
        <a href="{{ route('admin.offers.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Offers</a>
    </x-admin.page-header>

    <form action="{{ route('admin.offers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.offers.partials.form', ['offer' => null])
    </form>
@endsection
