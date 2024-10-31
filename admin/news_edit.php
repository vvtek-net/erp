<?php

session_start();
include '../db_connection.php';
require 'src/features.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải là admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Kiểm tra xem có ID bài viết hay không
if (!isset($_GET['id'])) {
    echo "Không tìm thấy ID bài viết.";
    exit();
}

$article_id = $_GET['id'];

// Lấy thông tin bài viết từ cơ sở dữ liệu
$sql = "SELECT * FROM news WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    echo "Bài viết không tồn tại.";
    exit();
}

// Cập nhật bài viết nếu có yêu cầu từ form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $summary = $_POST['summary'];
    $content = $_POST['content'];
    $slug = createSlug($title);

    $update_sql = "UPDATE news SET title = ?, summary = ?, content = ?, slug = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $title, $summary, $content, $slug, $article_id);
    $update_stmt->execute();

    echo "Bài viết đã được cập nhật thành công.";
    header("Location: manage_news.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa bài viết</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_accounts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/showdown@1.9.1/dist/showdown.min.js"></script> <!-- Thêm thư viện Showdown -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body>
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h2 class="mt-4 mb-4">Chỉnh sửa bài viết</h2>
        
        <form method="POST" action="">
            <!-- Phần nhập tiêu đề với Markdown -->
            <div class="form-group">
                <label for="title">Tiêu đề</label>
                <input type="text" class="form-control" id="titleInput" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                <small class="form-text text-muted">Bạn có thể sử dụng Markdown để định dạng tiêu đề.</small>
            </div>

            <!-- Phần hiển thị Markdown đã chuyển đổi thành HTML -->
            <div class="form-group">
                <label>Preview Tiêu đề:</label>
                <div id="titlePreview" class="p-2 border" style="background-color: #f9f9f9;"></div>
            </div>

            <div class="form-group">
                <label for="summary">Tóm tắt</label>
                <textarea class="form-control" name="summary" rows="2" required><?php echo htmlspecialchars($article['summary']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="content">Nội dung</label>
                <textarea class="form-control" name="content" id="editor1" rows="10" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                <script>
                    CKEDITOR.replace('editor1');
                </script>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="manage_news.php" class="btn btn-secondary">Hủy</a>
        </form>
    </div>

    <script>
        // Khởi tạo Showdown converter
        const converter = new showdown.Converter();
        
        // Lắng nghe sự kiện khi người dùng nhập vào trường tiêu đề
        document.getElementById('titleInput').addEventListener('input', function() {
            const markdownText = this.value;
            const htmlText = converter.makeHtml(markdownText); // Chuyển đổi Markdown sang HTML
            document.getElementById('titlePreview').innerHTML = htmlText; // Hiển thị HTML đã chuyển đổi
        });
    </script>
</body>

</html>
