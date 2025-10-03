@extends('layouts.default')

@section('content')
  <x-statistics />

  @include('components.low-stock-products')
@endsection
