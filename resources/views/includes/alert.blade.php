@if(session()->has('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session()->has('failed'))
    <div class="alert alert-danger">
        {{ session('failed') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
