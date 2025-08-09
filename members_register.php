<?php
require __DIR__ . '/parts/db-connect.php';
$imageBasePath = dirname($_SERVER['PHP_SELF']) . '/db/product_images/';


$title = '註冊會員';
$pageName = 'members_register';



?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<!-- 新增輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card mb-4">
        <div class="card-body">
          <!-- <h5 class="card-title">新增會員</h5> -->
          <form name="memberForm" id="memberForm" novalidate>
            <div class="mb-3">
              <label for="username" class="form-label">帳號名稱<span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="username" name="username">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">電子郵箱<span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="email" name="email">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">密碼<span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="password" name="password">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="full_name" class="form-label">姓名<span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="full_name" name="full_name">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="phone_number" class="form-label">連絡電話</label>
              <input type="text" class="form-control" id="phone_number" name="phone_number">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="gender" class="form-label">性別</label>
              <select class="form-select" id="gender" name="gender">
                <option value="">不透露</option>
                <option value="1">男</option>
                <option value="0">女</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="birth_date" class="form-label">出生日期</label>
              <input type="date" class="form-control" id="birth_date" name="birth_date">
              <div class="form-text"></div>
            </div>
            <div class="mb-3">
              <label for="address" class="form-label">地址</label>
              <textarea class="form-control" id="address"
                name="address"></textarea>
            </div>
            <div class="form-text text-danger"></div>
            <div class="card mb-4">
              <div class="card-header">
                大頭貼圖片
              </div>
              <div class="card-body ">
                <label for="avatar_url" class="form-label"></label>
                <input type="hidden" name="old_avatar_url" value="<?= htmlspecialchars($row['avatar_url'] ?? '') ?>">
                <input class="form-control" type="file" name="avatar_url" id="avatar_url" accept="image/*">
                <div id="previewContainer" class="d-flex flex-wrap gap-2 mt-2 p-2" style="min-height: 80px;">
                  <?php if (!empty($r['avatar_url'])): ?>
                    <img src="<?= htmlspecialchars($r['avatar_url']) ?>" style="max-width: 100px;">
                  <?php endif; ?>
                </div>
                <div id="imageError" class="form-text text-danger"></div>
              </div>
            </div>
            <div class="col-1 d-none d-md-block"></div>
            <button type="submit" class="btn btn-primary ">新增</button>
            <a class="btn btn-secondary " href="members_list.php" role="button">取消</a>
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
        <a href="members_list.php" class="btn btn-primary">回列表頁</a>
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
    const form = document.getElementById("memberForm");
    const nameField = document.getElementById("username");
    const emailField = document.getElementById("email");
    const fullNameField = document.getElementById("full_name");
    const phoneField = document.getElementById("phone_number");
    const passwordField = document.getElementById("password");

    function validateEmail(email) {
      const re =
        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email); //EMAIL的regret檢查方法
    }

    function validatePhone(phone_number) {
      const re = /^09\d{8}$/;
      return re.test(phone_number);
    }
    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
      nameField.classList.remove("border-danger");
      emailField.classList.remove("border-danger");
      fullNameField.classList.remove("border-danger");
      phoneField.classList.remove("border-danger");
      passwordField.classList.remove("border-danger");

      // 📝 表單欄位檢查
      let isPass = true;

      if (nameField.value.length < 2) {
        isPass = false;
        nameField.nextElementSibling.innerHTML = '名稱至少需要 2 個字';
        nameField.classList.add('border-danger');
      }
      if (!validateEmail(emailField.value)) {
        isPass = false;
        emailField.nextElementSibling.innerHTML = '請填入正確的 Email';
        emailField.classList.add('border-danger');
      }
      if (passwordField.value.length < 6) {
        isPass = false;
        passwordField.nextElementSibling.innerHTML = '密碼至少需要 6 個字';
        passwordField.classList.add('border-danger');
      }
      if (fullNameField.value.length < 2) {
        isPass = false;
        fullNameField.nextElementSibling.innerHTML = '請填入姓名';
        fullNameField.classList.add('border-danger');
      }
      if (phoneField.value.trim() === "") {
        isPass = true
      } else {
        if (!validatePhone(phoneField.value)) {
          isPass = false;
          phoneField.nextElementSibling.innerHTML = '請填入正確的電話格式';
          phoneField.classList.add('border-danger');
        }
      }
      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);

      console.log([...fd.entries()]);
      fetch("members_register-api.php", {
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
            modal.show();
          }
        })
        .catch(error => console.error("表單提交錯誤:", error));
    });


  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>