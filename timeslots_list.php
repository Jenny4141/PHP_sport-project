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

$title = '時間管理';
$pageName = 'timeslots_list';

// 設定分頁
$perPage = 20; # 每頁幾筆資料
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
  $searchSql = "WHERE tp.name LIKE ? OR ts.start_time LIKE ? OR ts.end_time LIKE ?";
  $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(1) FROM time_slots ts JOIN time_periods tp ON ts.period_id = tp.id $searchSql";
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

// 取得符合條件的場館與地區列表
$sql = "SELECT ts.id, 
        DATE_FORMAT(ts.start_time, '%H:%i') AS start_time, 
        DATE_FORMAT(ts.end_time, '%H:%i') AS end_time, 
        tp.name AS period_name
        FROM time_slots ts
        JOIN time_periods tp ON ts.period_id = tp.id
        $searchSql
        ORDER BY ts.id
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
        <!-- 新增按鈕 -->
        <div>
          <a href=" timeslots_add.php" class="btn btn-outline-primary" style="white-space: nowrap;">新增</a>
          <button id="deleteSelected" class="btn btn-outline-danger">刪除選擇項目</button>
        </div>

        <!-- 搜尋框區塊 -->
        <form class="d-flex align-items-center mb-0" role="search" method="GET" action="timeslots_list.php">
          <input class="form-control me-2" type="search" name="search" placeholder="輸入時間或時段" value="<?= htmlentities($search) ?>" aria-label="Search" />
          <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
        </form>
      </div>

      <!-- 主內容表格區 -->
      <div class="mb-3">
        <div class="table-responsive">
          <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
            <thead>
              <tr>
                <th class="sm-th">全選 <input type="checkbox" id="selectAll"></th>
                <th class="sm-th">編號</th>
                <th>時段</th>
                <th>開始時間</th>
                <th>結束時間</th>
                <th class="sm-th">編輯</th>
                <th class="sm-th">刪除</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td class="sm-td"><input type="checkbox" class="select-item" value="<?= $r['id'] ?>"></td>
                  <td><?= $r['id'] ?></td>
                  <td class="search-field"><?= htmlentities($r['period_name']) ?></td>
                  <td class="search-field startTime"><?= htmlentities($r['start_time']) ?></td>
                  <td class="search-field endTime"><?= htmlentities($r['end_time']) ?></td>
                  <td class="sm-td">
                    <a href="timeslots_edit.php?id=<?= $r['id'] ?>">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                  </td>
                  <td class="sm-td">
                    <a href="#" class="delete-btn text-danger">
                      <i class="fa-solid fa-trash-can"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
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
        deleteId = parseInt(deleteRow.querySelector("td:nth-child(2)").innerText.trim(), 10);
        const startTime = deleteRow.querySelector(".startTime").innerHTML;
        const endTime = deleteRow.querySelector(".endTime").innerHTML;

        // 更新 Modal 的訊息
        deleteMessage.innerHTML = `確定要刪除 <b>${startTime}-${endTime}</b> 的資料嗎？`;

        // 顯示 Modal
        deleteModal.show();
      });
    });

    confirmDeleteButton.addEventListener("click", () => {
      if (deleteId) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        fetch(`timeslots_delete-api.php?page=${page}&search=${encodeURIComponent(search)}`, {
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
              // window.location.href = `timeslots_list.php?page=${page}&search=${encodeURIComponent(search)}`;
            } else {
              alert("刪除失敗：" + data.error);
            }
            // 隱藏 Modal
            deleteModal.hide();
          })
          .catch(error => console.error("刪除時發生錯誤", error));
      }
    });

    // 多選刪除模塊
    const selectAll = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll(".select-item");
    const deleteSelectedBtn = document.getElementById("deleteSelected");

    checkboxes.forEach(checkbox => {
      checkbox.addEventListener("change", () => {
        const row = checkbox.closest("tr");
        if (checkbox.checked) {
          row.classList.add("table-active"); // ✅ 添加高亮樣式
        } else {
          row.classList.remove("table-active"); // ✅ 移除高亮樣式
        }
      });
    });
    // 控制「全選」行為
    selectAll.addEventListener("change", () => {
      checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        const row = checkbox.closest("tr");
        if (checkbox.checked) {
          row.classList.add("table-active");
        } else {
          row.classList.remove("table-active");
        }
      });
    });

    // 控制「批量刪除」按鈕
    deleteSelectedBtn.addEventListener("click", () => {
      const selectedTimes = Array.from(checkboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => {
          const row = checkbox.closest("tr");
          const startTime = row.querySelector(".startTime").innerHTML.trim();
          const endTime = row.querySelector(".endTime").innerHTML.trim();
          return {
            id: checkbox.value,
            start: startTime,
            end: endTime
          };
        });

      if (selectedTimes.length === 0) {
        // alert("請選擇要刪除的項目！");
        deleteMessage.innerHTML = "請選擇要刪除的項目！";
        deleteModal.show();
        return;
      }

      if (selectedTimes) {
        // 取得當前分頁和搜尋條件
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        // 更新 Modal 的訊息
        const timeList = selectedTimes.map(t => `${t.start} - ${t.end}`).join("<br>");
        deleteMessage.innerHTML = `確定要刪除以下時間嗎？<br><b>${timeList}</b>`;

        // 顯示 Modal
        deleteModal.show();

        confirmDeleteButton.addEventListener("click", () => {
          fetch("timeslots_delete-api.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json"
              },
              body: JSON.stringify({
                ids: selectedTimes.map(t => t.id)
              }) // ✅ 只傳送 ID

            })
            .then(response => response.json())
            .then(data => {

              if (data.success) {
                selectedTimes.forEach(t => {
                  const row = document.querySelector(`input[value='${t.id}']`).closest("tr");
                  row.style.transition = "opacity 0.5s"; // ✅ 設置淡出動畫
                  row.style.opacity = "0";

                  setTimeout(() => {
                    row.remove();
                  }, 500); // ✅ 確保動畫有時間運行
                });
                // window.location.href = `venues_list.php?page=${page}&search=${encodeURIComponent(search)}`;
              } else {
                alert("刪除失敗：" + data.error);
              }
              // 隱藏 Modal
              deleteModal.hide();
            })
            .catch(error => console.error("刪除時發生錯誤", error));
        });
      }
    });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>