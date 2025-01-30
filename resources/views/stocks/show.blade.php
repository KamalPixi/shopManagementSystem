@extends('layouts.default')

@section('content')
    @livewire('stocks.stock-show', ['product_id' => $product_id])
@endsection
