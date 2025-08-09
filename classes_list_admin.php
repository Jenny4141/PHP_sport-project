<?php include __DIR__ . '/parts/init.php'; # 初始化頁面

$title = '課程管理';
$pageName = 'classes_list';

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
  $searchSql = "WHERE c.classname_id LIKE ? OR ch.coachname_id LIKE ? OR cr.name LIKE ?";
  $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(1)
          FROM sessions s
          JOIN classes c ON s.course_id = c.class_id
          JOIN courts cr ON s.courts_id = cr.id
          JOIN coaches ch ON s.coach_id = ch.coach_id
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

// 取得課程資料
$sql = "SELECT 
            s.session_id,
            c.classname_id AS course_name,
            cr.name AS court_name,
            ch.coachname_id AS coach_name,
            s.sessions_date,
            s.sessions_time,
            s.price,
            s.max_capacity
        FROM sessions s
        JOIN classes c ON s.course_id = c.class_id
        JOIN courts cr ON s.courts_id = cr.id
        JOIN coaches ch ON s.coach_id = ch.coach_id
        $searchSql
        ORDER BY s.session_id
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
        <a href="classes_add.php" class="btn btn-outline-primary">新增</a>
        <form class="d-flex align-items-center mb-0" role="search" method="GET" action="classes_list.php">
          <input class="form-control me-2" type="search" name="search" placeholder="輸入課程名稱、單號" value="<?= htmlentities($search) ?>" aria-label="Search" />
          <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
        </form>
      </div>

      <div class="mb-3">
        <div class="table-responsive">
          <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
            <thead>
              <tr>
                <th class="sm-th">代號</th>
                <th>課程名稱</th>
                <th>場地名稱</th>
                <th>教練</th>
                <th>日期</th>
                <th>時間</th>
                <th>金額</th>
                <th>人數上限</th>
                <th class="sm-th">編輯</th>
                <th class="sm-th">刪除</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td class="sm-td"><?= $r['session_id'] ?></td>
                  <td class="search-field "><?= htmlentities($r['course_name']) ?></td>
                  <td class="search-field "><?= htmlentities($r['court_name']) ?></td>
                  <td class="search-field "><?= htmlentities($r['coach_name']) ?></td>
                  <td class="search-field "><?= htmlentities($r['sessions_date']) ?></td>
                  <td class="search-field "><?= htmlentities($r['sessions_time']) ?></td>
                  <td class="search-field "><?= htmlentities($r['price']) ?></td>
                  <td class="search-field "><?= htmlentities($r['max_capacity']) ?></td>
                  <td class="sm-td">
                    <a href="classes_edit.php?id=<?= $r['session_id'] ?>">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                  </td>
                  <td class="sm-td">
                    <a href="#" class="delete-btn">
                      <i class="fa-solid fa-trash-can text-danger "></i>
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
<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
  // ✅ 搜尋模塊
  const $_GET = <?= json_encode($_GET) ?>; // 生頁面時, 直接把資料放到 JS
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
        /* const name = deleteRow.querySelector(".name").innerHTML;
        const venueName = deleteRow.querySelector(".venueName").innerHTML;
        const sportName = deleteRow.querySelector(".sportName").innerHTML; */

        // 更新 Modal 的訊息
        deleteMessage.innerHTML = `確定要刪除 <b>${deleteId}</b> 的資料嗎？`;

        // 顯示 Modal
        deleteModal.show();
      });
    });

    confirmDeleteButton.addEventListener("click", () => {
      if (deleteId) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        fetch(`classes_delete-api.php?page=${page}&search=${encodeURIComponent(search)}`, {
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
              window.location.href = `classes_list.php?page=${page}&search=${encodeURIComponent(search)}`;
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