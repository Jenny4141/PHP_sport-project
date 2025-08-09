<?php
include __DIR__ . '/parts/init.php';

$title = '新增場地';
$pageName = 'courts_add';

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

<!-- 新增場地輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">新增場地</h5>
          <form name="courtForm" id="courtForm" novalidate>

            <div class="mb-3">
              <label for="venue_id" class="form-label">場館<span class="text-danger">*</span></label>
              <select class="form-select" id="venue_id" name="venue_id">
                <option value="">請選擇場館</option>
                <?php foreach ($venues as $venue): ?>
                  <option value="<?= $venue['id'] ?>"><?= $venue['name'] ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="sport_id" class="form-label">運動類型<span class="text-danger">*</span></label>
              <select class="form-select" id="sport_id" name="sport_id">
                <option value="">請選擇運動類型</option>
                <?php foreach ($sports as $sport): ?>
                  <option value="<?= $sport['id'] ?>"><?= $sport['name'] ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="name" class="form-label">場地名稱</label>
              <input type="text" class="form-control" id="name" name="name">
              <div class="form-text text-danger"></div>
            </div>

            <button type="submit" class="btn btn-primary me-2">新增</button>
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
          成功新增資料！
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續新增</button>
        <a href="courts_list.php" class="btn btn-primary">回列表頁</a>
      </div>
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
    const form = document.getElementById("courtForm");
    const venueField = document.getElementById("venue_id");
    const sportField = document.getElementById("sport_id");
    const nameField = document.getElementById("name");

    // ✅ 自動生成場地名稱
    function updateCourtName() {
      const venueName = venueField.options[venueField.selectedIndex]?.text || "";
      const sportName = sportField.options[sportField.selectedIndex]?.text || "";
      nameField.value = venueName && sportName ? `${venueName}_${sportName}` : "";
    }

    venueField.addEventListener("change", updateCourtName);
    sportField.addEventListener("change", updateCourtName);

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

      console.log([...fd.entries()]);
      fetch("courts_add-api.php", {
          method: "POST",
          headers: {
            "Accept": "application/json"
          },
          body: fd
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            nameField.value = data.court_name;
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