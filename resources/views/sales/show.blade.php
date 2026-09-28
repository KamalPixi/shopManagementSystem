@extends('layouts.default')

@section('content')
    @livewire('sales.sale-show', ['sale_id' => $sale_id])
@endsection
