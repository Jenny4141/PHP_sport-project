<!-- 分頁按鈕區 -->
<div class="d-flex flex-column mb-5">

  <div class="d-flex justify-content-center">

    <div class="d-inline-block alert alert-light p-2" role="alert">
      <span class="text-muted mb-0">目前總共有 <strong class="text-danger"><?= $totalRows ?></strong> 筆資料。</span>
    </div>

  </div>

  <nav aria-label="Page navigation example">
    <ul class="pagination d-flex justify-content-center">
      <!-- 跳到第一頁 -->
      <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
        <a class="page-link d-flex align-items-center" style="height: 38px;" href="?page=1&search=<?= urlencode($search) ?>" aria-label="First">
          <span aria-hidden="true"><i class="fa-solid fa-angles-left"></i></span>
        </a>
      </li>
      <!-- 跳到前一頁 -->
      <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
        <a class="page-link d-flex align-items-center" style="height: 38px;" href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>" aria-label="Previous">
          <span aria-hidden="true"><i class="fa-solid fa-angle-left"></i></span>
        </a>
      </li>
      <!-- 頁碼 -->
      <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <?php if ($i == $page): ?>
            <span class="page-link"><?= $i ?></span>
          <?php else: ?>
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          <?php endif; ?>
        </li>
      <?php endfor; ?>
      <!-- 跳到下一頁 -->
      <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
        <a class="page-link d-flex align-items-center" style="height: 38px;" href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>" aria-label="Next">
          <span aria-hidden="true"><i class="fa-solid fa-angle-right"></i></span>
        </a>
      </li>
      <!-- 跳到最後一頁 -->
      <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
        <a class="page-link d-flex align-items-center" style="height: 38px;" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>" aria-label="Last">
          <span aria-hidden="true"><i class="fa-solid fa-angles-right"></i></span>
        </a>
      </li>
    </ul>
  </nav>

</div>