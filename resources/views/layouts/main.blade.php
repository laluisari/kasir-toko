<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

@include('partials.head')

<body>

    @include('partials.sidebar')

    <main class="dashboard-main">
        @include('partials.navbar')

        <div class="dashboard-main-body">

            @include('partials.breadcrumb')

            @yield('content')
        </div>

        @include('partials.footer')
    </main>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content" style="border-radius: 1rem; box-shadow: 0 20px 25px rgba(0,0,0,0.12); border: 1px solid #e2e8f0;">
                <div class="modal-header border-bottom" style="border-color:#e2e8f0 !important; background-color: #fef2f2;">
                    <h6 class="modal-title fw-semibold text-danger-600" id="confirmationTitle">Konfirmasi</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-24">
                    <p id="confirmationMessage" class="mb-0" style="line-height: 1.6; color: #475569;"></p>
                </div>
                <div class="modal-footer border-top d-flex gap-2" style="border-color:#e2e8f0 !important;">
                    <button type="button" class="btn btn-light radius-8 px-20 py-10 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger radius-8 px-20 py-10 fw-semibold" id="confirmationBtn">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.scripts')
    @yield('scripts')

    <script>
        // Global confirmation modal handler
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        let pendingForm = null;
        let pendingCallback = null;

        window.showConfirmation = function(message, callback, buttonText = 'Ya, Lanjutkan') {
            document.getElementById('confirmationTitle').textContent = 'Konfirmasi';
            document.getElementById('confirmationMessage').textContent = message;
            document.getElementById('confirmationBtn').textContent = buttonText;
            pendingCallback = callback;
            confirmationModal.show();
        };

        window.showFormConfirmation = function(form, message, buttonText = 'Ya, Lanjutkan') {
            document.getElementById('confirmationTitle').textContent = 'Konfirmasi';
            document.getElementById('confirmationMessage').textContent = message;
            document.getElementById('confirmationBtn').textContent = buttonText;
            pendingForm = form;
            confirmationModal.show();
        };

        document.getElementById('confirmationBtn').addEventListener('click', function() {
            confirmationModal.hide();
            if (pendingForm) {
                pendingForm.submit();
            } else if (pendingCallback) {
                pendingCallback();
            }
            pendingForm = null;
            pendingCallback = null;
        });

        // Helper function to prevent form submission and show modal instead
        window.handleFormSubmit = function(e, message, buttonText = 'Ya, Lanjutkan') {
            e.preventDefault();
            showFormConfirmation(e.target, message, buttonText);
        };
    </script>
</body>

</html>
