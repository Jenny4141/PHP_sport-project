<?php
include __DIR__ . '/parts/init.php';

$title = '編輯訂單';
$pageName = 'booking_edit';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: booking_list.php');
    exit;
}

// 取得資料
$sql = "SELECT * FROM booking WHERE booking_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$r = $stmt->fetch();
if (empty($r)) {
    header('Location: booking_list.php');
    exit;
}

$sessionStmt = $pdo->query("SELECT 
  s.session_id, 
  c.classname_id, 
  s.sessions_date, 
  s.sessions_time 
  FROM sessions s
  JOIN classes c ON s.course_id = c.class_id");
$sessions = $sessionStmt->fetchAll();

$statusStmt = $pdo->query("SELECT booking_status_id, status_name FROM booking_status");
$statuses = $statusStmt->fetchAll();
?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>
<style>
    .modal-body .alert-success {
        display: none;
    }

    .modal-body .alert-danger {
        display: none;
    }

    .modal-body .alert-warning {
        display: block;
    }

    .modal-body.success .alert-success {
        display: block;
    }

    .modal-body.success .alert-danger,
    .modal-body.success .alert-warning {
        display: none;
    }

    .modal-body.fail .alert-danger {
        display: block;
    }

    .modal-body.fail .alert-success,
    .modal-body.fail .alert-warning {
        display: none;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">編輯訂單</h5>
                    <form id="bookingForm" name="bookingForm" novalidate>
                        <input type="hidden" name="booking_id" value="<?= $r['booking_id'] ?>">
                        <div class="mb-3">
                            <label for="member_id" class="form-label">會員 ID</label>
                            <input type="number" class="form-control" id="member_id" name="member_id" value="<?= $r['member_id'] ?>" required>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="session_id" class="form-label">課程場次</label>
                            <select class="form-select" id="session_id" name="session_id" required>
                                <option value="">請選擇課程場次</option>
                                <?php foreach ($sessions as $s): ?>
                                    <option value="<?= $s['session_id'] ?>" <?= $r['session_id'] == $s['session_id'] ? 'selected' : '' ?>>
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
                                    <option value="<?= $s['booking_status_id'] ?>" <?= $r['booking_status_id'] == $s['booking_status_id'] ? 'selected' : '' ?>>
                                        <?= htmlentities($s['status_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">價位</label>
                            <input type="number" class="form-control" id="price" name="price" value="<?= $r['price'] ?>" required>
                            <div class="form-text text-danger"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">修改</button>
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
                <h1 class="modal-title fs-5" id="exampleModalLabel">更新結果</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" role="alert">成功修改資料！</div>
                <div class="alert alert-warning" role="alert">沒有資料變更。</div>
                <div class="alert alert-danger" role="alert">更新失敗，請稍後再試。</div>
            </div>
            <div class="modal-footer">
                <a href="booking_list.php" class="btn btn-primary">回列表頁</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const modal = new bootstrap.Modal('#exampleModal')
        const form = document.getElementById("bookingForm");
        const modalBody = document.querySelector(".modal-body");

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            fetch("booking_edit-api.php", {
                    method: "POST",
                    headers: {
                        "Accept": "application/json"
                    },
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    modalBody.classList.remove("success", "fail");
                    if (data.success) {
                        modalBody.classList.add("success");
                    } else {
                        modalBody.classList.add("fail");
                    }
                    modal.show();
                })
                .catch(err => {
                    modalBody.classList.remove("success");
                    modalBody.classList.add("fail");
                    modal.show();
                    console.error("發送錯誤", err);
                });
        });
    });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>