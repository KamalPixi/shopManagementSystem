@if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between auto-hide-alert" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-circle-check me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session()->has('failed'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center justify-content-between auto-hide-alert" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <div>{{ session('failed') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session()->has('message'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center justify-content-between auto-hide-alert" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-circle-info me-2"></i>
            <div>{{ session('message') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show auto-hide-alert" role="alert">
        <div class="d-flex align-items-start justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

<script>
    (function() {
        function setupAlertAutoHide() {
            document.querySelectorAll('.auto-hide-alert').forEach(function(alertEl) {
                if (!alertEl.dataset.timerSet) {
                    alertEl.dataset.timerSet = 'true';
                    setTimeout(function() {
                        alertEl.classList.remove('show');
                        setTimeout(function() {
                            alertEl.remove();
                        }, 300);
                    }, 5000);
                }
            });
        }
        setupAlertAutoHide();
        document.addEventListener('DOMContentLoaded', setupAlertAutoHide);
        if (window.Livewire) {
            Livewire.hook('message.processed', setupAlertAutoHide);
        }
    })();
</script>

