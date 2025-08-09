<?php
if (!isset($pageName)) {
  $pageName = '';
}
?>

<div class="container-fluid bg-light flex-grow-1" style="overflow: hidden;">
  <nav class="border-bottom navbar navbar-expand-lg mb-4 w-100">
    <div class="container-fluid">
      <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
        <span class="navbar-toggler-icon"></span>
      </button>
      <a class="navbar-brand" href="#" style="font-weight: bold; color: #090e4e;"><?= !empty($title) ? "$title" : 'Sports' ?></a>
      <button class="btn btn-outline-secondary navbar-toggler" style="height: 38px;" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="fs-6">帳戶</span>
      </button>
      <div class="collapse navbar-collapse text-end" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <?php if (isset($_SESSION['member'])
          // &&($_SESSION['member']['role'] === 'admin')
        ): ?>
            <li class="nav-item me-2 dropdown">
              <a class="nav-link dropdown-toggle py-0" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                <?php if (!empty($_SESSION['member']['avatar_url'])): ?>
                  <img src="<?= htmlspecialchars($_SESSION['member']['avatar_url']) ?>" class="rounded-circle me-1" style="max-width: 35px;">
                <?php endif; ?>
                <?= $_SESSION['member']['username'] ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="logout.php">登出</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item me-2">
              <a class="nav-link" href="login.php">登入</a>
            </li>
            <li class="nav-item me-2">
              <a class="nav-link" href="members_register.php">註冊</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
