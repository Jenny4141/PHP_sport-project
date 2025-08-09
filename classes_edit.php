<?php
include __DIR__ . '/parts/init.php';

$title = '編輯課程';
$pageName = 'classes_edit';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  header('Location: classes_list.php');
  exit;
}

$classStmt = $pdo->query("SELECT class_id, classname_id FROM classes");
$courtStmt = $pdo->query("SELECT id, name FROM courts");
$coachStmt = $pdo->query("SELECT coach_id, coachname_id FROM coaches");
$classes = $classStmt->fetchAll();
$courts = $courtStmt->fetchAll();
$coaches = $coachStmt->fetchAll();

$sql = "SELECT * FROM `sessions` WHERE session_id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$r = $stmt->fetch();
if (empty($r)) {
  header('Location: classes_list.php');
  exit;
}
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

  .modal-body.success .alert-danger {
    display: none;
  }

  .modal-body.success .alert-warning {
    display: none;
  }

  .modal-body.fail .alert-success {
    display: none;
  }

  .modal-body.fail .alert-danger {
    display: block;
  }

  .modal-body.fail .alert-warning {
    display: none;
  }
</style>
<div class="container-fluid px-3">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">編輯課程</h5>
          <form name="venueForm" id="venueForm" novalidate>
            <input type="hidden" name="session_id" value="<?= $r['session_id'] ?>">
            <div class="mb-3">
              <label for="classes_id" class="form-label">課程名稱</label>
              <select class="form-select" id="classes_id" name="classes_id">
                <option value="">請選擇課程</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= $c['class_id'] ?>" <?= $r['course_id'] == $c['class_id'] ? 'selected' : '' ?>>
                    <?= htmlentities($c['classname_id']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="courts_id" class="form-label">場地</label>
              <select class="form-select" id="courts_id" name="courts_id">
                <option value="">請選擇場地</option>
                <?php foreach ($courts as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $r['courts_id'] == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlentities($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="coach_id" class="form-label">授課教練</label>
              <select class="form-select" id="coach_id" name="coach_id">
                <option value="">請選擇教練</option>
                <?php foreach ($coaches as $co): ?>
                  <option value="<?= $co['coach_id'] ?>" <?= $r['coach_id'] == $co['coach_id'] ? 'selected' : '' ?>>
                    <?= htmlentities($co['coachname_id']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="sessions_date" class="form-label">開課日期</label>
              <input type="date" class="form-control" id="sessions_date" name="sessions_date" value="<?= htmlentities($r['sessions_date']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="sessions_time" class="form-label">上課時段</label>
              <input type="text" class="form-control" id="sessions_time" name="sessions_time" value="<?= htmlentities($r['sessions_time']) ?>" placeholder="例如：19:00 - 21:00">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="price" class="form-label">價格</label>
              <input type="number" class="form-control" id="price" name="price" value="<?= htmlentities($r['price']) ?>">
              <div class="form-text text-danger"></div>
            </div>
            <div class="mb-3">
              <label for="max_capacity" class="form-label">人數上限</label>
              <input type="number" class="form-control" id="max_capacity" name="max_capacity" value="<?= htmlentities($r['max_capacity']) ?>">
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
        <div class="alert alert-success" role="alert">
          成功編輯資料
        </div>
        <div class="alert alert-warning" role="alert">
          沒有資料修改
        </div>
      </div>
      <div class="modal-footer">
        <a href="classes_list.php" class="btn btn-primary">回列表頁</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const modal = new bootstrap.Modal('#exampleModal')
    const form = document.getElementById("venueForm");
    const modalBody = document.querySelector(".modal-body");

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      fetch("classes_edit-api.php", {
          method: "POST",
          headers: { "Accept": "application/json" },
          body: fd
        })
        .then(r => r.json())
        .then(data => {
          modalBody.classList.remove("success");
          if (data.success) {
            modalBody.classList.add("success");
            modalBody.classList.remove("warning");
          } else {
            modalBody.classList.remove("success");
            modalBody.classList.add("warning");
          }
          modal.show();
        })
        .catch(err => console.error("發送錯誤", err));
    });
  });
</script>

<?php include __DIR__ . '/parts/html-tail.php' ?>
