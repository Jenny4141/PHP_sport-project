<?php
include __DIR__ . '/parts/init.php';
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

$title = '編輯會員角色';
$pageName = 'members_roles_edit';


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
  # 沒有給 PK 就直接回列表頁
  header('Location: members_list.php');
  exit;
}

$sql = "SELECT * FROM members WHERE id=$id";
$r = $pdo->query($sql)->fetch();
if (empty($r)) {
  # 沒有這筆資料
  header('Location: members_list.php');
  exit;
}

// 取得所有地區
// $locStmt = $pdo->query("SELECT id, name FROM locations");
// $locations = $locStmt->fetchAll();
// 
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>
<style>
  .modal-body .alert-success {
    display: none;
  }

  .modal-body .alert-warning {
    display: block;
  }

  .modal-body.success .alert-success {
    display: block;
  }

  .modal-body.success .alert-warning {
    display: none;
  }
</style>

<!-- 新增輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6 p-2">
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">編輯</h5>
          <form id="memberForm" name="memberForm" novalidate>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="mb-3">
              <label for="" class="form-label">編號</label>
              <input type="text" class="form-control"
                value="<?= $r['id'] ?>" disabled>
            </div>
            <div class="mb-3">
              <input type="hidden" name="username"
                value="<?= htmlentities($r['username']) ?>">
              <label for="" class="form-label">名稱</label>
              <input type="text" class="form-control"
                value="<?= htmlentities($r['username']) ?>" disabled>
            </div>
            <label for="role" class="form-label">角色</label>
            <select class="form-select" id="role" name="role">
              <option value="user" <?= $r['role'] === 'user' ? 'selected' : '' ?>>user</option>
              <option value="admin" <?= $r['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
              <!-- <option value="0" <?= $r[''] === '0' ? 'selected' : '' ?>>女</option> -->
            </select>
            <button type="submit" class="btn btn-primary mt-4">修改</button>
            <a class="btn btn-secondary mt-4" href="members_list_roles.php" role="button">取消</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">編輯結果</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-success" role="alert">
          成功編輯資料！
        </div>
        <div class="alert alert-warning" role="alert">
          請填入正確資料
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續新增</button>
        <a id="backToList" class="btn btn-primary">回列表頁</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // ✅ 回列表頁模塊
    const backToListBtn = document.getElementById("backToList");

    // 取得來自 referrer 的 URL
    const referrerURL = document.referrer;
    if (referrerURL.includes("members_list_roles.php")) {
      const urlParams = new URL(referrerURL).searchParams;
      const page = urlParams.get("page") || 1; // 預設回到第 1 頁
      const search = urlParams.get("search") || "";

      // 設定 "回列表頁" 按鈕的 URL，保留 page 和 search 參數
      backToListBtn.href = `members_list_roles.php?page=${page}&search=${encodeURIComponent(search)}`;
    } else {
      // 如果沒有 referrer，則回到一般的列表頁
      backToListBtn.href = "members_list_roles.php";
    }
    // ✅ 表單編輯模塊
    const form = document.getElementById("memberForm");
    const roleField = document.getElementById("role");

    const modal = new bootstrap.Modal(document.getElementById("exampleModal"));

    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 清空錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = '');
      roleField.classList.remove("border-danger");
      // 📝 表單欄位檢查
      let isPass = true;
      if (roleField.value.trim() === "") {
        isPass = false;
        roleField.nextElementSibling.innerHTML = '請選擇角色';
        roleField.classList.add('border-danger');
      }

      if (!isPass) {
        return;
      }

      // 送出表單資料
      const fd = new FormData(form);
      fetch("members_roles_edit-api.php", {
          method: "POST",
          // headers: {
          //   "Accept": "application/json"
          // },
          body: fd
        })
        .then(response => response.json())
        .then(data => {
          console.log(data);
          const modalBody = document.querySelector(".modal-body");

          if (data.success) {
            modalBody.classList.add("success");
            // form.reset(); // 清空表單
            modal.show();
          } else {
            modalBody.classList.remove("success");
          }

        })
        // .catch(error => console.error("表單提交錯誤:", error));
        .catch(console.warn)
    });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>