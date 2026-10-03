@extends('admin.layouts.admin')

@section('title', 'Edit offer ' . $offer->code)

@section('content')
    <x-admin.page-header :title="'Edit offer '.$offer->code" subtitle="Update coupon terms, discount values and validity." icon="ticket">
        <a href="{{ route('admin.offers.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Offers</a>
    </x-admin.page-header>

    <form action="{{ route('admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.offers.partials.form', ['offer' => $offer])
    </form>
@endsection
