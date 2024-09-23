@extends('layouts.app')
@section('title')
    {{ __('Manual Billing Payments') }}
@endsection

@section('content')
    @include('flash::message')
    <div class="container-fluid">
        <div class="d-flex flex-column">
            <livewire:manual-bill-payment-table/>
        </div>
    </div>
@endsection
