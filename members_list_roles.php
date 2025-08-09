<?php
include __DIR__ . '/parts/init.php'; # 初始化頁面
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

$title = '會員列表';
$pageName = 'members_list';


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
  $searchSql = "WHERE members.username LIKE ? OR members.email LIKE ? OR members.full_name LIKE ?";
  $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(1) FROM `members` $searchSql";
$stmt = $pdo->prepare($t_sql);
$stmt->execute($params);
$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];


# 預設值
$totalPages = 0;
$rows = [];

// 計算總頁數
$totalPages = ceil($totalRows / $perPage);

// 確保 page 不超過最大頁數
if ($totalRows > 0) {
  if ($page > $totalPages && $totalPages > 0) {
    header("Location: ?page={$totalPages}");
    exit;
  }
  // $sql = sprintf("SELECT * FROM `members` ORDER BY id DESC LIMIT %s, %s", ($page - 1) * $perPage, $perPage);
  // $rows = $pdo->query($sql)->fetchAll();
}

// 取得符合條件的場館與地區列表
$sql = "SELECT *
        FROM members
        $searchSql 
        ORDER BY id  
        DESC
        LIMIT " . (($page - 1) * $perPage) . ", " . $perPage;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

?>
<?php include __DIR__ . '/parts/html-head.php' ?>
<style>
  .search-field b {
    color: #dc3545;
  }

  .status th {
    word-break: break-all;
  }

  /* tr{word-break: break-all;} */
  #member_tr {
    font-size: 12px;
    vertical-align: middle;
  }

  #member_tr th:nth-child(3) {
    word-break: break-all;
  }

  .email td {
    word-break: break-all;
  }

  .avatar td {
    word-break: break-all;
  }

  td:nth-child(2),
  td:nth-child(3),
  td:nth-child(8) {
    word-break: break-all;
    white-space: normal;
  }

  td:nth-child(4) {
    white-space: nowrap;
  }

  td {
    text-align: center;
    vertical-align: middle;
  }
</style>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>
<div class="container-fluid px-3">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-11 col-md-10">

      <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2 mb-3">
        <!-- 新增按鈕 -->
        <!-- <a href="members_add.php" class="btn btn-outline-primary" style="white-space: nowrap;">新增</a> -->
        <!-- 搜尋框區塊 -->
        <form class="d-flex align-items-center mb-0" role="search" method="GET" action="members_list_roles.php">
          <input class="form-control me-2" type="search" name="search" placeholder="輸入信箱或帳號" value="<?= htmlentities($search) ?>" aria-label="Search" />
          <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
        </form>
      </div>

      <!-- 主內容表格區 -->
      <div class="mb-3">
        <div class="table-responsive">
          <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
            <thead>
              <tr id="member_tr">
                <th>使用者主鍵 ID</th>
                <th>帳號名稱</th>
                <th>信箱</th>
                <!-- <th>密碼</th> -->
                <!-- <th>姓名</th>
            <th>聯絡電話</th>
            <th>性別</th>
            <th>出生日期</th>
            <th>大頭貼圖片連結</th>
            <th>收件地址</th>
            <th class="status">是否啟用帳號</th>
            <th class="status">是否驗證信箱</th>
            <th class="status">是否驗證電話</th> -->
                <th>使用者角色</th>
                <th>編輯</th>
                <th>刪除</th>
              </tr>
            </thead>
            <tbody>
              <?php $genderMap = [
                0 => '女',
                1 => '男',
                NULL => '不透露'
              ]; ?>
              <?php $varifyMap = [
                0 => '否',
                1 => '是'
              ]; ?>
              <?php foreach ($rows as $r): ?>
                <tr>
                  </td>
                  <td class="members_id"><?= $r['id'] ?></td>
                  <td class="username"><?= htmlentities($r['username']) ?></td>
                  <td class="email"><?= $r['email'] ?></td>
                  <!-- <td><?= $r['password'] ?></td> -->
                  <!-- <td class="full_name"><?= $r['full_name'] ?></td>
              <td class="phone_number"><?= $r['phone_number'] ?></td>
              <td class="gender"><?= $genderMap[$r['gender']] ?></td>
              <td class="birth_date"><?= $r['birth_date'] ?></td>
              <td class="avatar"><?= $r['avatar_url'] ?></td>
              <td class="address"><?= htmlentities($r['address']) ?></td>
              <td class="is_active"><?= $varifyMap[$r['is_active']] ?></td>
              <td class="e_verified"><?= $varifyMap[$r['email_verified']] ?></td>
              <td class="p_verified"><?= $varifyMap[$r['phone_verified']] ?></td> -->
                  <td class="role"><?= $r['role'] ?></td>
                  <td>
                    <a href="members_roles_edit.php?id=<?= $r['id'] ?>">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                  </td>
                  <td>
                    <a href="#" class="delete-btn text-danger">
                      <i class="fa-solid fa-trash-can"></i>
                    </a>
                  </td>
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
        const name = deleteRow.querySelector(".username").innerHTML;

        // 更新 Modal 的訊息
        deleteMessage.innerHTML = `確定要刪除 <b>${deleteId}-${name} 的資料嗎？`;

        // 顯示 Modal
        deleteModal.show();
      });
    });

    confirmDeleteButton.addEventListener("click", () => {
      if (deleteId) {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || 1;
        const search = urlParams.get("search") || "";

        fetch(`members_delete-api.php?page=${page}&search=${encodeURIComponent(search)}`, {
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
              setTimeout(() => deleteRow.remove(), 300); // 添加淡出動畫

              // 刪除後更新 URL，停留在當前分頁
              window.location.href = `members_list_roles.php?page=${page}&search=${encodeURIComponent(search)}`;
            } else {
              alert("刪除失敗：" + data.error);
            }
            deleteModal.hide(); // 隱藏 Modal
          })
          .catch(error => console.error("刪除時發生錯誤", error));
      }
    });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>