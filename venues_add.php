<?php
include __DIR__ . '/parts/init.php';

$title = '新增場館';
$pageName = 'venues_add';

// 取得所有地區
$locStmt = $pdo->query("SELECT id, name FROM locations");
$locations = $locStmt->fetchAll();
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
          <h5 class="card-title">新增場館</h5>
          <form name="venueForm" id="venueForm" novalidate>
            <div class="mb-3">
              <label for="name" class="form-label">場館名稱<span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="location" class="form-label">地區<span class="text-danger">*</span></label>
              <select class="form-select" id="location_id" name="location_id">
                <option value="">請選擇地區</option>
                <?php foreach ($locations as $loc): ?>
                  <option value="<?= $loc['id'] ?>"><?= $loc['name'] ?></option>
                <?php endforeach; ?>
              </select>

              <div class="form-text text-danger"></div>
            </div>
            <button type="submit" class="btn btn-primary me-2">新增</button>
            <a class="btn btn-secondary " href="venues_list.php" role="button">取消</a>
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
        <a href="venues_list.php" class="btn btn-primary">回列表頁</a>
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
    const form = document.getElementById("venueForm");
    const nameField = document.getElementById("name");
    const locationField = document.getElementById("location_id");
    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
      nameField.classList.remove("border-danger");
      locationField.classList.remove("border-danger");

      // 📝 表單欄位檢查
      let isPass = true;

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

      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);

      console.log([...fd.entries()]);
      fetch("venues_add-api.php", {
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