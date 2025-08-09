<?php
include __DIR__ . '/parts/init.php';

$title = '新增隊伍';
$pageName = 'teams_add';


// 取得下拉式選單及其類型內容
$sportStmt = $pdo->query("SELECT id, name FROM sports");
$sports = $sportStmt->fetchAll();

$courtsStmt = $pdo->query("SELECT c.id, c.name, s.id AS sport_id, s.name AS sport_name FROM courts c JOIN sports s ON c.sport_id = s.id ORDER BY c.name ASC");
$courts = $courtsStmt->fetchAll();

$levelStmt = $pdo->query("SELECT id, name FROM level");
$levels = $levelStmt->fetchAll();

?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">創建隊伍</h5>
          <form name="courtForm" id="courtForm" novalidate>
            <div class="mb-3">
              <label for="name" class="form-label">輸入隊伍名稱</label>
              <input type="text" class="form-control" id="name" name="name">
              <div class="form-text text-danger" data-field="name"></div>
            </div>
            <div class="mb-3">
              <label for="courts_id" class="form-label">團練場地</label>
              <select class="form-select" id="courts_id" name="courts_id">
                <option value="">請選擇場地</option>
                <?php foreach ($courts as $court): ?>
                  <option value="<?= $court['id'] ?>" data-sport-id="<?= $court['sport_id'] ?>" data-sport-name="<?= htmlentities($court['sport_name']) ?>">
                    <?= htmlentities($court['name']) ?> (<?= htmlentities($court['sport_name']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger" data-field="courts_id"></div>
            </div>
            <div class="mb-3">
              <label for="sport_name_display" class="form-label">運動種類</label>
              <input type="text" class="form-control" id="sport_name_display" readonly>
              <input type="hidden" name="sport_id" id="sport_id_hidden">
              <div class="form-text text-danger" data-field="sport_id"></div>
            </div>
            <div class="mb-3">
              <label for="level_id" class="form-label">隊伍級別</label>
              <select class="form-select" id="level_id" name="level_id">
                <option value="">請選擇隊伍級別</option>
                <?php foreach ($levels as $level_item): ?>
                  <option value="<?= $level_item['id'] ?>"><?= htmlentities($level_item['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger" data-field="level_id"></div>
            </div>
            <div class="d-flex">
              <button type="submit" class="btn btn-primary">新增</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

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
        <a href="teams_list.php" class="btn btn-primary">回列表頁</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const modal = new bootstrap.Modal('#exampleModal');
    const form = document.getElementById("courtForm");
    const nameField = document.getElementById("name");
    const courtField = document.getElementById("courts_id"); // 已是正確的 ID
    const levelField = document.getElementById("level_id");
    const sportNameDisplay = document.getElementById('sport_name_display');
    const sportIdHidden = document.getElementById('sport_id_hidden');

    // 監聽團練場地選擇的變化
    courtField.addEventListener('change', () => {
      const selectedOption = courtField.options[courtField.selectedIndex];
      // **從 selectedOption 讀取 data-屬性，確保存在且有值**
      const sportId = selectedOption.dataset.sportId;
      const sportName = selectedOption.dataset.sportName;

      if (selectedOption.value !== "") { // 確保不是 "請選擇場地"
        sportNameDisplay.value = sportName || ''; // 如果 data-屬性不存在，則設定為空字串
        sportIdHidden.value = sportId || ''; // 如果 data-屬性不存在，則設定為空字串
      } else {
        sportNameDisplay.value = '';
        sportIdHidden.value = '';
      }
      // 清除運動種類的錯誤提示
      document.querySelector('[data-field="sport_id"]').innerText = '';
      sportNameDisplay.classList.remove("border-danger");
    });

    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
      nameField.classList.remove("border-danger");
      courtField.classList.remove("border-danger");
      levelField.classList.remove("border-danger");
      sportNameDisplay.classList.remove("border-danger");

      // 📝 表單欄位檢查
      let isPass = true;

      if (nameField.value.trim() === "") {
        isPass = false;
        nameField.nextElementSibling.innerHTML = '請填入名稱';
        nameField.classList.add('border-danger');
      }
      if (courtField.value.trim() === "") {
        isPass = false;
        courtField.nextElementSibling.innerHTML = '請選擇場地';
        courtField.classList.add('border-danger');
      }
      // 檢查隱藏的 sport_id 是否有值 (即運動種類是否已由場地選擇設定)
      if (sportIdHidden.value.trim() === "") {
        isPass = false;
        document.querySelector('[data-field="sport_id"]').innerHTML = '請選擇團練場地以自動設定運動種類';
        sportNameDisplay.classList.add('border-danger'); // 標記顯示欄位為錯誤
      }

      if (levelField.value.trim() === "") {
        isPass = false;
        levelField.nextElementSibling.innerHTML = '請選擇階級';
        levelField.classList.add('border-danger');
      }
      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);

      console.log([...fd.entries()]); // 調試用，查看 FormData 內容

      fetch("teams_add-api.php", {
          method: "POST",
          headers: {
            "Accept": "application/json"
          },
          body: fd
        })
        .then(response => response.json())
        .then(data => {
          // === 從這裡開始替換 ===
          if (data.success) {
            // 更新 Modal 腳部的連結
            const modalFooter = document.querySelector('#exampleModal .modal-footer');
            const continueBtn = modalFooter.querySelector('.btn-secondary');
            const backToListBtn = modalFooter.querySelector('a.btn-primary');

            continueBtn.textContent = '新增隊伍成員';

            continueBtn.onclick = () => { // 直接覆寫 onclick 屬性
              if (data.team_id) {
                window.location.href = `tmember_add.php?team_id=${data.team_id}`; // 跳轉到新增成員頁面
              } else {
                modal.hide(); // 如果沒有 team_id，就關閉 Modal
              }
            };


            // "回列表頁" 則跳轉回隊伍列表
            backToListBtn.href = `teams_list.php`;

            modal.show();
          } else {
            // 錯誤訊息顯示邏輯
            if (data.errors) {
              let errorMessages = "隊伍新增失敗，請檢查以下欄位：\n";
              for (let field in data.errors) {
                const errorElement = document.querySelector(`[data-field="${field}"]`);
                if (errorElement) {
                  errorElement.innerHTML = data.errors[field];
                  // 找到對應的 input 欄位並加上 border-danger
                  let inputElement = form.querySelector(`[name="${field}"]`);
                  if (field === 'sport_id') { // 特殊處理 sport_id，因為它是隱藏欄位
                    inputElement = sportNameDisplay; // 將錯誤樣式加到顯示的 input 上
                  } else if (field === 'courts_id') { // 特殊處理 courts_id，因為它是 select
                    inputElement = courtField;
                  } else if (field === 'level_id') { // 特殊處理 level_id，因為它是 select
                    inputElement = levelField;
                  }
                  if (inputElement) {
                    inputElement.classList.add('border-danger');
                  }
                }
                errorMessages += `- ${data.errors[field]}\n`; // 將錯誤訊息加到 alert 內容
              }
              alert(errorMessages); // 顯示詳細錯誤訊息
            } else {
              alert("隊伍新增失敗：" + (data.error || '未知錯誤'));
            }
          }
          // === 到這裡結束替換 ===
        })
        .catch(error => console.error("表單提交錯誤:", error));
    });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>