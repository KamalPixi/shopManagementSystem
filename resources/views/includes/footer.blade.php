<footer class="site-footer rounded-0 py-2 px-4 mt-auto mb-0">
    <div class="row align-items-center m-0">
      <div class="col-12 col-md-6 text-center text-md-start ps-2">
          <p class="mb-0 small">© <span class="current-year">{{ date('Y') }}</span> <a class="text-primary fw-semibold" href="#">{{ \App\Models\Shop::first()?->name }}</a></p>
      </div>
      <div class="col-12 col-md-6 text-center text-md-end pe-2">
          <ul class="list-inline mb-0">
            <li class="list-inline-item">
                <a href="javascript:void(0)" class="text-decoration-none text-muted" data-bs-toggle="modal" data-bs-target="#developerInfoModal" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-code text-primary me-1"></i>Developed By: <strong class="text-primary">GreenPixi</strong>
                </a>
            </li>
          </ul>
      </div>
    </div>
</footer>

<!-- Developer Info Modal -->
<div class="modal fade" id="developerInfoModal" tabindex="-1" aria-labelledby="developerInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h6 class="modal-title text-white mb-0" id="developerInfoModalLabel">
                    <i class="fa-solid fa-laptop-code me-2"></i>Developer Information
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div class="avatar-lg bg-light text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-1">Md Kamal Uddin</h5>
                <p class="text-muted small mb-3">Founder & Full-Stack Developer at <strong>GreenPixi</strong></p>
                <hr class="my-3">
                <div class="text-start px-2">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon icon-xs me-2 text-primary text-center" style="width: 24px;">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Phone (BD):</small>
                            <a href="tel:+8801811329970" class="text-dark fw-semibold text-decoration-none">+880 1811-329970</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon icon-xs me-2 text-success text-center" style="width: 24px;">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Phone (MY) / WhatsApp:</small>
                            <a href="https://wa.me/601111085061" target="_blank" class="text-dark fw-semibold text-decoration-none">+60 11-1108 5061</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="icon icon-xs me-2 text-danger text-center" style="width: 24px;">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Email:</small>
                            <a href="mailto:kamalpixi@gmail.com" class="text-dark fw-semibold text-decoration-none">kamalpixi@gmail.com</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light justify-content-end">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

