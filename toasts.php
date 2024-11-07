<!--  toast -->
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3 mt-4">
        <div class="toast d-flex toast--success" style="
                animation: 0.3s ease 0s 1 normal none running slideInLeft,
                1s linear 5s 1 normal forwards running fadeOut;">
            <div class="toast__icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="toast__body">
                <h3 class="toast__title mb-0">Khách hàng đã được xóa!</h3>
            </div>
            <div class="toast__close" onclick="this.parentElement.style.display='none';">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
<!-- error -->
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3 mt-4">
        <div class="toast d-flex toast--error" style="
                animation: 0.3s ease 0s 1 normal none running slideInLeft,
                1s linear 5s 1 normal forwards running fadeOut;">
            <div class="toast__icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="toast__body">
                <h3 class="toast__title mb-0">Có lỗi xảy ra!</h3>
            </div>
            <div class="toast__close" onclick="this.parentElement.style.display='none';">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
<!-- add -->
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'success'):?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3 mt-4">
        <div class="toast d-flex toast--success" style="
                animation: 0.3s ease 0s 1 normal none running slideInLeft,
                1s linear 5s 1 normal forwards running fadeOut;">
            <div class="toast__icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="toast__body">
                <h3 class="toast__title mb-0">Thêm thành công!</h3>
            </div>
            <div class="toast__close" onclick="this.parentElement.style.display='none';">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
<!-- update -->
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'):?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3 mt-4">
        <div class="toast d-flex toast--success" style="
                animation: 0.3s ease 0s 1 normal none running slideInLeft,
                1s linear 5s 1 normal forwards running fadeOut;">
            <div class="toast__icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="toast__body">
                <h3 class="toast__title mb-0">Cập nhật thành công!</h3>
            </div>
            <div class="toast__close" onclick="this.parentElement.style.display='none';">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
<?php endif; ?>