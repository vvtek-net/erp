<?php
// Kết nối tới MySQL
$conn = new mysqli("localhost", "root", "", "telesale");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy URL của website dựa trên ID được truyền từ trang trước
$id = $_GET['id'];
$sql = "SELECT * FROM website_templates WHERE id=$id";
$result = $conn->query($sql);
$url = '';
$title = '';

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $url = $row['url'];
    $title = $row['title'];
} else {
    echo "Website không tìm thấy!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem giao diện - <?php echo $title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        iframe {
            border: none;
            transition: all 0.5s ease; /* Thêm hiệu ứng chuyển động */
        }
        #menu {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: #007bff;
            padding: 10px;
            text-align: center;
            z-index: 1000;
        }
        #menu button {
            background-color: transparent;
            color: white;
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            cursor: pointer;
            font-size: 20px;
        }
        #menu button i {
            color: white;
        }
        #menu button:hover i {
            color: #45a049;
        }
        #iframe-container {
            position: absolute;
            top: 50px;
            bottom: 0;
            width: 100%;
            display: flex;
            justify-content: center; /* Đặt iframe ở giữa */
            align-items: center;
        }
    </style>
    <script>
        // Phát hiện loại thiết bị dựa trên kích thước màn hình
        function detectDevice() {
            const width = window.innerWidth;
            if (width <= 767) {
                return 'phone';
            } else if (width >= 768 && width <= 1024) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }

        // Cập nhật kích thước iframe dựa trên thiết bị
        function setIframeSize(size) {
            const iframe = document.getElementById('preview-frame');
            if (size === 'phone') {
                iframe.style.width = '375px';
                iframe.style.height = '667px';
            } else if (size === 'tablet') {
                iframe.style.width = '768px';
                iframe.style.height = '1024px';
            } else {
                iframe.style.width = '100%';
                iframe.style.height = '100%';
            }
            iframe.style.margin = 'auto'; // Đặt iframe ở giữa
        }

        // Tự động điều chỉnh khi trang được tải
        window.onload = function() {
            const deviceType = detectDevice();
            setIframeSize(deviceType);
        };
    </script>
</head>
<body>
    <div id="menu">
        <button onclick="setIframeSize('phone')" title="Điện thoại"><i class="fas fa-mobile-alt"></i></button>
        <button onclick="setIframeSize('tablet')" title="Máy tính bảng"><i class="fas fa-tablet-alt"></i></button>
        <button onclick="setIframeSize('desktop')" title="Desktop"><i class="fas fa-desktop"></i></button>
        <span style="color: white; margin-left: 20px;">Mã giao diện: <?php echo $id; ?></span>
    </div>

    <div id="iframe-container">
        <?php if ($url): ?>
            <iframe id="preview-frame" src="<?= $url ?>"></iframe>
        <?php endif; ?>
    </div>
</body>
</html>
