<?php

session_start();
include '../db_connection.php';
require 'src/features.php';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải là admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $summary = $_POST['summary'];
    $thumbnail = '';
    $views = 239;
    $slug = createSlug($title);

    // Kiểm tra và xử lý upload file
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['thumbnail']['tmp_name'];
        $fileName = $_FILES['thumbnail']['name'];
        $fileSize = $_FILES['thumbnail']['size'];
        $fileType = $_FILES['thumbnail']['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Kiểm tra định dạng file hợp lệ
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            // Đặt tên file và đường dẫn upload
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFileDir = '../../truongthanhweb/assets/images/';
            $dest_path = $uploadFileDir . $newFileName;

            // Di chuyển file vào thư mục upload
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $uploadFileDir = 'assets/images/';
                $dest_path = $uploadFileDir . $newFileName;
                $thumbnail = $dest_path;
            } else {
                echo "Đã xảy ra lỗi khi upload file.";
            }
        } else {
            echo "Chỉ chấp nhận các file định dạng png, jpg, jpeg, webp.";
        }
    }

    // Lưu thông tin vào cơ sở dữ liệu
    $sql = "INSERT INTO news (title, content, summary, slug, thumbnail, views, post_time) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $title, $content, $summary, $slug, $thumbnail, $views);
    $stmt->execute();
    if($stmt){
        header('location: manage_news.php');
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/manage_accounts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body>
    <?php include('sidebar.php'); ?>

    <div class="container">
        <h3>Thêm mới tin tức trên website Trường Thành Web</h3>
        <div class="row">
            <div class="col-lg-12">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Nhập tiêu đề" name="title">
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" id="summary" name="summary" rows="2" placeholder="Nhập tóm tắt"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="file" class="form-control" name="thumbnail" accept=".png, .jpg, .jpeg, .webp">
                    </div>
                    <div class="form-group">
                        <textarea name="content" id="editor1" rows="20" cols="80">
                            Nhập nội dung
                        </textarea>
                        <script>
                            CKEDITOR.replace('editor1');
                        </script>
                    </div>
                    <button type="submit" class="btn btn-primary">Đăng</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
