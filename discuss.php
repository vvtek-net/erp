<?php
session_start();
// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để tham gia thảo luận.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discuss</title>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .chat-box {
            border: 1px solid #ccc;
            padding: 10px;
            height: 400px;
            overflow-y: scroll;
            background-color: #f9f9f9;
        }
        .message {
            margin-bottom: 10px;
        }
        .message .user {
            font-weight: bold;
        }
        .message .time {
            font-size: 0.8em;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Discuss</h2>
        <div class="chat-box" id="chat-box"></div>
        <form id="chat-form" class="mt-3">
            <input type="text" id="message" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off" required>
            <button type="submit" class="btn btn-primary mt-2">Gửi</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var discussionId = <?php echo intval($_GET['discussion_id']); ?>;

        // Kiểm tra xem discussion_id có hợp lệ không
        if (!discussionId) {
            alert("Discussion ID không hợp lệ.");
            throw new Error("Discussion ID không hợp lệ.");
        }

        // Gửi tin nhắn bằng AJAX
        $('#chat-form').on('submit', function(e) {
    e.preventDefault();
    var message = $('#message').val();

    $.ajax({
        url: 'send_message.php',
        type: 'POST',
        data: {
            discussion_id: discussionId,
            message: message
        },
        success: function(response) {
            console.log("Response from server:", response); // Ghi log phản hồi từ server
            if (response === 'success') {
                $('#message').val(''); // Xóa nội dung sau khi gửi
                loadMessages(); // Tải lại tin nhắn
            } else {
                alert('Lỗi khi gửi tin nhắn: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            alert('Có lỗi xảy ra khi gửi tin nhắn.');
        }
    });
});


        // Hàm tải các tin nhắn
        function loadMessages() {
            $.ajax({
                url: 'get_messages.php',
                type: 'GET',
                data: {
                    discussion_id: discussionId
                },
                success: function(data) {
                    console.log("Messages loaded:", data);
                    var chatBox = $('#chat-box');
                    chatBox.empty(); // Xóa nội dung cũ

                    data.forEach(function(msg) {
                        var messageElement = '<div class="message">' +
                            '<span class="user">' + msg.fullname + ':</span> ' +
                            '<span class="text">' + msg.comment + '</span> ' +
                            '<span class="time">(' + msg.created_at + ')</span>' +
                            '</div>';
                        chatBox.append(messageElement);
                    });

                    chatBox.scrollTop(chatBox[0].scrollHeight); // Cuộn xuống cuối
                },
                error: function(xhr, status, error) {
                    console.log("Error loading messages:", error);
                }
            });
        }

        // Gọi hàm loadMessages mỗi 2 giây
        setInterval(loadMessages, 2000);

        // Tải tin nhắn ngay khi trang được tải
        loadMessages();
    </script>
</body>
</html>
