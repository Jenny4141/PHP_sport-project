<?php
include __DIR__ . '/parts/init.php';

$title = '新增課程';
$pageName = 'venues_add';

// 取得可用場地、教練
$classStmt = $pdo->query("SELECT class_id, classname_id FROM classes");
$courtStmt = $pdo->query("SELECT id, name FROM courts");
$coachStmt = $pdo->query("SELECT coach_id, coachname_id FROM coaches");
$classes = $classStmt->fetchAll();
$courts = $courtStmt->fetchAll();
$coaches = $coachStmt->fetchAll();
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
                    <h5 class="card-title">新增課程</h5>
                    <form name="venueForm" id="venueForm" novalidate>
                        <div class="mb-3">
                            <label for="classname_id" class="form-label">課程名稱</label>
                            <select class="form-select" id="classes_id" name="classes_id">
                                <option value="">請選擇課程</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['class_id'] ?>"><?= htmlentities($c['classname_id']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="courts_id" class="form-label">場地</label>
                            <select class="form-select" id="courts_id" name="courts_id">
                                <option value="">請選擇場地</option>
                                <?php foreach ($courts as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlentities($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="coach_id" class="form-label">授課教練</label>
                            <select class="form-select" id="coach_id" name="coach_id">
                                <option value="">請選擇教練</option>
                                <?php foreach ($coaches as $co): ?>
                                    <option value="<?= $co['coach_id'] ?>"><?= htmlentities($co['coachname_id']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="sessions_date" class="form-label">開課日期</label>
                            <input type="date" class="form-control" id="sessions_date" name="sessions_date">
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="sessions_time" class="form-label">上課時段</label>
                            <input type="text" class="form-control" id="sessions_time" name="sessions_time" placeholder="例如：19:00 - 21:00">
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">價格</label>
                            <input type="number" class="form-control" id="price" name="price">
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="max_capacity" class="form-label">人數上限</label>
                            <input type="number" class="form-control" id="max_capacity" name="max_capacity">
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
                <a href="classes_list.php" class="btn btn-primary">回列表頁</a>
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
        /* const nameField = document.getElementById("name");
        const locationField = document.getElementById("location_id"); */
        // 點擊事件行為
        form.addEventListener("submit", (e) => {
            e.preventDefault();

            // 重置錯誤訊息
            /* document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
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

            if (!isPass) return; */

            // 送出表單資料
            const fd = new FormData(form);

            console.log([...fd.entries()]);
            fetch("classes_add-api.php", {
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