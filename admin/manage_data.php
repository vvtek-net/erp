<?php
include('src/manage_data_process.php');
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý dữ liệu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_data.css">

</head>

<body>
<?php include('sidebar.php'); ?>

    <div class="container">
        <?php if ($resultCount->num_rows > 0) {
            $rowCount = $resultCount->fetch_assoc();
            echo '<h2>Quản lý dữ liệu khách hàng - Tổng dữ liệu: ' . $rowCount['total'] . '</h2>';
        } ?>

        <div class="filters">
            <form method="GET" action="manage_data.php">
                <select name="user" onchange="this.form.submit()" class="<?php echo $filter_by_user ? 'filter-active' : ''; ?>">
                    <option value="">Lọc theo người nhập</option>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <option value="<?php echo $user['created_by']; ?>" <?php if ($filter_by_user == $user['created_by']) echo 'selected'; ?>>
                            <?php echo $user['created_by']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <select name="contact_status" onchange="this.form.submit()" class="<?php echo $filter_by_contact_status ? 'filter-active' : ''; ?>">
                    <option value="">Lọc theo trạng thái tiếp xúc</option>
                    <option value="Đã gọi và gửi mail" <?php if ($filter_by_contact_status == 'Đã gọi và gửi mail') echo 'selected'; ?>>Đã gọi và gửi mail</option>
                    <option value="Không nghe máy" <?php if ($filter_by_contact_status == 'Không nghe máy') echo 'selected'; ?>>Không nghe máy</option>
                    <option value="Thuê bao/Nhầm số" <?php if ($filter_by_contact_status == 'Thuê bao/Nhầm số') echo 'selected'; ?>>Thuê bao/Nhầm số</option>
                    <option value="Không nhu cầu" <?php if ($filter_by_contact_status == 'Không nhu cầu') echo 'selected'; ?>>Không nhu cầu</option>
                    <option value="Đang cân nhắc suy nghĩ" <?php if ($filter_by_contact_status == 'Đang cân nhắc suy nghĩ') echo 'selected'; ?>>Đang cân nhắc suy nghĩ</option>
                    <option value="Đang tư vấn" <?php if ($filter_by_contact_status == 'Đang tư vấn') echo 'selected'; ?>>Đang tư vấn</option>
                    <option value="KH tiềm năng" <?php if ($filter_by_contact_status == 'KH tiềm năng') echo 'selected'; ?>>KH tiềm năng</option>
                    <option value="Đã hẹn gặp" <?php if ($filter_by_contact_status == 'Đã hẹn gặp') echo 'selected'; ?>>Đã hẹn gặp</option>
                    <option value="Đã demo" <?php if ($filter_by_contact_status == 'Đã demo') echo 'selected'; ?>>Đã demo</option>
                    <option value="Báo giá" <?php if ($filter_by_contact_status == 'Báo giá') echo 'selected'; ?>>Báo giá</option>
                    <option value="Chờ ký HĐ" <?php if ($filter_by_contact_status == 'Chờ ký HĐ') echo 'selected'; ?>>Chờ ký HĐ</option>
                    <option value="Đã ký HĐTC" <?php if ($filter_by_contact_status == 'Đã ký HĐTC') echo 'selected'; ?>>Đã ký HĐTC</option>
                    <option value="KH Đã có web" <?php if ($filter_by_contact_status == 'KH Đã có web') echo 'selected'; ?>>KH Đã có web</option>
                </select>

                <select name="evaluation" onchange="this.form.submit()" class="<?php echo $filter_by_evaluation ? 'filter-active' : ''; ?>">
                    <option value="">Lọc theo Đánh giá</option>
                    <option value="Chưa có đánh giá" <?php if ($filter_by_evaluation == 'Chưa có đánh giá') echo 'selected'; ?>>Chưa có đánh giá</option>
                    <option value="Khách hàng mới tiếp xúc" <?php if ($filter_by_evaluation == 'Khách hàng mới tiếp xúc') echo 'selected'; ?>>Khách hàng mới tiếp xúc</option>
                    <option value="Khách hàng tiềm năng" <?php if ($filter_by_evaluation == 'Khách hàng tiềm năng') echo 'selected'; ?>>Khách hàng tiềm năng</option>
                    <option value="Khách hàng cơ hội" <?php if ($filter_by_evaluation == 'Khách hàng cơ hội') echo 'selected'; ?>>Khách hàng cơ hội</option>
                    <option value="Khách hàng chăm sóc sau bán" <?php if ($filter_by_evaluation == 'Khách hàng chăm sóc sau bán') echo 'selected'; ?>>Khách hàng chăm sóc sau bán</option>
                </select>

                <select name="data_source" onchange="this.form.submit()" class="<?php echo $filter_by_data_source ? 'filter-active' : ''; ?>">
                    <option value="">Lọc theo nguồn data</option>
                    <option value="Facebook (Lead)" <?php if ($filter_by_data_source == 'Facebook') echo 'selected'; ?>>Facebook</option>
                    <option value="Ucall (Lead)" <?php if ($filter_by_data_source == 'Ucall') echo 'selected'; ?>>Ucall</option>
                    <option value="Facebook Ads (Lead)" <?php if ($filter_by_data_source == 'Facebook Ads') echo 'selected'; ?>>Facebook Ads</option>
                    <option value="Google Ads (Lead)" <?php if ($filter_by_data_source == 'Google Ads') echo 'selected'; ?>>Google Ads</option>
                    <option value="Tự tìm kiếm" <?php if ($filter_by_data_source == 'Tự tìm kiếm') echo 'selected'; ?>>Tự tìm kiếm</option>
                </select>

                <input type="text" name="search" placeholder="Tìm kiếm tên khách hàng" value="<?php echo htmlspecialchars($search_query); ?>">
                <select name="limit" onchange="this.form.submit()">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                </select>
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </form>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Nguồn data</th>
                    <th>Trạng thái tiếp xúc</th>
                    <th>Đánh giá</th>
                    <th>Người nhập</th>
                    <th>Ngày nhập</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['data_source']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_evaluation']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td class="action-btns">
                                <a href="edit_data.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <a href="manage_data.php?delete_id=<?php echo $row['id']; ?>&user=<?php echo urlencode($filter_by_user); ?>&contact_status=<?php echo urlencode($filter_by_contact_status); ?>&evaluation=<?php echo urlencode($filter_by_evaluation); ?>&data_source=<?php echo urlencode($filter_by_data_source); ?>&search=<?php echo urlencode($search_query); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?');" class="btn btn-danger btn-sm">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Không có dữ liệu nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php
                $total_pages = ceil($rowCount['total'] / $limit);
                $range = 3; // Số trang hiển thị trước và sau trang hiện tại

                // Hiển thị trang đầu tiên và các dấu '...'
                if ($page > $range + 1) {
                    echo '<li class="page-item">';
                    echo '<a class="page-link" href="?page=1&limit=' . $limit . '&user=' . urlencode($filter_by_user) . '&contact_status=' . urlencode($filter_by_contact_status) . '&evaluation=' . urlencode($filter_by_evaluation) . '&data_source=' . urlencode($filter_by_data_source) . '&search=' . urlencode($search_query) . '">1</a>';
                    echo '</li>';
                    if ($page > $range + 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                // Hiển thị các trang trong khoảng từ $page - $range đến $page + $range
                for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++) {
                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                    echo '<a class="page-link" href="?page=' . $i . '&limit=' . $limit . '&user=' . urlencode($filter_by_user) . '&contact_status=' . urlencode($filter_by_contact_status) . '&evaluation=' . urlencode($filter_by_evaluation) . '&data_source=' . urlencode($filter_by_data_source) . '&search=' . urlencode($search_query) . '">' . $i . '</a>';
                    echo '</li>';
                }

                // Hiển thị trang cuối cùng và các dấu '...'
                if ($page < $total_pages - $range) {
                    if ($page < $total_pages - $range - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item">';
                    echo '<a class="page-link" href="?page=' . $total_pages . '&limit=' . $limit . '&user=' . urlencode($filter_by_user) . '&contact_status=' . urlencode($filter_by_contact_status) . '&evaluation=' . urlencode($filter_by_evaluation) . '&data_source=' . urlencode($filter_by_data_source) . '&search=' . urlencode($search_query) . '">' . $total_pages . '</a>';
                    echo '</li>';
                }
                ?>
            </ul>
        </nav>

    </div>
</body>

</html>