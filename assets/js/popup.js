// Hiển thị popup nếu có thông báo thành công
var successMessage = "<?php echo $successMessage; ?>";
var isUpdated = "<?php echo $isUpdated; ?>";

if (successMessage !== "" && isUpdated) {
    document.getElementById("successPopup").style.display = "flex";
}

// Điều hướng về trang user_data.php khi nhấn nút OK
document.getElementById("okButton").onclick = function() {
    window.location.href = 'user_data.php';
};

// Ở lại trang hiện tại khi nhấn nút Nhập tiếp
document.getElementById("continueButton").onclick = function() {
    document.getElementById("successPopup").style.display = "none";
};