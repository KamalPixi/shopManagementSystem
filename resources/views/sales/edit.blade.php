@extends('layouts.default')

@section('content')
    @livewire('sales.sale-edit', ['sale_id' => $sale_id])
@endsection
