// Lấy trạng thái đăng nhập từ PHP
var loginStatus = "<?php echo $loginStatus; ?>";
var loginPopup = document.getElementById("loginPopup");
var popupMessage = document.getElementById("popupMessage");
var okButton = document.getElementById("okButton");
var retryButton = document.getElementById("retryButton");
var backButton = document.getElementById("backButton");

// Nếu trạng thái đăng nhập không rỗng, hiển thị popup
if (loginStatus !== "") {
    if (loginStatus === "success") {
        popupMessage.innerText = "Đăng nhập thành công!";
        okButton.style.display = "inline-block";
        okButton.onclick = function() {
            window.location.href = "<?php echo $redirectPage; ?>";
        };
    } else {
        popupMessage.innerText = "Đăng nhập thất bại. Vui lòng thử lại.";
        retryButton.style.display = "inline-block";
        backButton.style.display = "inline-block";
        retryButton.onclick = function() {
            window.location.href = "login.php";
        };
        backButton.onclick = function() {
            window.location.href = "index.php";
        };
    }
    loginPopup.style.display = "flex";
}