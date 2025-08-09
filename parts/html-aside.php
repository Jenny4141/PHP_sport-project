<!-- 🔹 固定側邊欄（桌面版顯示） -->

<nav class="px-3 d-none d-md-block z-1" style="width: 20%; min-width: 182px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);" id="sidebar">

  <a href="index_.php">
    <div class="d-flex justify-content-center align-items-center w-100" style="min-height: 70px;">
      <img src="images/sportify-logo-sm.png" class="w-100" alt="">
    </div>
  </a>

  <ul class="list-unstyled">
    <!-- 會員系統 -->
    <?php if (
      isset($_SESSION['member'])
      && ($_SESSION['member']['role'] === 'admin')
    ): ?>
      <li>
        <a href="#memberSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
          <i class="fa-regular fa-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 會員系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="memberSubmenu">
          <li class=""><a href="members_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">會員管理</a></li>
          <li class=""><a href="members_list_roles.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">角色管理</a></li>
        </ul>
      </li>
    <?php else : ?>
      <li>
        <a href="#memberSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
          <i class="fa-regular fa-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 會員系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="memberSubmenu">
          <li class=""><a href="members_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">會員管理</a></li>
        </ul>
      </li>
    <?php endif; ?>
    <!-- 預約場地系統 -->
    <?php if (
      isset($_SESSION['member'])
      && ($_SESSION['member']['role'] === 'admin')
    ): ?>
      <li>
        <a href="#reservationSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
          <i class="fa-regular fa-calendar fa-lg me-2" style="width: 25px; text-align: right;"></i> 預約場地系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="reservationSubmenu">
          <li><a href="venues_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場館管理</a></li>
          <li><a href="courts_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場地管理</a></li>
          <li><a href="timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">時間管理</a></li>
          <li><a href="courts_timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">價格管理</a></li>
          <li><a href="reservations_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">訂單管理</a></li>
        </ul>
      </li>
    <?php else : ?>
      <li>
        <a href="#reservationSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
          <i class="fa-regular fa-calendar fa-lg me-2" style="width: 25px; text-align: right;"></i> 預約場地系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="reservationSubmenu">
          <!-- <li><a href="venues_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場館管理</a></li> -->
          <!-- <li><a href="courts_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場地管理</a></li> -->
          <!-- <li><a href="timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">時間管理</a></li> -->
          <li><a href="courts_timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">價格管理</a></li>
          <li><a href="reservations_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">訂單管理</a></li>
        </ul>
      </li>
    <?php endif; ?>
    <!-- 商城管理系統 -->
    <?php if (
      isset($_SESSION['member'])
      && ($_SESSION['member']['role'] === 'admin')
    ): ?>
      <li>
        <a href="#shopSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
          <i class="fa-regular fa-credit-card fa-lg me-2" style="width: 25px; text-align: right;"></i> 商城管理系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="shopSubmenu">
          <li><a href="products_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">商品管理</a></li>
          <li><a href="orders_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">訂單管理</a></li>
        </ul>
      </li>
    <?php else : ?>
      <li>
        <a href="#shopSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
          <i class="fa-regular fa-credit-card fa-lg me-2" style="width: 25px; text-align: right;"></i> 商城管理系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="shopSubmenu">
          <li><a href="products_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">商品管理</a></li>
        </ul>
      </li>
    <?php endif; ?>
    <!-- 揪團報名系統 -->
    <li>
      <a href="#groupSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
        <i class="fa-solid fa-users-rectangle fa-lg me-2" style="width: 25px; text-align: right;"></i> 揪團報名系統
      </a>
      <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="groupSubmenu">
        <li><a href="teams_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">隊伍列表</a></li>
      </ul>
    </li>

    <!-- 課程報名系統 -->
    <?php if (
      isset($_SESSION['member'])
      && ($_SESSION['member']['role'] === 'admin')
    ): ?>
      <li>
        <a href="#courseSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
          <i class="fa-solid fa-chalkboard-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 課程報名系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="courseSubmenu">
          <li><a href="classes_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">課程管理</a></li>
          <li><a href="coaches_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">教練管理</a></li>
          <li><a href="booking_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">預約管理</a></li>
        </ul>
      </li>
    <?php else : ?>
      <li>
        <a href="#courseSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
          <i class="fa-solid fa-chalkboard-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 課程報名系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebar" id="courseSubmenu">
          <li><a href="classes_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">課程管理</a></li>
          <!-- <li><a href="coaches_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">教練管理</a></li> -->
          <!-- <li><a href="booking_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">預約管理</a></li> -->
        </ul>
      </li>
    <?php endif; ?>
  </ul>
</nav>

<!-- 🔹 收起式側邊欄（手機版） -->
<div class="offcanvas offcanvas-start bg-light text-dark-blue d-md-none" id="sidebarMobile">
  <div class="offcanvas-header">
    <a href="index_.php">
      <div class="d-flex justify-content-center align-items-center w-100" style="height: 70px;">
        <img src="images/sportify-logo-sm.png" class="w-100" alt="">
      </div>
    </a>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="list-unstyled">
      <!-- 會員系統 -->
      <?php if (
        isset($_SESSION['member'])
        && ($_SESSION['member']['role'] === 'admin')
      ): ?>
        <li>
          <a href="#memberSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
            <i class="fa-regular fa-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 會員系統
          </a>
          <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="memberSubmenu">
            <li class=""><a href="members_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">會員管理</a></li>
            <li class=""><a href="members_roles.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">角色管理</a></li>
          </ul>
        </li>
      <?php else : ?>
        <a href="#memberSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
          <i class="fa-regular fa-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 會員系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="memberSubmenu">
          <li class=""><a href="members_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">會員管理</a></li>
        </ul>
        </li>
      <?php endif; ?>
      <!-- 預約場地系統 -->
      <?php if (
        isset($_SESSION['member'])
        && ($_SESSION['member']['role'] === 'admin')
      ): ?>
        <li>
          <a href="#reservationSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
            <i class="fa-regular fa-calendar fa-lg me-2" style="width: 25px; text-align: right;"></i> 預約場地系統
          </a>
          <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="reservationSubmenu">
            <li><a href="venues_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場館管理</a></li>
            <li><a href="courts_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場地管理</a></li>
            <li><a href="timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">時間管理</a></li>
            <li><a href="courts_timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">價格管理</a></li>
            <li><a href="reservations_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">訂單管理</a></li>
          </ul>
        </li>
      <?php else : ?>
        <li>
          <a href="#reservationSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded sidebar-link" data-bs-toggle="collapse">
            <i class="fa-regular fa-calendar fa-lg me-2" style="width: 25px; text-align: right;"></i> 預約場地系統
          </a>
          <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="reservationSubmenu">
            <!-- <li><a href="venues_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場館管理</a></li> -->
            <!-- <li><a href="courts_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">場地管理</a></li> -->
            <!-- <li><a href="timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">時間管理</a></li> -->
            <li><a href="courts_timeslots_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">價格管理</a></li>
            <li><a href="reservations_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">訂單管理</a></li>
          </ul>
        </li>
      <?php endif; ?>
      <!-- 商城管理系統 -->
      <?php if (
        isset($_SESSION['member'])
        && ($_SESSION['member']['role'] === 'admin')
      ): ?>
        <li>
          <a href="#shopSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
            <i class="fa-regular fa-credit-card fa-lg me-2" style="width: 25px; text-align: right;"></i> 商城管理系統
          </a>
          <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="shopSubmenu">
            <li><a href="products_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">商品管理</a></li>
            <li><a href="orders_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">訂單管理</a></li>
          </ul>
        </li>
      <?php else : ?>
        <li>
          <a href="#shopSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
            <i class="fa-regular fa-credit-card fa-lg me-2" style="width: 25px; text-align: right;"></i> 商城管理系統
          </a>
          <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="shopSubmenu">
            <li><a href="products_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">商品管理</a></li>
          </ul>
        </li>
      <?php endif; ?>
      <!-- 揪團報名系統 -->
      <li>
        <a href="#groupSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
          <i class="fa-solid fa-users-rectangle fa-lg me-2" style="width: 25px; text-align: right;"></i> 揪團報名系統
        </a>
        <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="groupSubmenu">
          <li><a href="teams_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">隊伍列表</a></li>
        </ul>
      </li>

      <!-- 課程報名系統 -->
      <?php if (
        isset($_SESSION['member'])
        && ($_SESSION['member']['role'] === 'admin')
      ): ?>
        <li>
          <a href="#courseSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
            <i class="fa-solid fa-chalkboard-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 課程報名系統
          </a>
          <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="courseSubmenu">
            <li><a href="classes_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">課程管理</a></li>
            <li><a href="coaches_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">教練管理</a></li>
            <li><a href="booking_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">預約管理</a></li>
          </ul>
        </li>
      <?php else : ?>
        <li>
          <a href="#courseSubmenu" class="text-dark-blue d-block border-bottom p-2 rounded  sidebar-link" data-bs-toggle="collapse">
            <i class="fa-solid fa-chalkboard-user fa-lg me-2" style="width: 25px; text-align: right;"></i> 課程報名系統
          </a>
          <ul class="collapse list-unstyled sidebar" data-bs-parent="#sidebarMobile" id="courseSubmenu">
            <li><a href="classes_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">課程管理</a></li>
            <!-- <li><a href="coaches_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">教練管理</a></li> -->
            <!-- <li><a href="booking_list.php" class="text-secondary d-block p-2 sidebar-link " style="margin-left: 36px">預約管理</a></li> -->
          </ul>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</div>