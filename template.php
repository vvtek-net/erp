<?php
session_start();
// Kết nối tới MySQL
$conn = new mysqli("localhost", "root", "", "telesale");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Xác định số lượng kết quả trên mỗi trang
$results_per_page = 20;

// Xử lý tìm kiếm
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Điều kiện lọc dựa trên giá website
$filter_condition = '';
if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    switch ($filter) {
        case 'basic':
            $filter_condition = "price < 5000000";
            break;
        case 'standard':
            $filter_condition = "price >= 5000000 AND price < 9000000";
            break;
        case 'premium':
            $filter_condition = "price >= 9000000";
            break;
    }
}

// Lấy trang hiện tại từ URL (nếu không có mặc định là trang 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Tạo câu lệnh SQL với phân trang và điều kiện tìm kiếm
$where_clause = "WHERE 1=1"; // Điều kiện luôn đúng để dễ dàng thêm điều kiện khác
if (!empty($search_query)) {
    $where_clause .= " AND title LIKE '%$search_query%'";
}
if (!empty($filter_condition)) {
    $where_clause .= " AND $filter_condition";
}

$sql = "SELECT * FROM website_templates $where_clause LIMIT $start_from, $results_per_page";
$count_sql = "SELECT COUNT(*) FROM website_templates $where_clause";

// Thực hiện truy vấn để lấy dữ liệu cho trang hiện tại
$result = $conn->query($sql);

// Lấy tổng số kết quả để tính số trang
$total_result = $conn->query($count_sql)->fetch_row()[0];
$total_pages = ceil($total_result / $results_per_page);
?>


<!DOCTYPE html>
<html>

<head>
    <title>Mẫu Giao Diện - VVTEK</title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="assets/css/template.css"> -->

    <style>
        /* Custom styles for the header */
        .navbar-custom {
            background-color: #007bff;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-custom .navbar-brand {
            color: white;
            font-weight: bold;
        }

        .navbar-custom .navbar-brand img {
            max-height: 40px;
        }

        .navbar-custom .navbar-nav .nav-link {
            color: white;
            font-weight: 600;
        }

        .navbar-custom .navbar-nav .nav-link:hover {
            color: #e0e0e0;
        }

        .navbar-custom .form-inline {
            margin-left: auto;
        }

        .navbar-custom .form-inline input {
            width: 200px;
        }

        /* Styles for mobile responsiveness */
        @media (max-width: 768px) {
            .navbar-custom .form-inline input {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* Page styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-container input[type="text"] {
            padding: 10px;
            width: 100%;
            /* Full width trên mobile */
            max-width: 300px;
            font-size: 16px;
        }

        .search-container button {
            padding: 10px;
            font-size: 16px;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
            /* Full width trên mobile */
            max-width: 150px;
        }

        .search-container button:hover {
            background-color: #2980b9;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            /* Linh hoạt số cột dựa trên kích thước màn hình */
            gap: 20px;
            padding: 20px;
        }

        .grid-item {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .grid-item:hover {
            transform: translateY(-10px);
            /* Hiệu ứng nhấc lên khi hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .grid-item img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .grid-item h3 {
            font-size: 1.5em;
            margin: 10px 0;
            position: relative;
            z-index: 1;
        }

        /* Ẩn nút Xem Website mặc định */
        .grid-item a {
            display: none;
            text-decoration: none;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .grid-item a:hover {
            background-color: #2980b9;
        }

        /* Hiển thị nút khi người dùng click vào grid-item */
        .grid-item.clicked h3 {
            display: none;
        }

        .grid-item.clicked a {
            display: inline-block;
        }

        /* Phân trang */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 16px;
            margin: 0 5px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #2980b9;
        }

        .pagination a.active {
            background-color: #2980b9;
        }

        /* Responsive Design cho Mobile */
        @media (max-width: 600px) {
            .grid-container {
                grid-template-columns: 1fr;
                /* Chỉ hiển thị 1 cột trên mobile */
            }

            .search-container input[type="text"],
            .search-container button {
                max-width: 100%;
                /* Đảm bảo các phần tử tìm kiếm chiếm hết chiều rộng trên mobile */
                width: 100%;
            }

            .grid-item img {
                max-height: 150px;
                /* Giảm chiều cao ảnh trên mobile */
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Tìm tất cả các grid-item
            var gridItems = document.querySelectorAll('.grid-item');

            // Lặp qua từng grid-item và thêm sự kiện click
            gridItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    // Thêm class 'clicked' vào grid-item khi được click
                    this.classList.toggle('clicked');
                });
            });
        });
    </script>
</head>

<body>

    <!-- Top Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <a class="navbar-brand" href="dashboard.php"><img src="assets/img/logo.png" alt=""></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"></li>
            </ul>
            <form class="form-inline my-2 my-lg-0" method="GET" action="template.php">
                <input type="text" class="form-control mr-sm-2" name="search" placeholder="Tìm kiếm Website..." value="<?= htmlspecialchars($search_query) ?>">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Tìm kiếm</button>
            </form>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/user.png" alt="User Icon" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <!-- Đưa nút lọc vào đây -->
                            <a href="template.php?filter=basic" class="dropdown-item" >Cơ bản</a>
                            <a href="template.php?filter=standard" class="dropdown-item">Tiêu Chuẩn</a>
                            <a href="template.php?filter=premium" class="dropdown-item">Cao cấp</a>
                        
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng nhập</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <br>
    <br>
    <br>
    <br>

    <!-- Hiển thị các template -->
    <div class="grid-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="grid-item">';
                // Thêm hình ảnh demo dùng chung cho tất cả các ô
                if ($row["image_url"] != null) {
                    echo '<img src="' . $row["image_url"] . '" alt="Demo Website">';
                } else {
                    echo '<img src="assets/img/demo.jpg" alt="Demo Website">';
                }
                echo '<h3>' . $row["title"] . '</h3>';
                // Nút Xem Website (ẩn mặc định, hiện khi click vào grid-item)
                echo '<a href="demo.php?id=' . $row["id"] . '" target="_blank">Xem Website</a>';
                echo '</div>';
            }
        } else {
            echo "<p style='text-align:center;'>Không tìm thấy kết quả nào!</p>";
        }
        ?>
    </div>

    <!-- Phân trang -->
    <div class="pagination">
        <?php
        // Hiển thị trang đầu tiên
        if ($page > 3) {
            echo '<a href="template.php?page=1&search=' . $search_query . '">1</a>';
            if ($page > 4) {
                echo '<a>...</a>'; // Dấu "..." khi có nhiều trang giữa
            }
        }

        // Hiển thị 2-3 trang trước và sau trang hiện tại
        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
            if ($i == $page) {
                echo '<a class="active" href="template.php?page=' . $i . '&search=' . $search_query . '">' . $i . '</a>';
            } else {
                echo '<a href="template.php?page=' . $i . '&search=' . $search_query . '">' . $i . '</a>';
            }
        }

        // Hiển thị trang cuối cùng
        if ($page < $total_pages - 2) {
            if ($page < $total_pages - 3) {
                echo '<a>...</a>'; // Dấu "..." khi có nhiều trang giữa
            }
            echo '<a href="template.php?page=' . $total_pages . '&search=' . $search_query . '">' . $total_pages . '</a>';
        }
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>

</html>