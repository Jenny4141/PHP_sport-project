<?php include __DIR__ . '/parts/init.php'; # 初始化頁面

$title = '隊伍管理';
$pageName = 'teams_list';

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
  $searchSql = "WHERE ts.name LIKE ? OR c.name LIKE ? OR s.name LIKE ? OR level.name LIKE ?";
  $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(DISTINCT ts.id) FROM teams AS ts
          JOIN courts AS c ON ts.courts_id = c.id
          JOIN level ON ts.level_id = level.id
          JOIN sports AS s ON c.sport_id = s.id
          LEFT JOIN tmember AS tm ON ts.id = tm.team_id 
          $searchSql";
$stmt = $pdo->prepare($t_sql);
$stmt->execute($params);

# 預設值
$totalPages = 0;
$rows = [];

$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
$totalPages = ceil($totalRows / $perPage);

// 確保 page 不超過最大頁數
if ($page > $totalPages && $totalPages > 0) {
  header("Location: ?page={$totalPages}");
  exit;
}

// 取得符合條件的場地列表
$sql = "SELECT
    ts.id,
    ts.name,
    level.name AS level_name,
    c.name AS court_name,
    s.name AS sport_name,
    COUNT(tm.id) AS member_count, -- 計算隊伍成員數量
    ts.created_at
FROM
    teams AS ts
JOIN
    courts AS c ON ts.courts_id = c.id
JOIN
    level ON ts.level_id = level.id
JOIN
    sports AS s ON c.sport_id = s.id
LEFT JOIN
    tmember AS tm ON ts.id = tm.team_id -- 連接隊伍成員表
$searchSql
GROUP BY
    ts.id, ts.name, level.name, c.name, s.name, ts.created_at -- 必須 GROUP BY 所有 SELECT 中的非聚合欄位
ORDER BY
    ts.id
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
  <div class="d-flex flex-wrap justify-content-md-between align-items-center gap-2 mb-3">
    <a href="teams_add.php" class="btn btn-outline-primary">新增</a>
    <form class="d-flex align-items-center mb-0" role="search" method="GET" action="teams_list.php">
      <input class="form-control me-2" type="search" name="search" placeholder="輸入隊名、場地或運動類型" value="<?= htmlentities($search) ?>" aria-label="Search" />
      <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
    </form>
  </div>

<div class="mb-3">
    <div class="table-responsive">
      <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
        <thead>
          <tr>
            <th class="sm-th">隊伍編號</th>
            <th>隊伍名稱</th>
            <th>運動種類</th>
            <th>階級</th>
            <th>隊伍人數</th>
            <th>出沒場地</th>
            <th class="sm-th">編輯</th>
            <th class="sm-th">刪除</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="sm-td"><?= $r['id'] ?></td>
              <td class="search-field name"><?= htmlentities($r['name']) ?></td>
              <td class="search-field sportName"><?= htmlentities($r['sport_name']) ?></td>
              <td class="search-field levelName"><?= htmlentities($r['level_name']) ?></td>
              <td><?= htmlentities($r['member_count']) ?>/8</td>
              <td class="search-field courtName"><?= htmlentities($r['court_name']) ?></td>
              <td class="sm-td">
                <a href="teams_edit.php?id=<?= $r['id'] ?>">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>
              </td>
              <td class="sm-td">
                <a href="#" class="delete-btn text-danger" data-id="<?= $r['id'] ?>" data-name="<?= htmlentities($r['name']) ?>">
                  <i class="fa-solid fa-trash-can"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
      </table>
    </div>
  </div>

  <?php include __DIR__ . '/parts/html-pagination.php' ?>

    </div>
  </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">確認刪除</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="deleteMessage" class="alert alert-danger" role="alert">
          確定要刪除 號碼 名稱 的資料嗎？
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">刪除</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/parts/html-scripts.php' ?> <script>
  // ✅ 搜尋模塊
  const $_GET = <?= json_encode($_GET) ?>; // 生成頁面時, 直接把資料放到 JS
  const search = $_GET.search;
  if (search) {
    const searchFields = document.querySelectorAll('.search-field');
    for (let td of searchFields) {
      // 使用正規表達式和 'gi' 標誌進行全局不區分大小寫替換
      const regex = new RegExp(search, 'gi'); // **修正搜尋高亮邏輯**
      td.innerHTML = td.innerHTML.replace(regex, `<b>$&</b>`);
    }
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
        // 從 data-id 屬性獲取 ID，這比直接讀取表格內容更可靠
        deleteId = parseInt(button.dataset.id, 10);
        const name = button.dataset.name; // 從 data-name 屬性獲取名稱

        // 更新 Modal 的訊息
        deleteMessage.innerHTML = `確定要刪除 <b>${deleteId}-${name}</b> 的資料嗎？`;

        // 顯示 Modal
        deleteModal.show();
      });
    });

    confirmDeleteButton.addEventListener("click", () => {
      if (deleteId) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        // 修改這裡: 指向 teams-edit-api.php (破折號) 並傳遞 action=delete-team
        fetch(`teams_edit-api.php`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Accept": "application/json"
            },
            body: JSON.stringify({
              action: 'delete-team', // 新增 action 參數
              id: deleteId
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              deleteRow.style.transition = "opacity 0.3s";
              deleteRow.style.opacity = "0";
              setTimeout(() => deleteRow.remove(), 300);
              // 刪除成功後，重新導向回當前頁面以刷新列表 (保留分頁和搜尋)
              window.location.href = `teams_list.php?page=${page}&search=${encodeURIComponent(search)}`; // 回到 teams_list.php (底線)
            } else {
              alert("刪除失敗：" + (data.error || '未知錯誤'));
            }
            // 隱藏 Modal
            deleteModal.hide();
          })
          .catch(error => {
            console.error("刪除時發生錯誤", error);
            alert("刪除時發生網路錯誤。");
            deleteModal.hide();
          });
      }
    });
  });
</script>

<?php include __DIR__ . '/parts/html-tail.php' ?>