<?php
include __DIR__ . '/parts/init.php';

$title = '新增訂單';
$pageName = 'booking_add';

// 取得課程場次與預約狀態選項
$sessionStmt = $pdo->query("SELECT session_id, sessions_date, sessions_time FROM sessions");
$statusStmt = $pdo->query("SELECT booking_status_id, status_name FROM booking_status");
$sessions = $sessionStmt->fetchAll();
$statuses = $statusStmt->fetchAll();
$sessionStmt = $pdo->query("
  SELECT 
    s.session_id, 
    c.classname_id, 
    s.sessions_date, 
    s.sessions_time 
  FROM sessions s
  JOIN classes c ON s.course_id = c.class_id
");
$sessions = $sessionStmt->fetchAll();

?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<!-- 新增輸入 -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">新增訂單</h5>
                    <form id="bookingForm" name="bookingForm" novalidate>
                        <div class="mb-3">
                            <label for="member_id" class="form-label">會員 ID</label>
                            <input type="number" class="form-control" id="member_id" name="member_id" required>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="session_id" class="form-label">課程場次</label>
                            <select class="form-select" id="session_id" name="session_id" required>
                                <option value="">請選擇課程場次</option>
                                <?php foreach ($sessions as $s): ?>
                                    <option value="<?= $s['session_id'] ?>">
                                        <?= htmlentities($s['classname_id']) ?>｜<?= $s['sessions_date'] ?>｜<?= $s['sessions_time'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="booking_status_id" class="form-label">預約狀態</label>
                            <select class="form-select" id="booking_status_id" name="booking_status_id" required>
                                <option value="">請選擇狀態</option>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= $s['booking_status_id'] ?>">
                                        <?= htmlentities($s['status_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">價位</label>
                            <input type="number" class="form-control" id="price" name="price" required>
                            <div class="form-text text-danger"></div>
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
                <a href="booking_list.php" class="btn btn-primary">回列表頁</a>
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
        const form = document.getElementById("bookingForm");
        /* const nameField = document.getElementById("name");
        const locationField = document.getElementById("location_id"); */
        // 點擊事件行為
        form.addEventListener("submit", (e) => {
            e.preventDefault();

            // 重置錯誤訊息
            // document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
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
            fetch("booking_add-api.php", {
                    method: "POST",
                    headers: {
                        "Accept": "application/json"
                    },
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        modal.show();
                        // form.reset();
                    } else {
                        alert("新增失敗：" + data.error);
                    }
                })
                .catch(err => console.error("提交錯誤：", err));
        });


    });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>