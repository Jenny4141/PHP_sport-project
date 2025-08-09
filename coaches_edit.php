<?php
include __DIR__ . '/parts/init.php';

$title = '教練資料編輯';
$pageName = 'coaches_edit';

$id = isset($_GET['coach_id']) ? intval($_GET['coach_id']) : 0;

if ($id <= 0) {
  # 沒有給 PK 就直接回列表頁
  header('Location: coaches_list.php');
  exit;
}

$sql = "SELECT * FROM coaches WHERE coach_id=$id";
$r = $pdo->query($sql)->fetch();
if (empty($r)) {
  # 沒有這筆資料
  header('Location: coaches_list.php');
  exit;
}

// 從 specialties 表單撈出所有資料
$sql = "SELECT specialty_id, specialty_name FROM specialties";
$stmt = $pdo->query($sql);
$specialties = $stmt->fetchAll();
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
<div class="container-fluid px-3">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">編輯資料</h5>
          <form name="coachForm" id="coachForm" novalidate>
            <div class="mb-3">
              <label for="" class="form-label">編號</label>
              <input type="text" class="form-control"
                value="<?= $r['coach_id'] ?>" disabled>
              <input type="hidden" name="coach_id" value="<?= $r['coach_id'] ?>">
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">姓名</label>
              <input type="text" class="form-control" id="name" name="name" value="<?= htmlentities($r['coachname_id']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">電話</label>
              <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlentities($r['phone']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">信箱</label>
              <input type="text" class="form-control" id="email" name="email" value="<?= htmlentities($r['email']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="specialty" class="form-label">專長</label>
              <select class="form-select" id="specialty" name="specialty">
                <option value="">請選擇專長</option>
                <?php foreach ($specialties as $spec): ?>
                  <option value="<?= $spec['specialty_id'] ?>">
                    <?= htmlentities($spec['specialty_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
              <div class="mb-3">
                <label for="bio" class="form-label">簡介</label>
                <input type="text" class="form-control" id="bio" name="bio"value="<?= htmlentities($r['bio']) ?>">
                <div class="form-text text-danger"></div>
              </div>
              
            </div>
            <button type="submit" class="btn btn-primary">修改</button>
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
        <h1 class="modal-title fs-5" id="exampleModalLabel">新增結果</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-success" role="alert">
          成功新增資料！
        </div>
        <div class="alert alert-warning" role="alert">
          沒有資料修改
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
    if (referrerURL.includes("coaches_list.php")) {
      const urlParams = new URL(referrerURL).searchParams;
      const page = urlParams.get("page") || 1; // 預設回到第 1 頁
      const search = urlParams.get("search") || "";

      // 設定 "回列表頁" 按鈕的 URL，保留 page 和 search 參數
      backToListBtn.href = `coaches_list.php?page=${page}&search=${encodeURIComponent(search)}`;
    } else {
      // 如果沒有 referrer，則回到一般的列表頁
      backToListBtn.href = "coaches_list.php";
    }
    // ✅ 表單新增模塊
    // 彈出視窗實例化
    const modal = new bootstrap.Modal('#exampleModal')
    const modalBody = document.querySelector(".modal-body");
    // 獲取表單欄位
    const form = document.getElementById("coachForm");
    /* const nameField = document.getElementById("name");
    const locationField = document.getElementById("location_id"); */
    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
      /* nameField.classList.remove("border-danger");
      locationField.classList.remove("border-danger"); */

      // 📝 表單欄位檢查
      /* let isPass = true;

      if (nameField.value.trim() === "") {
        isPass = false;
        nameField.nextElementSibling.innerHTML = '請填入名稱';
        nameField.classList.add('border-danger');
      }
      if (locationField.value.trim() === "") {
        isPass = false;
        locationField.nextElementSibling.innerHTML = '請選擇地區';
        locationField.classList.add('border-danger');
      }

      if (!isPass) return; */

      // 送出表單資料
      const fd = new FormData(form);

      console.log([...fd.entries()]);
      fetch("coaches_edit-api.php", {
          method: "POST",
          headers: {
            "Accept": "application/json"
          },
          body: fd
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            modalBody.classList.add("success");
            form.reset(); // 清空表單
          } else {
            modalBody.classList.remove("success");
          }
          modal.show();
        })
        .catch(error => console.error("表單提交錯誤:", error));
    });


  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>