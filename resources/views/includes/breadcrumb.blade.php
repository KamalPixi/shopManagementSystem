<div class="pt-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <div class="icon icon-xxs">
                    <i class="fa-solid fa-house"></i>
                </div>
            </a>
            </li>
            @foreach ($data as $title => $url)
                <li class="breadcrumb-item @if($loop->last) active @endif">
                    <a href="{{ $url }}">{{ __($title) }}</a>
                </li>
            @endforeach
        </ol>
    </nav>
</div>
