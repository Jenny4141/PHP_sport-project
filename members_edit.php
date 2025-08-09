<?php
include __DIR__ . '/parts/init.php';
//阻擋admin以外的人進入
$come_from = "index_.php";
if (!empty($_SERVER['HTTP_REFERER'])) {
  $come_from = $_SERVER['HTTP_REFERER'];
}
if ($_SESSION['member']['role'] !== 'admin')
// if (!isset($_SESSION['admin'])) 
{
  header("Location: $come_from"); // 或導回首頁
  exit;
}

$title = '編輯會員資料';
$pageName = 'members_edit';



$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
  # 沒有給 PK 就直接回列表頁
  header('Location: members_list.php');
  exit;
}

$sql = "SELECT * FROM members WHERE id=$id";
$r = $pdo->query($sql)->fetch();
if (empty($r)) {
  # 沒有這筆資料
  header('Location: members_list.php');
  exit;
}

// 取得所有地區
// $locStmt = $pdo->query("SELECT id, name FROM locations");
// $locations = $locStmt->fetchAll();
// 
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
    <div class="col-12 col-sm-10 col-md-8 col-lg-6 p-2">
      <div class="card mb-4">
        <div class="card-body">
          <!-- <h5 class="card-title">編輯</h5> -->
          <form id="memberForm" name="memberForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="mb-3">
              <label for="" class="form-label">編號</label>
              <input type="text" class="form-control"
                value="<?= $r['id'] ?>" disabled>
            </div>
            <div class="mb-3">
              <label for="username" class="form-label">名稱</label>
              <input type="text" class="form-control" id="username" name="username"
                value="<?= htmlentities($r['username']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <div class="mb-3">
                <label for="email" class="form-label">電子郵箱</label>
                <input type="text" class="form-control" id="email" name="email"
                  value="<?= $r['email'] ?>">
                <div class="form-text"></div>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">密碼<span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password">
                <div class="form-text text-danger"></div>
              </div>
              <div class="mb-3">
                <label for="full_name" class="form-label">姓名</label>
                <input type="text" class="form-control" id="full_name" name="full_name"
                  value="<?= htmlentities($r['full_name']) ?>">
                <div class="form-text text-danger"></div>
              </div>
              <div class="mb-3">
                <label for="phone_number" class="form-label">連絡電話</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number"
                  value="<?= $r['phone_number'] ?>">
                <div class="form-text"></div>
              </div>
              <div class="mb-3">
                <label for="gender" class="form-label">性別</label>
                <select class="form-select" id="gender" name="gender">
                  <option value="" <?= $r['gender'] === null ? 'selected' : '' ?>>不透露</option>
                  <option value="1" <?= $r['gender'] === '1' ? 'selected' : '' ?>>男</option>
                  <option value="0" <?= $r['gender'] === '0' ? 'selected' : '' ?>>女</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="birth_date" class="form-label">出生日期</label>
                <input type="date" class="form-control" id="birth_date" name="birth_date"
                  value="<?= $r['birth_date'] ?>">
                <div class="form-text"></div>
              </div>
              <div class="mb-3">
                <label for="address" class="form-label">地址</label>
                <textarea class="form-control" id="address"
                  name="address"><?= $r['address'] ?></textarea>
              </div>
              <div class="form-text text-danger"></div>
            </div>
            <div class="card mb-4">
              <div class="card-header">
                大頭貼圖片
              </div>
              <div class="card-body ">
                <label for="avatar_url" class="form-label"></label>
                <input type="hidden" name="old_avatar_url" value="<?= htmlspecialchars($r['avatar_url'] ?? '') ?>">
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
            <button  button type="submit" class="btn btn-primary">修改</button>
            <a class="btn btn-secondary" href="members_list.php" role="button">取消</a>
          </form>
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
          請填入正確資料
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
    if (referrerURL.includes("members_list.php")) {
      const urlParams = new URL(referrerURL).searchParams;
      const page = urlParams.get("page") || 1; // 預設回到第 1 頁
      const search = urlParams.get("search") || "";

      // 設定 "回列表頁" 按鈕的 URL，保留 page 和 search 參數
      backToListBtn.href = `members_list.php?page=${page}&search=${encodeURIComponent(search)}`;
    } else {
      // 如果沒有 referrer，則回到一般的列表頁
      backToListBtn.href = "members_list.php";
    }
    // ✅ 表單編輯模塊
    const form = document.getElementById("memberForm");
    const nameField = document.getElementById("username");
    const emailField = document.getElementById("email");
    const genderField = document.getElementById("gender");
    const passwordField = document.getElementById("password");
    const fullNameField = document.getElementById("full_name");
    const phoneField = document.getElementById("phone_number");

    function validateEmail(email) {
      const re =
        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email); //EMAIL的regret檢查方法
    }

    function validatePhone(phone_number) {
      const re = /^09\d{8}$/;
      return re.test(phone_number);
    }

    const modal = new bootstrap.Modal(document.getElementById("exampleModal"));

    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 清空錯誤訊息
      document.querySelectorAll(".form-text.text-danger").forEach(el => el.textContent = '');
      nameField.classList.remove("border-danger");
      emailField.classList.remove("border-danger");
      passwordField.classList.remove("border-danger");
      fullNameField.classList.remove("border-danger");
      phoneField.classList.remove("border-danger");
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
      if (passwordField.value) {
        if (passwordField.value.length < 6) {
          isPass = false;
          passwordField.nextElementSibling.innerHTML = '密碼至少需要 6 個字';
          passwordField.classList.add('border-danger');
        }
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

      if (!isPass) {
        return;
      }

      // 送出表單資料
      const fd = new FormData(form);
      fetch("members_edit-api.php", {
          method: "POST",
          // headers: {
          //   "Accept": "application/json"
          // },
          body: fd
        })
        .then(response => response.json())
        .then(data => {
          console.log(data);
          const modalBody = document.querySelector(".modal-body");

          if (data.success) {
            modalBody.classList.add("success");
            // form.reset(); // 清空表單
            modal.show();
          } else {
            modalBody.classList.remove("success");
          }

        })
        // .catch(error => console.error("表單提交錯誤:", error));
        .catch(console.warn)
    });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>