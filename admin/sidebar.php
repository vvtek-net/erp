

<div class="sidebar bg-dark text-white p-3">
    <h2 class="text-center">Admin</h2>
    <ul class="list-unstyled">
        <li class="mt-4"><a href="admin.php" class="text-white text-decoration-none">Thống kê nhập liệu</a></li>
        <!-- Mục Cha: Quản lý -->
            
                <li class="mb-2"><a href="manage_accounts.php" class="text-white text-decoration-none">Quản lý tài khoản</a></li>
                <li class="mb-2"><a href="admin_leave_management.php" class="text-white text-decoration-none">Nghỉ Phép</a></li>
                <li class="mb-2"><a href="admin_task_management.php" class="text-white text-decoration-none">Task</a></li>
                <li class="mb-2"><a href="manage_data.php" class="text-white text-decoration-none">Quản lý dữ liệu</a></li>
                <li class="mb-2"><a href="telesale_manage.php" class="text-white text-decoration-none">Quản lý Telesale</a></li>
                <li class="mb-2"><a href="manage_business_data.php" class="text-white text-decoration-none">Data Doanh Nghiệp</a></li>
                <li class="mb-2"><a href="contact_customer.php" class="text-white text-decoration-none">Quản lý Contacts</a></li>
                <li class="mb-2"><a href="manage_news.php" class="text-white text-decoration-none">Quản lý News</a></li>
            
        
        <!-- Mục Cha: Lọc dữ liệu -->
        <li class="mb-2">
            <a href="#filterSubmenu" data-toggle="collapse" class="text-white text-decoration-none" aria-expanded="false">Lọc dữ liệu</a>
            <ul class="collapse list-unstyled" id="filterSubmenu">
                <li class="mb-2"><a href="manage_data_sources.php" class="text-white text-decoration-none">Nguồn Data</a></li>
                <li class="mb-2"><a href="manage_contact_statuses.php" class="text-white text-decoration-none">Trạng thái tiếp xúc</a></li>
                <li class="mb-2"><a href="manage_customer_evaluations.php" class="text-white text-decoration-none">Đánh giá</a></li>
            </ul>
        </li>
        <!-- Mục Khác -->
        <li class="mt-4"><a href="logout.php" class="text-white text-decoration-none">Đăng xuất</a></li>
    </ul>
</div>

<script>
    // Đoạn mã JavaScript này sẽ thêm lớp 'active' cho mục hiện tại
    $(document).ready(function () {
        var path = window.location.pathname; // Lấy đường dẫn hiện tại
        var page = path.split("/").pop(); // Lấy tên tệp
        $(".sidebar a").each(function () {
            var href = $(this).attr('href');
            if (href === page) {
                $(this).addClass('active');
                $(this).closest('ul.collapse').addClass('show'); // Mở menu con nếu có
                $(this).closest('ul.collapse').prev('a').attr('aria-expanded', 'true');
            }
        });
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
