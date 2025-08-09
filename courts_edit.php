<?php
include __DIR__ . '/parts/init.php';

$title = '編輯場地';
$pageName = 'courts_edit';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
  # 沒有給 PK 就直接回列表頁
  header('Location: courts_list.php');
  exit;
}

$sql = "SELECT * FROM courts WHERE id=$id";
$r = $pdo->query($sql)->fetch();
if (empty($r)) {
  # 沒有這筆資料
  header('Location: courts_list.php');
  exit;
}

// 取得所有場館
$venueStmt = $pdo->query("SELECT id, name FROM venues");
$venues = $venueStmt->fetchAll();

// 取得所有運動類型
$sportStmt = $pdo->query("SELECT id, name FROM sports");
$sports = $sportStmt->fetchAll();
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

<!-- 新增場地輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">編輯場地</h5>
          <form name="courtForm" id="courtForm" novalidate>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="mb-3">
              <label for="" class="form-label">編號</label>
              <input type="text" class="form-control"
                value="<?= $r['id'] ?>" disabled>
            </div>
            
            <div class="mb-3">
              <label for="venue_id" class="form-label">場館<span class="text-danger">*</span></label>
              <select class="form-select" id="venue_id" name="venue_id">
                <option value="">請選擇場館</option>
                <?php foreach ($venues as $venue): ?>
                  <option value="<?= $venue['id'] ?>" <?= ($r['venue_id'] == $venue['id']) ? 'selected' : '' ?>>
                    <?= $venue['name'] ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="sport_id" class="form-label">運動類型<span class="text-danger">*</span></label>
              <select class="form-select" id="sport_id" name="sport_id">
                <option value="">請選擇運動類型</option>
                <?php foreach ($sports as $sport): ?>
                  <option value="<?= $sport['id'] ?>" <?= ($r['sport_id'] == $sport['id']) ? 'selected' : '' ?>>
                    <?= $sport['name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">場地名稱</label>
              <input type="text" class="form-control" id="name" name="name" value="<?= $r['name'] ?>">
              <div class="form-text text-danger"></div>
            </div>
            <button type="submit" class="btn btn-primary me-2">修改</button>
            <a class="btn btn-secondary " href="courts_list.php" role="button">取消</a>
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
          成功編輯資料！
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
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // ✅ 回列表頁模塊
    const backToListBtn = document.getElementById("backToList");

    // 取得來自 referrer 的 URL
    const referrerURL = document.referrer;
    if (referrerURL.includes("courts_list.php")) {
      const urlParams = new URL(referrerURL).searchParams;
      const page = urlParams.get("page") || 1; // 預設回到第 1 頁
      const search = urlParams.get("search") || "";

      // 設定 "回列表頁" 按鈕的 URL，保留 page 和 search 參數
      backToListBtn.href = `courts_list.php?page=${page}&search=${encodeURIComponent(search)}`;
    } else {
      // 如果沒有 referrer，則回到一般的列表頁
      backToListBtn.href = "courts_list.php";
    }
    // ✅ 表單編輯模塊
    const modal = new bootstrap.Modal(document.getElementById("exampleModal"));
    const modalBody = document.querySelector(".modal-body");
    const form = document.getElementById("courtForm");
    const nameField = document.getElementById("name");
    const venueField = document.getElementById("venue_id");
    const sportField = document.getElementById("sport_id");
    
    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
      nameField.classList.remove("border-danger");
      venueField.classList.remove("border-danger");
      sportField.classList.remove("border-danger");

      // 📝 表單欄位檢查
      let isPass = true;

      if (nameField.value.trim() === "") {
        isPass = false;
        nameField.nextElementSibling.innerHTML = '請填入名稱';
        nameField.classList.add('border-danger');
      }
      if (venueField.value.trim() === "") {
        isPass = false;
        venueField.nextElementSibling.innerHTML = '請選擇場館';
        venueField.classList.add('border-danger');
      }
      if (sportField.value.trim() === "") {
        isPass = false;
        sportField.nextElementSibling.innerHTML = '請選擇運動';
        sportField.classList.add('border-danger');
      }

      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);

      fetch("courts_edit-api.php", {
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