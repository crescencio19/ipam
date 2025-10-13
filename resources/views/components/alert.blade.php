@if (session('success'))
    <div id="successToast" class="bs-toast toast fade show bg-primary" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bx bx-bell me-2"></i>
            <div class="me-auto fw-semibold">Info</div>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            {{ session('success') }}
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('successToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            setTimeout(() => {
                toast.hide();
            }, 3000); // 3 detik
        }
    });
</script>
@endif
