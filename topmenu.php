    <?php
    if ($role == 'user' || $role == 'admin') {
        echo ' <header class="top-menu clearfix bg-success sticky-top">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a class="navbar-brand" href="dashboard.php">
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
        </a>

                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 ms-3">
                    <li><a href="sale_order.php" class="nav-link text-white px-2 link-dark">Sale Orders</a></li>
                    <li class="ms-3"><a href="ticket_management.php" class="nav-link text-white px-2 link-dark">Help Desk</a></li>
                </ul>
            </div>
        </header>';
    } else {
        echo ' <header class="top-menu clearfix bg-success sticky-top">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 ms-3">
                    <li class="ms-3"><a href="ticket_management.php" class="nav-link text-white px-2 link-dark">Help Desk</a></li>
                    <a href="logout.php">Log Out</a>
                </ul>
            </div>
        </header>';
    }
    ?>

