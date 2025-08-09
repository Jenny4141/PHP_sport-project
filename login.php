<?php
include __DIR__ . '/parts/init.php';
$title = '登入 ';
$pageName = 'login';
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>


<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">登入</h5>
          <form name="form1" onsubmit="sendData(event)" novalidate>
            <div class="mb-3">
              <label for="email" class="form-label">電子郵件</label>
              <input type="text" class="form-control" id="email" name="email">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="address" class="form-label">密碼</label>
              <input type="password" class="form-control" id="password" name="password">
              <div class="form-text"></div>
            </div>
            <button type="submit" class="btn btn-primary">登入</button>
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
        <div class="alert alert-danger" role="alert">
          帳號或密碼錯誤
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
  // 彈出視窗實例化
  const modal = new bootstrap.Modal('#exampleModal')

  // 定義驗證 email 的函式
  function validateEmail(email) {
    const re =
      /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
  // 點擊事件行為
  const sendData = e => {
    // 阻止默認行為，不要讓表單以傳統的方式送出
    e.preventDefault();

    // ✅ TODO：表單欄位檢查
    let isPass = true; // 有沒有通過檢查

    if (!isPass) {
      return;
    }


    const fd = new FormData(document.form1);

    fetch('login-api.php', {
        method: 'POST',
        body: fd,
      })
      .then(r => r.json())
      .then(obj => {
        console.log(obj);

        // 失敗就彈窗，成功就跳轉回首頁1
        if (!obj.success) {
          const alertBox = document.querySelector('#exampleModal .modal-body .alert');

          if (obj.code === 450 && obj.error) {
            alertBox.innerText = obj.error;
          } else {
            alertBox.innerText = "帳號或密碼錯誤";
          }

          modal.show();
        } else {
          // location.href = document.referrer ? document.referrer : "index_.php";
          location.href = 'index_.php';
        }
      })
      .catch(console.warn) // 防止崩潰
  }
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>