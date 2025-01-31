<div class="container text-center mt-11">
    <div class="card shadow-sm border-0" style="max-width: 600px; margin: auto;">
        <div class="card-body">
            <h1 class="text-danger display-4"><i class="bi bi-shield-lock-fill"></i></h1>
            <h2 class="text-danger">Access Denied</h2>
            <p class="text-muted">You do not have permission to view this page.</p>
            
            <a href="{{ url()->previous() }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Go Back
            </a>
            
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </div>
    </div>
</div>
