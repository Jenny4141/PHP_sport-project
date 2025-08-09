<?php
include __DIR__ . '/parts/init.php';

$title = '批量編輯價格';
$pageName = 'courts_timeslots_bulk_edit';

// 取得所有場館
$venueStmt = $pdo->query("SELECT id, name FROM venues");
$venues = $venueStmt->fetchAll();

// 取得所有運動類型
$sportStmt = $pdo->query("SELECT id, name FROM sports");
$sports = $sportStmt->fetchAll();

// 取得所有時間段
$timeSlotStmt = $pdo->query("SELECT id, start_time, end_time FROM time_slots");
$timeSlots = $timeSlotStmt->fetchAll();

// 取得所有時間區段
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

<!-- 新增場地時間輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">批量編輯價格</h5>
          <form name="bulkEditForm" id="bulkEditForm" novalidate>
            <div class="mb-3">
              <label class="form-label">修改範圍<span class="text-danger">*</span></label>
              <select class="form-select" name="update_type" id="updateTypeSelect">
                <option value="venue" selected>場館</option>
                <option value="sport">運動類型</option>
                <option value="time_range">時間範圍</option>
                <option value="time_period">時間區段</option>
              </select>
            </div>

            <div class="mb-3" id="venueSection">
              <label for="venue_id" class="form-label">場館<span class="text-danger">*</span></label>
              <select class="form-select" id="venue_id" name="venue_id">
                <option value="">請選擇場館</option>
                <?php foreach ($venues as $venue): ?>
                  <option value="<?= $venue['id'] ?>"><?= $venue['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3" id="sportSection" style="display: none;">
              <label for="sport_id" class="form-label">運動類型<span class="text-danger">*</span></label>
              <select class="form-select" id="sport_id" name="sport_id">
                <option value="">請選擇運動類型</option>
                <?php foreach ($sports as $sport): ?>
                  <option value="<?= $sport['id'] ?>"><?= $sport['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3" id="timeRangeSection" style="display: none;">
              <label for="start_time" class="form-label">開始時間<span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="start_time" name="start_time">
              <label for="end_time" class="form-label">結束時間<span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="end_time" name="end_time">
            </div>

            <div class="mb-3" id="timePeriodSection" style="display: none;">
              <label for="period_id" class="form-label">時間區段<span class="text-danger">*</span></label>
              <select class="form-select" id="period_id" name="period_id">
                <option value="">請選擇時間區段</option>
                <?php foreach ($timePeriods as $period): ?>
                  <option value="<?= $period['id'] ?>"><?= $period['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="price" class="form-label">新價格 (NTD)<span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="price" name="price">
            </div>

            <button type="submit" class="btn btn-primary me-2">批量修改</button>
            <a class="btn btn-secondary " href="courts_timeslots_list.php" role="button">取消</a>
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
    // ✅ 選擇某種方式其他隱藏模塊
    const updateTypeRadios = document.querySelectorAll("input[name='update_type']");
    const venueSection = document.getElementById("venueSection");
    const sportSection = document.getElementById("sportSection");
    const timeRangeSection = document.getElementById("timeRangeSection");
    const timePeriodSection = document.getElementById("timePeriodSection");

    updateTypeSelect.addEventListener("change", () => {
      const selectedValue = updateTypeSelect.value;
      venueSection.style.display = (selectedValue === "venue") ? "block" : "none";
      sportSection.style.display = (selectedValue === "sport") ? "block" : "none";
      timeRangeSection.style.display = (selectedValue === "time_range") ? "block" : "none";
      timePeriodSection.style.display = (selectedValue === "time_period") ? "block" : "none";
    });
    // ✅ 回列表頁模塊
    const backToListBtn = document.getElementById("backToList");

    // 取得來自 referrer 的 URL
    const referrerURL = document.referrer;
    if (referrerURL.includes("courts_timeslots_list.php")) {
      const urlParams = new URL(referrerURL).searchParams;
      const page = urlParams.get("page") || 1; // 預設回到第 1 頁
      const search = urlParams.get("search") || "";

      // 設定 "回列表頁" 按鈕的 URL，保留 page 和 search 參數
      backToListBtn.href = `courts_timeslots_list.php?page=${page}&search=${encodeURIComponent(search)}`;
    } else {
      // 如果沒有 referrer，則回到一般的列表頁
      backToListBtn.href = "courts_timeslots_list.php";
    }
    // ✅ 表單編輯模塊
    // 彈出視窗實例化
    const modal = new bootstrap.Modal(document.getElementById("exampleModal"));
    const modalBody = document.querySelector(".modal-body");
    // 獲取表單欄位
    const form = document.getElementById("bulkEditForm");
    /* const courtField = document.getElementById("court_id");
    const timeField = document.getElementById("time_slot_id");
    const priceField = document.getElementById("price"); */
    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      /* // 重置錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
      courtField.classList.remove("border-danger");
      timeField.classList.remove("border-danger");
      priceField.classList.remove("border-danger");

      // 📝 表單欄位檢查
      let isPass = true;

      if (courtField.value.trim() === "") {
        isPass = false;
        courtField.nextElementSibling.innerHTML = '請填入場地';
        courtField.classList.add('border-danger');
      }
      if (timeField.value.trim() === "") {
        isPass = false;
        timeField.nextElementSibling.innerHTML = '請選擇時間';
        timeField.classList.add('border-danger');
      }
      if (priceField.value.trim() === "") {
        isPass = false;
        priceField.nextElementSibling.innerHTML = '請填入價格';
        priceField.classList.add('border-danger');
      }

      if (!isPass) return; */

      // 送出表單資料
      const fd = new FormData(form);

      console.log([...fd.entries()]);
      fetch("courts_timeslots_bulk_edit-api.php", {
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