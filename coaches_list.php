<?php include __DIR__ . '/parts/init.php'; # 初始化頁面
//阻擋admin以外的人進入
$come_from = "index_.php";
if (!empty($_SERVER['HTTP_REFERER'])) {
  $come_from = $_SERVER['HTTP_REFERER'];
}
if ($_SESSION['member']['role'] !== 'admin')
// if (!isset($_SESSION['admin'])) 
{
  header("Location: $come_from"); // 或導回首頁
  exit;
}
$title = '教練人員管理';
$pageName = 'coaches_list';

// 設定分頁
$perPage = 20;
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
  $searchSql = "WHERE 
    c.coachname_id LIKE ? OR 
    s.specialty_name LIKE ? OR
    c.phone LIKE ? OR
    c.email LIKE ?";
  $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(1) FROM coaches c 
          JOIN specialties s ON c.specialty = s.specialty_id
          $searchSql";
$stmt = $pdo->prepare($t_sql);
$stmt->execute($params);
$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
$totalPages = ceil($totalRows / $perPage);

// 確保 page 不超過最大頁數
if ($page > $totalPages && $totalPages > 0) {
  header("Location: ?page={$totalPages}");
  exit;
}

// 取得教練資料
$sql = "SELECT c.*, s.specialty_name
        FROM coaches c
        JOIN specialties s ON c.specialty = s.specialty_id
        $searchSql
        ORDER BY c.coach_id
        LIMIT " . (($page - 1) * $perPage) . ", " . $perPage;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>
<div class="col-12 col-sm-11 col-md-10 px-3 container-fluid">
  <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-center gap-2 mb-3">
    <a href="coaches_add.php" class="btn btn-outline-primary">新增</a>
    <form class="d-flex align-items-center mb-0" role="search" method="GET" action="coaches_list.php">
      <input class="form-control me-2" type="search" name="search" placeholder="輸入姓名、專長" value="<?= htmlentities($search) ?>" aria-label="Search" />
      <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
    </form>
  </div>

  <div class="mb-3 row justify-content-center">
    <div class="table-responsive">
      <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
        <thead>
          <tr>
            <th class="sm-th">教練編號</th>
            <th>姓名</th>
            <th>專長</th>
            <th>連絡電話</th>
            <th>E-mail</th>
            <th class="sm-th">編輯</th>
            <th class="sm-th">刪除</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="sm-td"><?= htmlentities($r['coach_id']) ?></td>
              <td class="search-field coach-name"><?= htmlentities($r['coachname_id']) ?></td>
              <td class="search-field specialty-name"><?= htmlentities($r['specialty_name']) ?></td>
              <td class="search-field"><?= htmlentities($r['phone']) ?></td>
              <td class="search-field"><?= htmlentities($r['email']) ?></td>
              <td class="sm-td">
                <a href="coaches_edit.php?coach_id=<?= $r['coach_id'] ?>">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>
              </td>
              <td class="sm-td">
                <a href="#" class="delete-btn">
                  <i class="fa-solid fa-trash-can text-danger"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php include __DIR__ . '/parts/html-pagination.php' ?>

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
          確定要刪除教練資料嗎？
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
  // ✅ 搜尋模塊 - 高亮搜尋關鍵字
  const $_GET = <?= json_encode($_GET) ?>;
  const search = $_GET.search;
  if (search) {
    const searchFields = document.querySelectorAll('.search-field');
    for (let td of searchFields) {
      td.innerHTML = td.innerHTML.split(search).join(`<b>${search}</b>`)
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    // ✅ 表單刪除模塊
    const deleteButtons = document.querySelectorAll(".delete-btn");
    let deleteId = null;
    let deleteRow = null;

    const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
    const deleteMessage = document.getElementById("deleteMessage");
    const confirmDeleteButton = document.getElementById("confirmDelete");

    deleteButtons.forEach(button => {
      button.addEventListener("click", (e) => {
        e.preventDefault();

        deleteRow = e.target.closest("tr");
        deleteId = parseInt(deleteRow.querySelector("td:first-child").innerText.trim(), 10);
        const coachName = deleteRow.querySelector(".coach-name").innerText.trim();

        // 更新 Modal 的訊息
        deleteMessage.innerHTML = `確定要刪除教練 <b>${deleteId} - ${coachName}</b> 的資料嗎？`;

        // 顯示 Modal
        deleteModal.show();
      });
    });

    confirmDeleteButton.addEventListener("click", () => {
      if (deleteId) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        fetch(`coaches_delete-api.php?page=${page}&search=${encodeURIComponent(search)}`, {
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
              // 添加淡出效果
              deleteRow.style.transition = "opacity 0.3s";
              deleteRow.style.opacity = "0";
              setTimeout(() => {
                // 重新載入頁面以更新數據
                window.location.href = `coaches_list.php?page=${page}&search=${encodeURIComponent(search)}`;
              }, 300);
            } else {
              alert("刪除失敗：" + data.error);
            }
            // 隱藏 Modal
            deleteModal.hide();
          })
          .catch(error => {
            console.error("刪除時發生錯誤", error);
            alert("刪除時發生錯誤，請稍後再試");
            deleteModal.hide();
          });
      }
    });
  });
</script>

<?php include __DIR__ . '/parts/html-tail.php' ?>