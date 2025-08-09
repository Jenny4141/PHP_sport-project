<?php
include __DIR__ . '/parts/init.php';

$title = '編輯時間';
$pageName = 'timeslots_edit';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
  # 沒有給 PK 就直接回列表頁
  header('Location: timeslots_list.php');
  exit;
}

$sql = "SELECT * FROM time_slots WHERE id=$id";
$r = $pdo->query($sql)->fetch();
if (empty($r)) {
  # 沒有這筆資料
  header('Location: timeslots_list.php');
  exit;
}

// 取得所有時間段
$periodStmt = $pdo->query("SELECT id, name FROM time_periods");
$timePeriods = $periodStmt->fetchAll();
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
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">編輯時間</h5>
          <form id="venueForm" name="form1" novalidate>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="mb-3">
              <label for="" class="form-label">編號</label>
              <input type="text" class="form-control"
                value="<?= $r['id'] ?>" disabled>
            </div>
            <div class="mb-3">
              <label for="start_time" class="form-label">開始時間<span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="start_time" name="start_time"
                value="<?= htmlentities($r['start_time']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="end_time" class="form-label">結束時間<span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="end_time" name="end_time"
                value="<?= htmlentities($r['end_time']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="period_id" class="form-label">時間區段<span class="text-danger">*</span></label>
              <select class="form-select" id="period_id" name="period_id">
                <option value="">請選擇時間區段</option>
                <?php foreach ($timePeriods as $period): ?>
                  <option value="<?= $period['id'] ?>" <?= ($r['period_id'] == $period['id']) ? 'selected' : '' ?>>
                    <?= $period['name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <button type="submit" class="btn btn-primary">修改</button>
            <a class="btn btn-secondary " href="timeslots_list.php" role="button">取消</a>
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
    if (referrerURL.includes("timeslots_list.php")) {
      const urlParams = new URL(referrerURL).searchParams;
      const page = urlParams.get("page") || 1; // 預設回到第 1 頁
      const search = urlParams.get("search") || "";

      // 設定 "回列表頁" 按鈕的 URL，保留 page 和 search 參數
      backToListBtn.href = `timeslots_list.php?page=${page}&search=${encodeURIComponent(search)}`;
    } else {
      // 如果沒有 referrer，則回到一般的列表頁
      backToListBtn.href = "timeslots_list.php";
    }
    // ✅ 表單編輯模塊
    const form = document.getElementById("venueForm");
    const startField = document.getElementById("start_time");
    const endField = document.getElementById("end_time");
    const periodField = document.getElementById("period_id");
    const modal = new bootstrap.Modal(document.getElementById("exampleModal"));
    const modalBody = document.querySelector(".modal-body");

    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 清空錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = '');
      startField.classList.remove("border-danger");
      endField.classList.remove("border-danger");
      periodField.classList.remove("border-danger");

      // 📝 表單欄位檢查
      let isPass = true;

      if (startField.value.trim() === "") {
        isPass = false;
        startField.nextElementSibling.textContent = "請填入開始時間";
        startField.classList.add("border-danger");
      }

      if (endField.value.trim() === "") {
        isPass = false;
        endField.nextElementSibling.textContent = "請填入結束時間";
        endField.classList.add("border-danger");
      }

      if (periodField.value.trim() === "") {
        isPass = false;
        periodField.nextElementSibling.textContent = "請選擇時間區段";
        periodField.classList.add("border-danger");
      }

      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);
      fetch("timeslots_edit-api.php", {
          method: "POST",
          headers: {
            "Accept": "application/json"
          },
          body: fd
        })
        .then(response => response.json())
        .then(data => {
          console.log(data);
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