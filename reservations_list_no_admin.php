<?php include __DIR__ . '/parts/init.php'; # 初始化頁面

$title = '訂單管理';
$pageName = 'reservations_list';

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
  $searchSql = "WHERE m.username LIKE ? OR c.name LIKE ? OR ts.start_time LIKE ? OR r.date LIKE ?";
  $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(1) FROM reservations r
          JOIN members m ON r.member_id = m.id
          JOIN courts_timeslots ct ON r.court_timeslot_id = ct.id
          JOIN courts c ON ct.court_id = c.id
          JOIN time_slots ts ON ct.time_slot_id = ts.id
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

// 取得符合條件的訂單
$sql = "SELECT r.id, m.username, c.name AS court_name, 
        DATE_FORMAT(ts.start_time, '%H:%i') AS start_time, 
        DATE_FORMAT(ts.end_time, '%H:%i') AS end_time, 
        r.date, r.price AS price, 
        rs.name AS status,
        r.created_at AS created
        FROM reservations r
        JOIN members m ON r.member_id = m.id
        JOIN courts_timeslots ct ON r.court_timeslot_id = ct.id
        JOIN courts c ON ct.court_id = c.id
        JOIN time_slots ts ON ct.time_slot_id = ts.id
        JOIN reservation_statuses rs ON r.status_id = rs.id
        $searchSql 
        ORDER BY r.id  
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
        <div>
          <!-- <a href="reservations_add.php" class="btn btn-outline-primary" style="white-space: nowrap;">新增</a> -->
          <!-- <button id="deleteSelected" class="btn btn-outline-danger">刪除選擇項目</button> -->
        </div>

        <!-- 搜尋框 -->
        <form class="d-flex align-items-center mb-0" role="search" method="GET" action="reservations_list.php">
          <input class="form-control me-2" type="search" name="search" placeholder="輸入條件" value="<?= htmlentities($search) ?>" aria-label="Search" />
          <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
        </form>
      </div>

      <!-- 主內容表格區 -->
      <div class="mb-3">
        <div class="table-responsive">
          <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
            <thead>
              <tr>
                <!-- <th class="sm-th">全選 <input type="checkbox" id="selectAll"></th> -->
                <th class="sm-th">編號</th>
                <th>會員</th>
                <th>場地名稱</th>
                <th>時間段</th>
                <th>日期</th>
                <th>價格</th>
                <th>成立時間</th>
                <th>狀態</th>
                <!-- <th class="sm-th">編輯</th> -->
                <!-- <th class="sm-th">刪除</th> -->
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <!-- <td class="sm-td"><input type="checkbox" class="select-item" value="<?= $r['id'] ?>"></td> -->
                  <td><?= $r['id'] ?></td>
                  <td class="search-field username"><?= htmlentities($r['username']) ?></td>
                  <td class="search-field court"><?= htmlentities($r['court_name']) ?></td>
                  <td class="search-field time"><?= htmlentities($r['start_time']) ?> - <?= htmlentities($r['end_time']) ?></td>
                  <td class="search-field date"><?= htmlentities($r['date']) ?></td>
                  <td class="search-field price"><?= number_format($r['price'], 0) ?>元</td>
                  <td class="search-field"><?= htmlentities($r['created']) ?></td>
                  <td class="search-field status"><?= htmlentities($r['status']) ?></td>
                  <!-- <td class="sm-td">
                    <a href="reservations_edit.php?id=<?= $r['id'] ?>">
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
        const username = deleteRow.querySelector(".username").innerHTML;
        const court = deleteRow.querySelector(".court").innerHTML;
        const time = deleteRow.querySelector(".time").innerHTML;
        const date = deleteRow.querySelector(".date").innerHTML;
        const price = deleteRow.querySelector(".price").innerHTML;
        const status = deleteRow.querySelector(".status").innerHTML;

        // 更新 Modal 的訊息
        deleteMessage.innerHTML = `確定要刪除以下這筆訂單嗎？ <b><br>用戶：${username}<br>場地：${court}<br>日期：${date}<br>時間：${time}`;

        // 顯示 Modal
        deleteModal.show();
      });
    });

    confirmDeleteButton.addEventListener("click", () => {

      if (deleteId) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        fetch(`reservations_delete-api.php?page=${page}&search=${encodeURIComponent(search)}`, {
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
              // window.location.href = `courts_timeslots_list.php?page=${page}&search=${encodeURIComponent(search)}`;
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
      const selectedReservations = Array.from(checkboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => {
          const row = checkbox.closest("tr");
          const username = row.querySelector(".username").innerText.trim();
          const court = row.querySelector(".court").innerText.trim();
          const time = row.querySelector(".time").innerText.trim();
          const date = row.querySelector(".date").innerText.trim();
          const price = row.querySelector(".price").innerText.trim();
          const status = row.querySelector(".status").innerText.trim();

          return {
            id: checkbox.value,
            username: username,
            court: court,
            time: time,
            date: date,
            price: price,
            status: status
          };
        });

      if (selectedReservations.length === 0) {
        // alert("請選擇要刪除的項目！");
        deleteMessage.innerHTML = "請選擇要刪除的項目！";
        deleteModal.show();
        return;
      }

      if (selectedReservations) {
        // 取得當前分頁和搜尋條件
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        // 更新 Modal 的訊息
        const ReservationList = selectedReservations.map(r => `${r.id} - ${r.username} - ${r.date}`).join("<br>");
        deleteMessage.innerHTML = `確定要刪除以下訂單嗎？<br><b>${ReservationList}</b>`;

        // 顯示 Modal
        deleteModal.show();

        confirmDeleteButton.addEventListener("click", () => {
          fetch("reservations_delete-api.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json"
              },
              body: JSON.stringify({
                ids: selectedReservations.map(r => r.id)
              }) // ✅ 只傳送 ID

            })
            .then(response => response.json())
            .then(data => {

              if (data.success) {
                selectedReservations.forEach(r => {
                  const row = document.querySelector(`input[value='${r.id}']`).closest("tr");
                  // row.style.transition = "opacity 0.5s";
                  // row.style.opacity = "0";

                  // setTimeout(() => {
                  row.remove();
                  // }, 500); // 
                });
                window.location.href = `reservations_list.php?page=${page}&search=${encodeURIComponent(search)}`;
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