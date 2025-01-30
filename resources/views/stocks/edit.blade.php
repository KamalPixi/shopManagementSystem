@extends('layouts.default')

@section('content')
    @livewire('stocks.stock-edit', ['product_id' => $product_id])
@endsection
