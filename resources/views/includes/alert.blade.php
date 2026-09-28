{{-- Floating Toast Notification Container (Top-Right) --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999; pointer-events: none;">
    @if(session()->has('success'))
        <div class="toast show align-items-center text-white bg-success border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="pointer-events: auto; min-width: 320px; border-radius: 0.5rem;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center py-3 px-3">
                    <i class="fa-solid fa-circle-check fs-5 me-3"></i>
                    <div class="fw-semibold">{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session()->has('failed'))
        <div class="toast show align-items-center text-white bg-danger border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="pointer-events: auto; min-width: 320px; border-radius: 0.5rem;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center py-3 px-3">
                    <i class="fa-solid fa-triangle-exclamation fs-5 me-3"></i>
                    <div class="fw-semibold">{{ session('failed') }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session()->has('message'))
        <div class="toast show align-items-center text-white bg-info border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="pointer-events: auto; min-width: 320px; border-radius: 0.5rem;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center py-3 px-3">
                    <i class="fa-solid fa-circle-info fs-5 me-3"></i>
                    <div class="fw-semibold">{{ session('message') }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(!empty($errorMessage))
        <div class="toast show align-items-center text-white bg-danger border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="pointer-events: auto; min-width: 320px; border-radius: 0.5rem;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center py-3 px-3">
                    <i class="fa-solid fa-triangle-exclamation fs-5 me-3"></i>
                    <div class="fw-semibold">{{ $errorMessage }}</div>
                </div>
                <button type="button" wire:click="$set('errorMessage', '')" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="toast show align-items-center text-white bg-danger border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="pointer-events: auto; min-width: 320px; border-radius: 0.5rem;">
            <div class="d-flex">
                <div class="toast-body py-3 px-3">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa-solid fa-triangle-exclamation fs-5 me-2"></i>
                        <span class="fw-bold">{{ __('Please check the errors:') }}</span>
                    </div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif
</div>

<script>
    (function() {
        function setupToastAutoHide() {
            document.querySelectorAll('.toast-container .toast').forEach(function(toastEl) {
                if (!toastEl.dataset.toastInit) {
                    toastEl.dataset.toastInit = 'true';
                    setTimeout(function() {
                        toastEl.classList.remove('show');
                        setTimeout(function() {
                            toastEl.remove();
                        }, 400);
                    }, 4000);
                }
            });
        }
        setupToastAutoHide();
        document.addEventListener('DOMContentLoaded', setupToastAutoHide);
        if (window.Livewire) {
            Livewire.hook('message.processed', setupToastAutoHide);
        }
    })();
</script>

