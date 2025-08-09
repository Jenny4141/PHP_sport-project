<?php
include __DIR__ . '/parts/init.php';

$title = '新增教練人員';
$pageName = 'coaches_add';


// 從 specialties 表單撈出所有資料
$sql = "SELECT specialty_id, specialty_name FROM specialties";
$stmt = $pdo->query($sql);
$specialties = $stmt->fetchAll();
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<!-- 新增輸入 -->
<div class="container-fluid px-3">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">新增教練人員</h5>
          <form name="coachForm" id="coachForm" novalidate>
            <div class="mb-3">
              <label for="name" class="form-label">姓名</label>
              <input type="text" class="form-control" id="name" name="name">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">電話</label>
              <input type="text" class="form-control" id="phone" name="phone">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">信箱</label>
              <input type="text" class="form-control" id="email" name="email">
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
                <input type="text" class="form-control" id="bio" name="bio">
                <div class="form-text text-danger"></div>
              </div>

            </div>
            <button type="submit" class="btn btn-primary">新增</button>
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
        <a href="coaches_list.php" class="btn btn-primary">回列表頁</a>
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
      fetch("coaches_add-api.php", {
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