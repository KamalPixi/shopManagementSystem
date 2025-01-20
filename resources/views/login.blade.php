@extends('layouts.auth')

@section('content')

<section class="vh-lg-100 mt-5 mt-lg-0 bg-soft d-flex align-items-center">
    <div class="container">
    <div class="row justify-content-center">
       <div class="col-12 d-flex align-items-center justify-content-center">

         @livewire('login')

       </div>
    </div>
    </div>
 </section>

@endsection
