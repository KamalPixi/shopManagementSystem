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

<!-- Developer Info Modal (Compact Terminal / Code Style) -->
<div class="modal fade" id="developerInfoModal" tabindex="-1" aria-labelledby="developerInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 380px;">
        <div class="modal-content border-0 shadow-lg rounded-3" style="background: #0f172a; color: #94a3b8; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Courier New', monospace; font-size: 0.78rem;">
            <div class="modal-body p-3 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" style="font-size: 0.65rem;" data-bs-dismiss="modal" aria-label="Close"></button>
                
                {{-- Terminal title prompt --}}
                <div class="d-flex align-items-center mb-2 text-muted pb-2 border-bottom border-secondary border-opacity-25" style="font-size: 0.7rem;">
                    <span class="d-inline-block rounded-circle bg-danger me-1" style="width: 8px; height: 8px;"></span>
                    <span class="d-inline-block rounded-circle bg-warning me-1" style="width: 8px; height: 8px;"></span>
                    <span class="d-inline-block rounded-circle bg-success me-2" style="width: 8px; height: 8px;"></span>
                    <span class="text-light opacity-75">kamal@dev:~</span>
                </div>

                {{-- Code / Developer meta --}}
                <div class="line-height-base">
                    <div class="p-2 rounded" style="background: #1e293b; color: #f8fafc; font-size: 0.74rem;">
                        <div><span class="text-info">"name"</span>: <span class="text-light fw-bold">"Md Kamal Uddin"</span>,</div>
                        <div><span class="text-info">"tel_bd"</span>: <a href="tel:+8801811329970" class="text-decoration-none" style="color: #4ade80 !important; font-weight: 500;">"+8801811329970"</a>,</div>
                        <div><span class="text-info">"email"</span>: <a href="mailto:kamalpixi@gmail.com" class="text-decoration-none" style="color: #38bdf8 !important; font-weight: 500;">"kamalpixi@gmail.com"</a></div>
                    </div>
                </div>

                <div class="d-flex justify-content-end align-items-center mt-2 text-muted" style="font-size: 0.68rem;">
                    <button type="button" class="btn btn-dark btn-sm py-0 px-2 border border-secondary border-opacity-50 text-light" style="font-size: 0.68rem;" data-bs-dismiss="modal">esc</button>
                </div>
            </div>
        </div>
    </div>
</div>


