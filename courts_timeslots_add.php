<?php
include __DIR__ . '/parts/init.php';

$title = '新增價格';
$pageName = 'courts_timeslots_add';

// 取得所有場地
$courtStmt = $pdo->query("SELECT id, name FROM courts");
$courts = $courtStmt->fetchAll();

// 取得所有時間段
$timeSlotStmt = $pdo->query("SELECT id, start_time, end_time FROM time_slots");
$timeSlots = $timeSlotStmt->fetchAll();
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<!-- 新增場地時間輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">新增價格</h5>
          <form name="courtTimeForm" id="courtTimeForm" novalidate>
            <div class="mb-3">
              <label for="court_id" class="form-label">場地<span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-3" id="court_search" placeholder="輸入場地名稱">
              <select class="form-select" id="court_id" name="court_id">
                <option value="">請選擇場地</option>
                <?php foreach ($courts as $court): ?>
                  <option value="<?= $court['id'] ?>"><?= $court['name'] ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="time_slot_id" class="form-label">時間段<span class="text-danger">*</span></label>
              <select class="form-select" id="time_slot_id" name="time_slot_id">
                <option value="">請選擇時間段</option>
                <?php foreach ($timeSlots as $slot): ?>
                  <option value="<?= $slot['id'] ?>"><?= date('G:i', strtotime($slot['start_time'])) ?> - <?= date('G:i', strtotime($slot['end_time'])) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="price" class="form-label">價格 (NTD)<span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="price" name="price">
              <div class="form-text text-danger"></div>
            </div>
            <button type="submit" class="btn btn-primary me-2">新增</button>
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
          成功新增資料！
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續新增</button>
        <a href="courts_timeslots_list.php" class="btn btn-primary">回列表頁</a>
      </div>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // ✅ 可搜尋式選單
    const searchField = document.getElementById("court_search");
    const selectField = document.getElementById("court_id");

    searchField.addEventListener("input", () => {
      const keyword = searchField.value.toLowerCase();
      let hasResults = false;

      selectField.classList.remove("d-none");
      Array.from(selectField.options).forEach(option => {
        if (option.text.toLowerCase().includes(keyword)) {
          option.hidden = false;
          hasResults = true;
        } else {
          option.hidden = true;
        }
      });

      if (!hasResults) {
        selectField.classList.add("d-none");
      }
    });
    
    // ✅ 表單新增模塊
    // 彈出視窗實例化
    const modal = new bootstrap.Modal('#exampleModal')
    // 獲取表單欄位
    const form = document.getElementById("courtTimeForm");
    const courtField = document.getElementById("court_id");
    const timeField = document.getElementById("time_slot_id");
    const priceField = document.getElementById("price");
    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
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

      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);

      console.log([...fd.entries()]);
      fetch("courts_timeslots_add-api.php", {
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