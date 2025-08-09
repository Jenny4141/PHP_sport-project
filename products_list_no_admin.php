<?php include __DIR__ . '/parts/init.php'; # 初始化頁面

$title = '商品管理';
$pageName = 'products_list';

// 設定分頁
$perPage = 15; # 每頁幾筆資料
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) {
  header('Location: ?page=1');
  exit;
}

// 取得搜尋關鍵字
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = '';
$params = [];

if (!empty($search)) {
  $searchSql = "WHERE LOWER(pr.name) LIKE LOWER(?) 
              OR LOWER(brands.name) LIKE LOWER(?) 
              OR LOWER(sports.name) LIKE LOWER(?)";
  $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(1) 
          FROM specs sp 
          JOIN products pr ON sp.product_id = pr.product_id 
          JOIN brands ON pr.brand_id = brands.brand_id 
          JOIN sports ON pr.sport_id = sports.id 
          $searchSql";
$stmt = $pdo->prepare($t_sql);
$stmt->execute($params);


# 預設值
$totalPages = 0;
$rows = [];

// 計算總頁數
$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
$totalPages = ceil($totalRows / $perPage);

// 確保 page 不超過最大頁數
if ($page > $totalPages && $totalPages > 0) {
  header("Location: ?page={$totalPages}");
  exit;
}

// 取得符合條件的商品
$sql = "SELECT sp.spec_id, pr.product_id, pr.name AS product_name, sports.name AS sport_name, brands.name AS brand_name, sp.price, sp.color, sp.stock, sp.updated FROM specs sp
        JOIN products pr ON sp.product_id = pr.product_id
        JOIN brands ON pr.brand_id = brands.brand_id
        JOIN sports ON pr.sport_id = sports.id
        $searchSql 
        ORDER BY sp.spec_id 
        LIMIT " . (($page - 1) * $perPage) . ", " . $perPage;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

?>
<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<div class="container-fluid px-3">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-11 col-md-10">
  <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2 mb-3">
    <!-- 新增按鈕 -->
    <!-- <a href="products_add.php" class="btn btn-outline-primary">新增</a> -->
    <!-- 搜尋框區塊 -->
    <form class="d-flex align-items-center mb-0" role="search" method="GET" action="products_list.php">
      <input class="form-control me-2" type="search" name="search" placeholder="輸入名稱、品牌、運動類型" value="<?= htmlentities($search) ?>" aria-label="Search" />
      <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
    </form>
  </div>

  <!-- 主內容表格區 -->
<div class="mb-3">
    <div class="table-responsive">
      <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
        <thead>
          <tr>
            <th class="sm-th">編號</th>
            <th>商品名稱</th>
            <th>運動種類</th>
            <th>品牌</th>
            <th>款式</th>
            <th>單價</th>
            <th>庫存</th>
            <th>更新時間</th>
            <!-- <th class="sm-th">編輯</th>
            <th class="sm-th">刪除</th> -->
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="11" class="text-muted text-center">查無符合條件的資料</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="sm-td"><?= $r['spec_id'] ?></td>
              <td class="name search-field"><?= htmlentities($r['product_name']) ?></td>
              <td class="search-field"><?= htmlentities($r['sport_name']) ?></td>
              <td class="search-field"><?= htmlentities($r['brand_name']) ?></td>
              <td class="search-field"><?= htmlentities($r['color']) ?></td>
              <td class="search-field"><?= htmlentities($r['price']) ?></td>
              <td class="search-field"><?= htmlentities($r['stock']) ?></td>
              <td class="search-field"><?= htmlentities($r['updated']) ?></td>
              <!-- <td class="sm-td">
                <a href="products_edit.php?spec_id=<?= $r['spec_id'] ?>">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>
              </td>
              <td class="sm-td">
                <a href="#" class="delete-btn text-danger">
                  <i class="fa-solid fa-trash-can"></i>
                </a>
              </td> -->
            </tr>
          <?php endforeach; ?>
      </table>
    </div>
  </div>

  <!-- 分頁按鈕區 -->
  <?php include __DIR__ . '/parts/html-pagination.php' ?>

    </div>
  </div>
</div>
<!-- 確認刪除 Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">確認刪除</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="deleteMessage" class="alert alert-danger" role="alert">
          確定要刪除 號碼 名稱 的規格嗎？
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">刪除</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
  // ✅ 搜尋模塊（支援不分大小寫並避免 HTML 結構破壞）
  const $_GET = <?= json_encode($_GET) ?>;
  const keyword = $_GET.search;

  if (keyword) {
    const searchFields = document.querySelectorAll('.search-field');

    // 保護使用者輸入，將正規表達式的特殊字元跳脫
    const safeKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(safeKeyword, 'gi'); // gi：全域、不分大小寫

    searchFields.forEach(td => {
      const raw = td.textContent; // 取得純文字
      td.innerHTML = raw.replace(regex, match => `<b>${match}</b>`); // 加粗比對文字
    });
  }


  document.addEventListener("DOMContentLoaded", () => {

    // ✅ 表單刪除模塊
    const deleteButtons = document.querySelectorAll(".delete-btn");
    let deleteId = null; // 存儲要刪除的 ID
    let deleteRow = null; // 存儲要刪除的行

    const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
    const deleteMessage = document.getElementById("deleteMessage");
    const confirmDeleteButton = document.getElementById("confirmDelete");

    deleteButtons.forEach(button => {
      button.addEventListener("click", (e) => {
        e.preventDefault();

        deleteRow = e.target.closest("tr");
        deleteId = parseInt(deleteRow.querySelector("td:first-child").innerText.trim(), 10);
        const name = deleteRow.querySelector(".name").innerHTML;

        // 更新 Modal 的訊息
        deleteMessage.innerHTML = `確定要刪除 <b>${deleteId}-${name}</b> 的規格嗎？`;

        // 顯示 Modal
        deleteModal.show();
      });
    });

    confirmDeleteButton.addEventListener("click", () => {
      if (deleteId) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        fetch(`products_delete-api.php?page=${page}&search=${encodeURIComponent(search)}`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              id: deleteId
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              deleteRow.style.transition = "opacity 0.3s";
              deleteRow.style.opacity = "0";
              setTimeout(() => deleteRow.remove(), 300);
              window.location.href = `products_list.php?page=${page}&search=${encodeURIComponent(search)}`;
            } else {
              alert("刪除失敗：" + data.error);
            }
            // 隱藏 Modal
            deleteModal.hide();
          })
          .catch(error => console.error("刪除時發生錯誤", error));
      }
    });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>