<?php

header('Content-Type: text/html; charset=UTF-8');

include '../db_connection.php';
require '../function.php';

$contract_id = isset($_GET['contract_id']) ? $_GET['contract_id'] : '';

$queryContract = "SELECT *, services.price as service_price, addons.price as addon_price FROM contracts 
        LEFT JOIN sale_orders ON contracts.sale_order_id = sale_orders.id
        LEFT JOIN services ON services.service_id = sale_orders.service_type
        LEFT JOIN addons ON addons.addon_id = sale_orders.addon_type
        LEFT JOIN customers ON customers.id = sale_orders.customer_id
        WHERE contract_id = $contract_id";
$stmt = $conn->prepare($queryContract);
$stmt->execute();
$contract = $stmt->get_result()->fetch_assoc();

// get a account
$emp = getAccount($conn, $contract['account_id']);

// get a customer
// $contract = getCustomer($conn, $contract['customer_id']);

// format date
$date = new DateTime($contract['created_at']);
        $formatted_date = $date->format('d');
        $formatted_mth = $date->format('m');
        $formatted_yr = $date->format('Y');

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="vi" xml:lang="">
  <head>
    <title>Page 1</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/hopdong.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>

    <style>
      .d-none {
        display: none;
      }
      .col-6 {
        position: absolute;
        width: 50%;
        min-height: 1px;
      }
    </style>

  </head>
  <body bgcolor="#525659" vlink="blue" link="blue">
    <!-- <button onclick="printContract()" style="position: fixed; top: 20px; right: 20px; padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Print Contract</button> -->
   <button class="" onclick="exportToPDF()" style="position: fixed; top: 20px; right: 20px; padding: 10px; background-color: #2196F3; color: white; border: none; cursor: pointer; z-index: 9999; box-shadow: rgba(9, 30, 66, 0.25) 0px 4px 8px -2px, rgba(9, 30, 66, 0.08) 0px 0px 0px 1px;">
    Xuất PDF
  </button>

    <!-- page 1 -->
    <div id="contract-content">
      <div
        id="page1-div"
        style="
          position: relative;
          width: 892px;
          height: 1263px;
          background-color: #fff;
          margin: auto;
        "
        class="bsd"
      >
        <img
          width="892"
          height="1263"
          src="../assets/img/File_mau001.png"
          alt="background image"
        />
        <!-- Tên nhân viên -->
        <p
          style="position: absolute; top: 1205px; left: 70px; white-space: nowrap"
          class="ft01"
        >
          <i>Nhân viên thực hiện hợp đồng: <?php echo $emp['fullname'] ?></i>
        </p>
        <!-- Địa chỉ -->
        <p
          style="
            position: absolute;
            top: 1205px;
            left: 820px;
            white-space: nowrap;
          "
          class="ft04"
        >
          1 / 3
        </p>
        <p
          style="position: absolute; top: 1225px; left: 70px; white-space: nowrap"
          class="ft01"
        >
          <i>
            Địa chỉ nhận hợp đồng: 16 ngõ 42 phố Trung Hòa, Cầu Giấy, Hà Nội ,
            Việt Nam
          </i>
        </p>
        <p
          style="position: absolute; top: 45px; left: 562px; white-space: nowrap"
          class="ft06"
        >
          <b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b>
        </p>
        <p
          style="position: absolute; top: 46px; left: 796px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 69px; left: 630px; white-space: nowrap"
          class="ft07"
        >
          <b>Độc lập - Tự do - Hạnh phúc</b>
        </p>
        <p
          style="position: absolute; top: 122px; left: 311px; white-space: nowrap"
          class="ft09"
        >
          <b>HỢP ĐỒNG CUNG CẤP DỊCH VỤ</b>
        </p>
        <p
          style="position: absolute; top: 152px; left: 600px; white-space: nowrap"
          class="ft010"
        >
          SỐ /<?php echo $formatted_yr ?>-TTWS
        </p>
        <!-- ! Từ đây -->
        <p
          style="position: absolute; top: 211px; left: 51px; white-space: nowrap"
          class="ft012"
        >
          <i>- Căn cứ quy định tại Bộ luật dân sự và Luật Thương mại.</i>
        </p>
        <br />
        <!-- Từ đây -->
        <p
          style="position: absolute; top: 230px; left: 51px; white-space: nowrap"
          class="ft012"
        >
          <i
            >- Căn cứ quy định về quản lý, cung cấp và sử dụng tài nguyên Internet
            của Bộ Thông Tin và Truyền Thông.</i
          >
        </p>
        <br />
        <!-- Từ đây -->
        <p
          style="position: absolute; top: 249px; left: 51px; white-space: nowrap"
          class="ft012"
        >
          <i>- Căn cứ nhu cầu, khả năng và thỏa thuận của hai bên.</i>
        </p>
        <br />
        <!-- Kết thúc -->
        <p
          style="position: absolute; top: 275px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          Hợp đồng này được lập tại Hà Nội, 
          <?php echo 'ngày '.$formatted_date.' tháng '.$formatted_mth. ' năm '. $formatted_yr; ?>, giữa các
          bên sau đây:
        </p>
        <!-- Bên A -->
        <h2
          style="position: absolute; top: 300px; left: 50px; white-space: nowrap"
          class="ft07"
        >
          BÊN A: <?php echo $contract['customer_name'] ?>
        </h2>
        <!-- Địa chỉ -->
        <p
          style="position: absolute; top: 331px; left: 68px; white-space: nowrap"
          class="ft010"
        >
          Địa chỉ: <span><?php echo $contract['address'] ?></span>
        </p>
        <!-- individual -->
        <!-- Điện thoại -->
        <div class="<?php if ($contract['customer_type'] !== 'individual') echo 'd-none' ?>" style="position: absolute; top: 350px; left: 68px; white-space: nowrap; line-height: 1.6">
          <div class="col-6">
            <p
              class="ft010"
            >
              Điện thoại: <span><?php echo $contract['phone_number'] ?></span>
            </p>
            <!-- Email -->
            <p
              class="ft010"
            >
              Email: <span><?php echo $contract['email'] ?></span>
            </p>
          </div>
          <div class="col-6" style="left: 500px">
            <p
              class="ft010"
            >
              Số CMND/CCCD: <span><?php echo $contract['identity_person'] ?></span>
            </p>
            <!-- Ngày sinh -->
            <p
              class="ft010"
            >
              Ngày sinh: <span><?php echo $contract['birthday'] ?></span>
            </p>
          </div>
        </div>
        <!-- business -->
        <div class="<?php if ($contract['customer_type'] !== 'business') echo 'd-none' ?>" style="position: absolute; top: 350px; left: 68px; white-space: nowrap; line-height: 1.6">
          <div class="col-6">
            <p
              class="ft010"
            >
              Người đại diện: <span><?php echo $contract['agent_name'] ?></span>
            </p>
            <p
              class="ft010"
            >
              Chức vụ: <span><?php echo $contract['agent_position'] ?></span>
            </p>
            <!-- Email -->
            <p
              class="ft010"
            >
              Email: <span><?php echo $contract['email'] ?></span>
            </p>
          </div>
          <div class="col-6" style="left: 500px">
            <p
              class="ft010"
            >
              Mã số thuế: <span><?php echo $contract['tax'] ?></span>
            </p>
            <p
              class="ft010"
            >
              Ngày thành lập: <span><?php echo $contract['founding_date'] ?></span>
            </p>
            <p
              class="ft010"
            >
              Hotline: <span><?php echo $contract['phone_number'] ?></span>
            </p>
          </div>
          
        </div>
        <!-- Bên B -->
        <h2
          style="position: absolute; top: 413px; left: 50px; white-space: nowrap"
          class="ft07"
        >
          BÊN B: Công Ty TNHH Kinh Doanh Tổng Hợp Và Xuất Nhập Khẩu Trường Thành –
          Khối Công Nghệ Thông Tin
        </h2>
        <!-- Địa chỉ -->
        <p
          style="position: absolute; top: 444px; left: 68px; white-space: nowrap"
          class="ft010"
        >
          Địa chỉ:
        </p>
        <p
          style="position: absolute; top: 444px; left: 200px; white-space: nowrap"
          class="ft010"
        >
          Số 16 ngõ 42 Trung Hòa, Cầu Giấy, Hà Nội
        </p>
        <!-- Đại diện -->
        <p
          style="position: absolute; top: 468px; left: 68px; white-space: nowrap"
          class="ft010"
        >
          Đại diện bởi:
        </p>
        <p
          style="position: absolute; top: 468px; left: 200px; white-space: nowrap"
          class="ft05"
        >
          Nguyễn Tuấn Anh
        </p>
        <p
          style="position: absolute; top: 468px; left: 565px; white-space: nowrap"
          class="ft010"
        >
          Chức vụ: Giám đốc điều hành
        </p>
        <!-- Điện thoại -->
        <p
          style="position: absolute; top: 492px; left: 68px; white-space: nowrap"
          class="ft010"
        >
          Điện thoại:
        </p>
        <p
          style="position: absolute; top: 492px; left: 200px; white-space: nowrap"
          class="ft010"
        >
          0328501599
        </p>
        <!-- tài khoản số -->
        <p
          style="position: absolute; top: 516px; left: 68px; white-space: nowrap"
          class="ft010"
        >
          Tài khoản số:
        </p>
        <p
          style="position: absolute; top: 516px; left: 200px; white-space: nowrap"
          class="ft010"
        >
          20328501599
        </p>
        <p
          style="position: absolute; top: 516px; left: 565px; white-space: nowrap"
          class="ft010"
        >
          Mở tại: Tienphong Bank - Chi nhánh Hà Nội
        </p>
        <!-- Mã số thuế -->
        <p
          style="position: absolute; top: 540px; left: 68px; white-space: nowrap"
          class="ft010"
        >
          Mã số thuế:
        </p>
        <p
          style="position: absolute; top: 540px; left: 200px; white-space: nowrap"
          class="ft010"
        >
          0110681400
        </p>
        <p
          style="position: absolute; top: 581px; left: 50px; white-space: nowrap"
          class="ft012"
        >
          (Bên A và Bên B được gọi chung là " <span><b>Các Bên</b></span> ")
        </p>
        <p
          style="position: absolute; top: 606px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          Sau khi thảo luận, hai bên đồng ý ký kết Hợp đồng cho thuê và sử dụng
          dịch vụ (sau đây gọi là “Hợp Đồng”) với các điều kiện và điều khoản sau:
        </p>
        <p
          style="position: absolute; top: 641px; left: 50px; white-space: nowrap"
          class="ft07"
        >
          <b>ĐIỀU 1: NỘI DUNG HỢP ĐỒNG</b>
        </p>
        <p
          style="position: absolute; top: 674px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          1.1. Bên B đồng ý cung cấp và Bên A đồng ý sử dụng các dịch vụ theo điều
          3.1 và Phụ Lục đính kèm Hợp Đồng này (nếu có phát sinh).
        </p>
        <p
          style="position: absolute; top: 674px; left: 735px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 700px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          1.2. Bên B chỉ cung cấp dịch vụ cho Bên A khi Bên A thanh toán các khoản
          phí đúng quy định tại điều 3.1.
        </p>
        <p
          style="position: absolute; top: 700px; left: 591px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 725px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          1.3. Thời hạn Hợp đồng: dựa vào ngày thanh toán theo các mốc thời gian
          theo điều 2 và sẽ tự động gia hạn cho các chu kỳ tiếp theo tương ứng với
          số tiền
        </p>
        <p
          style="position: absolute; top: 747px; left: 51px; white-space: nowrap"
          class="ft010"
        >
          thanh toán nếu 1 trong 2 Bên không có yêu cầu chấm dứt hợp đồng bằng văn
          bản trước 30 ngày khi Hợp đồng hết hạn hoặc dịch vụ hết hạn sử dụng.
        </p>
        <p
          style="position: absolute; top: 770px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          1.4. Gia hạn hợp đồng: Bằng việc thanh toán đúng hạn và đầy đủ theo quy
          định của Hợp Đồng này căn cứ trên chứng từ thanh toán ngân hàng của Bên
          A cho
        </p>
        <p
          style="position: absolute; top: 792px; left: 51px; white-space: nowrap"
          class="ft010"
        >
          Bên B hoặc căn cứ hóa đơn tài chính Bên B xuất cho Bên A, Bên A được xem
          như đồng ý gia hạn Hợp đồng này với thời gian gia hạn tương ứng với số
          tiền
        </p>
        <p
          style="position: absolute; top: 814px; left: 51px; white-space: nowrap"
          class="ft010"
        >
          thanh toán.
        </p>
        <p
          style="position: absolute; top: 837px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          1.5. Hết hạn hợp đồng: Khi dịch vụ hết hạn sử dụng mà Bên A không tiếp
          tục gia hạn và đóng cước thì xem như hợp đồng chấm dứt và được tự động
          thanh lý.
        </p>
        <p
          style="position: absolute; top: 871px; left: 50px; white-space: nowrap"
          class="ft07"
        >
          <b>ĐIỀU 2: THANH TOÁN VÀ HOÀN TIỀN</b>
        </p>
        <p
          style="position: absolute; top: 871px; left: 244px; white-space: nowrap"
          class="ft08"
        >
          <b> </b>
        </p>
        <p
          style="position: absolute; top: 907px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          2.1. Tối thiểu 15 ngày trước khi dịch vụ hết hạn, Bên B gửi thông báo
          yêu cầu thanh toán cước phí qua địa chi email Bên A đăng ký tai Hợp đồng
          hoặc trực tiếp
        </p>
        <p
          style="position: absolute; top: 932px; left: 51px; white-space: nowrap"
          class="ft010"
        >
          tới địa chỉ Bên A. Bên A có trách nhiệm thanh toán cước phí cho Bên B
          trước 07 (bảy) ngày khi dịch vụ hết hạn.
        </p>
        <p
          style="position: absolute; top: 932px; left: 622px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 956px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          2.2. Quá thời hạn Bên A vẫn chưa thanh toán hoặc thanh toán không đủ
          cước phí, Bên B có quyền tạm ngừng cung cấp dịch vụ. Bên B sẽ bảo lưu
          quyền được
        </p>
        <p
          style="position: absolute; top: 978px; left: 51px; white-space: nowrap"
          class="ft010"
        >
          thanh toán khoản nợ chưa trả và Bên A sẽ phải chịu thêm khoản lãi suất
          trả chậm là 1,5% / tháng cộng dồn theo ngày cho đến khi hết nợ.
        </p>
        <p
          style="position: absolute; top: 978px; left: 760px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 1001px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          2.3. Phương thức thanh toán:
        </p>
        <p
          style="
            position: absolute;
            top: 1001px;
            left: 204px;
            white-space: nowrap;
          "
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 1025px; left: 71px; white-space: nowrap"
          class="ft015"
        >
          <input type="checkbox" name="" id="" />
        </p>
        <p
          style="position: absolute; top: 1027px; left: 93px; white-space: nowrap"
          class="ft010"
        >
          Trực tiếp tại địa chỉ Bên B
        </p>
        <p
          style="
            position: absolute;
            top: 1027px;
            left: 219px;
            white-space: nowrap;
          "
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 1049px; left: 71px; white-space: nowrap"
          class="ft015"
        >
          <input type="checkbox" name="" id="" checked />
        </p>
        <p
          style="position: absolute; top: 1051px; left: 93px; white-space: nowrap"
          class="ft010"
        >
          Chuyển khoản
        </p>
        <p
          style="
            position: absolute;
            top: 1051px;
            left: 159px;
            white-space: nowrap;
          "
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 1075px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          2.4. Bên B sẽ cung cấp cho Bên A hoá đơn tài chính hợp lệ sau khi Bên A
          hoàn tất các thủ tục thanh toán.
        </p>
        <p
          style="
            position: absolute;
            top: 1075px;
            left: 591px;
            white-space: nowrap;
          "
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 1100px; left: 50px; white-space: nowrap"
          class="ft010"
        >
          2.5. Mọi sự nhầm lẫn trên giấy báo cước hoặc hoá đơn (nếu có) sẽ được
          hai bên kiểm tra, xác nhận và điều chỉnh ngay trong kỳ thanh toán đó.
        </p>
        
        <!-- 000503 -->
        <p
          style="position: absolute; top: 148px; left: 706px; white-space: nowrap; letter-spacing: 4px;"
          class="ft014"
        >
          <?php echo sprintf('%06d', $contract['contract_id']); ?>
        </p>
        <p
          style="position: absolute; top: 151px; left: 776px; white-space: nowrap"
          class="ft05"
        ></p>
      </div>

      <!-- page 2 -->
      <div
        id="page2-div"
        style="
          position: relative;
          width: 892px;
          height: 1263px;
          background-color: #fff;
          margin: auto;
          margin-top: 20px;
        "
        class="bsd"
      >
        <img
          width="892"
          height="1263"
          src="../assets/img/File_mau002.png"
          alt="background image"
        />
        <p
          style="position: absolute; top: 1205px; left: 70px; white-space: nowrap"
          class="ft01"
        >
          <i>Nhân viên thực hiện hợp đồng: <?php echo $emp['fullname'] ?></i>
        </p>
        <p
          style="
            position: absolute;
            top: 1205px;
            left: 820px;
            white-space: nowrap;
          "
          class="ft04"
        >
          2 / 3
        </p>
        <!-- Địa chỉ -->
        <p
          style="position: absolute; top: 1225px; left: 70px; white-space: nowrap"
          class="ft01"
        >
          <i
            >Địa chỉ nhận hợp đồng: 16 ngõ 42 phố Trung Hòa, Cầu Giấy, Hà Nội ,
            Việt Nam</i
          >
        </p>
        <p
          style="position: absolute; top: 55px; left: 50px; white-space: nowrap"
          class="ft07"
        >
          <b>ĐIỀU 3: CHI PHÍ DỊCH VỤ</b>
        </p>
        <p
          style="position: absolute; top: 77px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          <b>3.1.</b> Chi phí dịch vụ:
        </p>
        <p
          style="position: absolute; top: 107px; left: 61px; white-space: nowrap"
          class="ft07"
        >
          <b>STT</b>
        </p>
        <p
          style="position: absolute; top: 107px; left: 80px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 107px; left: 165px; white-space: nowrap"
          class="ft07"
        >
          <b>Khoản mục</b>
        </p>
        <p
          style="position: absolute; top: 107px; left: 224px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 107px; left: 362px; white-space: nowrap"
          class="ft06"
        >
          <b>Dịch Vụ</b>
        </p>
        <p
          style="position: absolute; top: 107px; left: 431px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 107px; left: 535px; white-space: nowrap"
          class="ft07"
        >
          <b>Thời hạn</b>
        </p>
        <p
          style="position: absolute; top: 107px; left: 583px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 107px; left: 646px; white-space: nowrap"
          class="ft07"
        >
          <b>Giảm</b>
        </p>
        <p
          style="position: absolute; top: 107px; left: 674px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 107px; left: 736px; white-space: nowrap"
          class="ft07"
        >
          <b>Thành tiền(VND)</b>
        </p>
        <p
          style="position: absolute; top: 107px; left: 825px; white-space: nowrap"
          class="ft05"
        ></p>
        <p
          style="position: absolute; top: 141px; left: 70px; white-space: nowrap"
          class="ft06"
        >
          1
        </p>
        <p
          style="position: absolute; top: 141px; left: 96px; white-space: nowrap"
          class="ft06"
        >
          Theo yêu cầu
        </p>
        <p
          style="position: absolute; top: 141px; left: 359px; white-space: nowrap"
          class="ft06"
        >
          <?php echo $contract['service_name'] ?>
        </p>
        <p
          style="position: absolute; top: 141px; left: 530px; white-space: nowrap"
          class="ft06"
        >
          <?php echo $contract['service_duration'] ?> Tháng
        </p>
        <p
          style="position: absolute; top: 141px; left: 692px; white-space: nowrap"
          class="ft06"
        >
          0
        </p>
        <p
          style="position: absolute; top: 141px; right: 35px; white-space: nowrap"
          class="ft06"
        >
          <?php echo number_format($contract['service_price'], 0, '', ',') ?>
        </p>
        <p
          style="position: absolute; top: 184px; left: 70px; white-space: nowrap"
          class="ft06"
        >
          2
        </p>
        <p
          style="position: absolute; top: 184px; left: 96px; white-space: nowrap"
          class="ft06"
        >
          Thiết kế chức năng
        </p>
        <p
          style="position: absolute; top: 184px; left: 302px; white-space: nowrap"
          class="ft06"
        >
          <?php echo $contract['addon_name'] ?>
        </p>
        <p
          style="position: absolute; top: 184px; left: 541px; white-space: nowrap"
          class="ft06"
        >
          1 Lần
        </p>
        <p
          style="position: absolute; top: 184px; left: 692px; white-space: nowrap"
          class="ft06"
        >
          0
        </p>
        <p
          style="position: absolute; top: 184px; right: 35px; white-space: nowrap"
          class="ft06"
        >
          <?php echo number_format($contract['addon_price'], 0, '', ',') ?>
        </p>
        <p
          style="position: absolute; top: 220px; left: 666px; white-space: nowrap"
          class="ft06"
        >
          Cộng
        </p>
        <p
          style="position: absolute; top: 220px; right: 35px; white-space: nowrap"
          class="ft06"
        >
          <?php echo number_format($contract['service_price'] + $contract['addon_price'], 0, '', ',') ?>
        </p>
        <p
          style="position: absolute; top: 258px; left: 666px; white-space: nowrap"
          class="ft06"
        >
          Giảm
        </p>
        <p
          style="position: absolute; top: 258px; right: 35px; white-space: nowrap"
          class="ft06"
        >
          0
        </p>
        <p
          style="position: absolute; top: 296px; left: 670px; white-space: nowrap"
          class="ft06"
        >
          Còn
        </p>
        <p
          style="position: absolute; top: 296px; right: 35px; white-space: nowrap"
          class="ft06"
        >
          <?php echo number_format($contract['service_price'] + $contract['addon_price'], 0, '', ',') ?>
        </p>
        <p
          style="position: absolute; top: 327px; left: 635px; white-space: nowrap"
          class="ft07"
        >
          <b>Thanh toán</b>
        </p>
        <p
          style="position: absolute; top: 327px; right: 35px; white-space: nowrap"
          class="ft07"
        >
          <b><?php echo number_format($contract['service_price'] + $contract['addon_price'], 0, '', ',') ?>VND</b>
        </p>
        <p
          style="position: absolute; top: 355px; right: 32px; white-space: nowrap"
          class="ft06"
        >
          ( Bằng chữ: <?php echo ucfirst(convert_number_to_words($contract['service_price'] + $contract['addon_price'])) . ' VND' ?> )
        </p>
        <p
          style="position: absolute; top: 376px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          <b>3.2. Quy trình thanh toán :</b>
        </p>
        <p
          style="position: absolute; top: 399px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          Bên A có nghĩa vụ thực hiện thanh toán trước 50% tổng giá trị hợp đồng (đặt cọc) cho bên B ngay sau khi hợp đồng được ký kết.
        </p>
        <p
          style="position: absolute; top: 423px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          Bên B có nghĩa vụ thiết kế Website theo đúng yêu cầu của bên A theo đúng
          thời gian hai bên đã thỏa thuận. 
        </p>
        <p
          style="position: absolute; top: 447px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          Sau khi thiết kế, bên B có trách nhiệm demo sản phẩm để bên A nghiệm
          thu.
        </p>
        <p
          style="position: absolute; top: 469px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          Sau khi nghiệm thu, bên A có nghĩa vụ thanh toán nốt 50% còn lại của giá
          trị hợp đồng
        </p>
        <p
          style="position: absolute; top: 493px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          <b>3.3.</b> Đối với trường hợp gia hạn, nếu có sự thay đổi giá so với
          giá trước đó đã ký trong hợp đồng đăng ký mới, bên B có trách nhiệm gửi
          thông
        </p>
        <p
          style="position: absolute; top: 514px; left: 51px; white-space: nowrap"
          class="ft06"
        >
          báo cho Bên A trước 15 (mười lăm) ngày trước khi dịch vụ hết hạn.
        </p>
        <p
          style="position: absolute; top: 538px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          <b>3.4.</b> Thời điểm tính thời gian sử dụng của gói dịch vụ được tính
          từ ngày Bên B cung cấp dịch vụ cho Bên A căn cứ vào ngày hoàn tất khởi
          tạo
        </p>
        <p
          style="position: absolute; top: 560px; left: 51px; white-space: nowrap"
          class="ft06"
        >
          dịch vụ, ngày kích hoạt trên hệ thống.
        </p>
        <p
          style="position: absolute; top: 587px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          <b>3.5.</b> Đối với dịch vụ tên miền: Tổng tiền sẽ bao gồm lệ phí đăng
          ký (nếu là đăng ký mới), phí duy trì và dịch vụ tài khoản quản trị tên
          miền của
        </p>
        <p
          style="position: absolute; top: 609px; left: 51px; white-space: nowrap"
          class="ft06"
        >
          Trường Thành Web.
        </p>
        <p
          style="position: absolute; top: 643px; left: 50px; white-space: nowrap"
          class="ft07"
        >
          <b>ĐIỀU 4: QUYỀN VÀ NGHĨA VỤ CỦA BÊN A</b>
        </p>
        <p
          style="
            position: absolute;
            top: 665px;
            left: 61px;
            white-space: nowrap;
            line-height: 1.6;
          "
          class="ft07"
        >
          <b>4.1 Quyền của Bên A</b>
        </p>
        <div
          style="
            position: absolute;
            top: 689px;
            left: 90px;
            white-space: nowrap;
            line-height: 1.6;
          "
        >
          <p class="ft06">
            4.1.1 Yêu cầu Bên B thực hiện đúng và đầy đủ nội dung, nghĩa vụ theo
            quy định tại Hợp Đồng này.
          </p>
          <p class="ft06">
            4.1.2 Có quyền khiếu nại về cước và chất lượng dịch vụ.
          </p>
          <p class="ft06">
            4.1.3 Được thông báo, giải thích kịp thời về sự thay đổi điều kiện sử
            dụng dịch vụ trong các trường hợp bất khả kháng bằng các hình thức
            <br />
            phù hợp.
          </p>
        </div>
        <p
          style="position: absolute; top: 790px; left: 61px; white-space: nowrap"
          class="ft07"
        >
          <b>4.2 Nghĩa vụ của Bên A</b>
        </p>
        <div
          style="
            position: absolute;
            top: 814px;
            left: 90px;
            white-space: nowrap;
            line-height: 1.6;
          "
        >
          <p class="ft06">
            4.2.1 Bên A chấp nhận và tuân thủ các điều khoản thoả thuận sử dụng
            dịch vụ và xem đây là một phần không thể tách rời của Hợp đồng.
          </p>
          <p class="ft06">
            4.2.2 Chịu trách nhiệm hoàn toàn về cách thức sử dụng, về nội dung
            thông tin lưu trữ trên dịch vụ, hoặc các thông tin do Bên A tự cài
            <br />
            đặt trên máy chủ dịch vụ đặt tại địa điểm thực hiện dịch vụ của Bên B,
            đảm bảo các thông tin này không chứa các phần mềm phá hoại <br />
            và không trái với đạo đức xã hội, quy định của pháp luật.
          </p>
          <p class="ft06">
            4.2.3 Thanh toán các khoản phí theo quy định tại Hợp Đồng này đúng hạn
            và đầy đủ.
          </p>
          <p class="ft06">
            4.2.4 Có trách nhiệm thông báo cho Bên B tất cả những thay đổi về tên
            giao dịch, địa chỉ giao dịch, địa chỉ nhận hóa đơn thanh toán, <br />
            tên tài khoản, tên người phụ trách kỹ thuật và việc ngừng sử dụng dịch
            vụ trước 30 ngày kể từ ngày có sự thay đổi các thông tin trên.
          </p>
          <p class="ft06">
            4.2.5 Chịu trách nhiệm bảo mật tên truy cập và mật khẩu của tài khoản
            người dùng Bên A quản lý.
          </p>
        </div>
        <p
          style="position: absolute; top: 1005px; left: 50px; white-space: nowrap"
          class="ft07"
        >
          <b>ĐIỀU 5: QUYỀN VÀ NGHĨA VỤ CỦA BÊN B</b>
        </p>
        <p
          style="position: absolute; top: 1032px; left: 61px; white-space: nowrap"
          class="ft07"
        >
          <b>5.1 Quyền của Bên B</b>
        </p>
        <div
          style="
            position: absolute;
            top: 1058px;
            left: 90px;
            white-space: nowrap;
            line-height: 1.6;
          "
        >
          <p class="ft06">
            5.1.1 Yêu cầu Bên A thực hiện đúng và đầy đủ nội dung, nghĩa vụ theo
            Hợp Đồng.
          </p>
          <p class="ft06">
            5.1.2 Được Bên A thanh toán các khoản chi phí được nêu trong Hợp Đồng
            một cách đầy đủ và đúng hạn.
          </p>
          <p class="ft06">
            5.1.3 Tạm ngưng cung cấp dịch vụ hoặc đơn phương chấm dứt cung cấp
            dịch vụ theo ý chí của Bên B trong trường hợp Bên A không <br />
            tuân thủ đúng quy định về việc thanh toán phí cho Bên B.
          </p>
        </div>
      </div>

      <!-- page 3 -->
      <div
        id="page3-div"
        style="
          position: relative;
          width: 892px;
          height: 1263px;
          background-color: #fff;
          margin: auto;
          margin-top: 20px;
        "
        class="bsd"
      >
        <img
          width="892"
          height="1263"
          src="../assets/img/File_mau003.png"
          alt="background image"
        />
        <p
          style="position: absolute; top: 1205px; left: 70px; white-space: nowrap"
          class="ft01"
        >
          <i>Nhân viên thực hiện hợp đồng: <?php echo $emp['fullname'] ?></i>
        </p>
        <p
          style="
            position: absolute;
            top: 1205px;
            left: 820px;
            white-space: nowrap;
          "
          class="ft04"
        >
          3 / 3
        </p>
        <p
          style="position: absolute; top: 1225px; left: 70px; white-space: nowrap"
          class="ft01"
        >
          <i
            >Địa chỉ nhận hợp đồng: 16 ngõ 42 phố Trung Hòa, Cầu Giấy, Hà Nội ,
            Việt Nam</i
          >
        </p>
        <p
          style="position: absolute; top: 55px; left: 61px; white-space: nowrap"
          class="ft06"
        >
          <b>5.2 Nghĩa vụ của Bên B</b>
        </p>
        <div style="position: absolute; top: 81px; left: 90px; white-space: nowrap; line-height: 1.6">
          <p
            class="ft08"
          >
            5.2.1 Đảm bảo cung cấp dịch vụ đúng nội dung trong hợp đồng.
          </p>
          <p
            class="ft08"
          >
            5.2.2 Đảm bảo dịch vụ hoạt động ổn định liên tục.
          </p>
          <p
            class="ft08"
          >
            5.2.3 Có trách nhiệm cung cấp tài liệu hướng dẫn sử dụng, đào tạo và
            hướng dẫn Bên A sử dụng dịch vụ.
          </p>
          <p
            class="ft08"
          >
            5.2.4 Hoàn tất việc cấu hình / cài đặt dịch vụ trước thời gian bắt đầu
            tính phí duy trì.
          </p>
          <p
            class="ft08"
          >
            5.2.5 Đảm bảo các vấn đề về cấu hình, băng thông, đường truyền, dự phòng
            theo quy định tại Hợp Đồng này.
          </p>
          <p
            class="ft08"
          >
            5.2.6 Tuân thủ các yêu cầu của cơ quan nhà nước trong trường hợp các cơ
            quan này muốn kiểm tra, tác động đối với máy chủ / dịch vụ của <br />
            Bên A đặt tại địa điểm của Bên B và Bên B được miễn trừ các trách nhiệm
            trong trường hợp này.
          </p>
        </div>
        <p
          style="position: absolute; top: 254px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          <b>ĐIỀU 6: THÔNG BÁO</b>
        </p>
        <p
          style="position: absolute; top: 287px; left: 50px; white-space: nowrap"
          class="ft08"
        >
          Mọi vấn đề liên quan đến kỹ thuật, nếu cần thiết, Bên B sẽ thông báo đến
          Bên A bằng điện thoại, email theo các thông tin sau:
        </p>
        <p
          style="position: absolute; top: 304px; left: 51px; white-space: nowrap"
          class="ft06"
        >
          <b>Thông tin 1</b>
        </p>
        <p
          style="position: absolute; top: 304px; left: 457px; white-space: nowrap"
          class="ft06"
        >
          <b>Thông tin 2</b>
        </p>
        <!-- Thông tin 1 -->
         <!-- individual -->
        <div class="<?php if ($contract['customer_type'] !== 'individual') echo 'd-none' ?>" style="position: absolute; top: 328px; left: 61px; white-space: nowrap; line-height: 1.6">
          <div class="col-6">
            <p
              class="ft08"
            >
              Họ tên: <?php echo $contract['customer_name'] ?>
            </p>
            <p
              class="ft08"
            >
              Số Điện thoại: <span id="phone_1"><?php echo $contract['phone_number'] ?></span>
            </p>
            <p
              class="ft08"
            >
              Email: <span><?php echo $contract['email'] ?></span>
            </p>
          </div>
          <div class="col-6" style="left: 400px">
              <p
                class="ft08"
              >
                Họ tên: <span id="name"> <?php echo $contract['customer_name'] ?>
                        </span>
              </p>
              <p
                class="ft08"
              >
                Số điện thoại: <?php echo $contract['phone_number'] ?>
              </p>
              <p
                class="ft08"
              >
                Email: <span><?php echo $contract['email'] ?></span>
              </p>
          </div>
        </div>
        <!-- business -->
        <div class="<?php if ($contract['customer_type'] !== 'business') echo 'd-none' ?>" style="position: absolute; top: 328px; left: 61px; white-space: nowrap; line-height: 1.6">
            <div class="col-6">
              <p
                class="ft08"
              >
                Người đại diện: <?php echo $contract['agent_name'] ?>
              </p>
              <p
                class="ft08"
              >
                Số Điện thoại: <span id="phone_1"><?php echo $contract['phone_number'] ?></span>
              </p>
              <p
                class="ft08"
              >
                Email: <span><?php echo $contract['email'] ?></span>
              </p>
            </div>
            <div class="col-6" style="left: 400px">
                <p
                  class="ft08"
                >
                  Người đại diện: <span id="name"> <?php echo $contract['agent_name'] ?>
                          </span>
                </p>
                <p
                  class="ft08"
                >
                  Số điện thoại: <?php echo $contract['phone_number'] ?>
                </p>
                <p
                  class="ft08"
                >
                  Email: <span><?php echo $contract['email'] ?></span>
                </p>
            </div>
        </div>
        
        <p
          style="position: absolute; top: 400px; left: 50px; white-space: nowrap"
          class="ft06"
        >
          <b>ĐIỀU 7: ĐIỀU KHOẢN CHUNG</b>
        </p>
        <div
          style="
            position: absolute;
            top: 421px;
            left: 50px;
            white-space: nowrap;
            line-height: 1.6;
          "
        >
          <p class="ft08">
            7.1. Mọi sửa đổi, bổ sung đối với Hợp Đồng /Phụ Lục phải được thực
            hiện bằng văn bản do đại diện có thẩm quyền của mỗi Bên ký tên và đóng
            dấu.
          </p>
          <p class="ft08">
            7.2. Việc một bên không thực hiện quyền của mình đối với Bên kia trong
            Hợp đồng / Phụ Lục không có nghĩa là Bên có quyền đã từ bỏ các quyền
            khác <br />
            trong hợp đồng.
          </p>

          <p class="ft08">
            7.3. Trường hợp một phần của Hợp Đồng / Phụ Lục bị vô hiệu bởi quyết
            định/bản án của tòa án hoặc bị các Bên hủy bỏ thì phần còn lại vẫn có
            hiệu lực <br />
            thi hành.
          </p>
          <p class="ft08">
            7.4. Hai bên cam kết tôn trọng và thực hiện nghiêm túc các điều khoản
            đã nêu trong Hợp Đồng / Phụ Lục. Trong quá trình thực hiện nếu gặp khó
            khăn, <br />
            vướng mắc thì phải kịp thời thông báo cho bên kia bằng văn bản để cùng
            bàn bạc giải quyết trên tinh thần hợp tác thương lượng. Mọi tranh chấp
            không <br />
            giải quyết được bằng thương lượng sẽ được đưa ra giải quyết tại Tòa án
            Nhân dân có thẩm quyền.
          </p>
          <p class="ft08">
            7.5. Hợp đồng được lập thành 02 (hai) bản bằng tiếng Việt có giá trị
            pháp lý như nhau, mỗi bên giữ 01 (một) bản và có hiệu lực kể từ ngày
            ký.
          </p>
          <p class="ft08">
            7.6. Hai Bên đã đọc kỹ, hiểu rõ và đồng ý ký tên, đóng dấu dưới dây:
          </p>
        </div>
        <p
          style="position: absolute; top: 659px; left: 210px; white-space: nowrap"
          class="ft06"
        >
          <b>ĐẠI DIỆN BÊN A</b>
        </p>
        <p
          style="position: absolute; top: 659px; left: 595px; white-space: nowrap"
          class="ft06"
        >
          <b>ĐẠI DIỆN BÊN B</b>
        </p>
      </div>
    </div>

    <script>
      function printContract() {
          window.print();
      }
      function exportToPDF() {
          const contractContent = document.getElementById('contract-content'); // Chọn toàn bộ hợp đồng
          const div3 = document.getElementById('page3-div')
          // sửa lại thành padding
          div3.style.paddingBottom = '50px'

          const options = {
              margin: 0,
              filename: 'HopDong.pdf',
              image: { type: 'jpeg', quality: 1.0 },
              html2canvas: { scale: 2 },
              jsPDF: { 
                  unit: 'px', // Đơn vị đo lường (có thể là 'mm', 'pt', hoặc 'in')
                  format: [892, 1272], // Chiều rộng và chiều cao tùy chỉnh (đơn vị: mm trong ví dụ này)
                  orientation: 'portrait' 
              }
          };

          html2pdf()
            .set(options)
            .from(contractContent)
            .save()
            .then(() => {
              // Khôi phục chiều cao ban đầu của div3 sau khi tạo PDF xong
              div3.style.paddingBottom = '0px';
        });;
      }
  </script>
  </body>
</html>
