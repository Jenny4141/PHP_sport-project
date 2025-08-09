<?php
include __DIR__ . '/parts/init.php';

$title = '新增時間';
$pageName = 'timeslots_add';

// 取得所有時段類別
$periodStmt = $pdo->query("SELECT id, name FROM time_periods");
$timePeriods = $periodStmt->fetchAll();
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<!-- 新增輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">新增時間</h5>
          <form name="timeSlotForm" id="timeSlotForm" novalidate>
            <div class="mb-3">
              <label for="start_time" class="form-label">開始時間<span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="start_time" name="start_time">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="end_time" class="form-label">結束時間<span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="end_time" name="end_time">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="period_id" class="form-label">時段類別<span class="text-danger">*</span></label>
              <select class="form-select" id="period_id" name="period_id">
                <option value="">請選擇時段</option>
                <?php foreach ($timePeriods as $period): ?>
                  <option value="<?= $period['id'] ?>"><?= $period['name'] ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <button type="submit" class="btn btn-primary me-2">新增</button>
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
        <h1 class="modal-title fs-5" id="exampleModalLabel">新增結果</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-success" role="alert">
          成功新增資料！
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續新增</button>
        <a href="timeslots_list.php" class="btn btn-primary">回列表頁</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // ✅ 表單新增模塊
    // 彈出視窗實例化
    const modal = new bootstrap.Modal('#exampleModal')
    // 獲取表單欄位
    const form = document.getElementById("timeSlotForm");
    const startTimeField = document.getElementById("start_time");
    const endTimeField = document.getElementById("end_time");
    const periodField = document.getElementById("period_id");
    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
      startTimeField.classList.remove("border-danger");
      endTimeField.classList.remove("border-danger");
      periodField.classList.remove("border-danger");

      // 📝 表單欄位檢查
      let isPass = true;

      if (startTimeField.value.trim() === "") {
        isPass = false;
        startTimeField.nextElementSibling.innerHTML = '請填入開始時間';
        startTimeField.classList.add('border-danger');
      }
      if (endTimeField.value.trim() === "") {
        isPass = false;
        endTimeField.nextElementSibling.innerHTML = '請填入結束時間';
        endTimeField.classList.add('border-danger');
      }
      if (periodField.value.trim() === "") {
        isPass = false;
        periodField.nextElementSibling.innerHTML = '請選擇時段類別';
        periodField.classList.add('border-danger');
      }

      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);

      console.log([...fd.entries()]);
      fetch("timeslots_add-api.php", {
          method: "POST",
          headers: {
            "Accept": "application/json"
          },
          body: fd
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            modal.show();
          } else {
            alert("新增失敗：" + data.error);
          }
        })
        .catch(error => console.error("表單提交錯誤:", error));
    });


  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>