<?php
include __DIR__ . '/parts/init.php';

$title = '編輯訂單';
$pageName = 'reservations_edit';

// 取得訂單 ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  header('Location: reservations_list.php');
  exit;
}

// 取得該訂單資料
$sql = "SELECT * FROM reservations WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$r = $stmt->fetch();
if (empty($r)) {
  header('Location: reservations_list.php');
  exit;
}

// 取得所有會員
$memberStmt = $pdo->query("SELECT id, username FROM members");
$members = $memberStmt->fetchAll();

// 取得所有場地時間
$courtStmt = $pdo->query("SELECT ct.id, c.name AS court_name, 
                          ts.start_time, ts.end_time 
                          FROM courts_timeslots ct
                          JOIN courts c ON ct.court_id = c.id
                          JOIN time_slots ts ON ct.time_slot_id = ts.id");
$courts = $courtStmt->fetchAll();

// 取得所有場館
$venueStmt = $pdo->query("SELECT id, name FROM venues");
$venues = $venueStmt->fetchAll();

// 取得所有運動種類
$sportStmt = $pdo->query("SELECT id, name FROM sports");
$sports = $sportStmt->fetchAll();

// 取得所有狀態
$statusStmt = $pdo->query("SELECT id, name FROM reservation_statuses");
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
<!-- 新增訂單輸入 -->
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card mb-5">
        <div class="card-body">
          <h5 class="card-title">編輯訂單</h5>
          <form name="reservationForm" id="reservationForm" novalidate>

            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="mb-3">
              <label class="form-label">編號</label>
              <input type="text" class="form-control" value="<?= $r['id'] ?>" disabled>
            </div>

            <div class="mb-3">
              <label for="member_search" class="form-label">會員<span class="text-danger">*</span></label>
              <input type="text" class="form-control mb-3" id="member_search" placeholder="輸入會員名稱">
              <select class="form-select" id="member_id" name="member_id">
                <option value="">請選擇會員</option>
                <?php foreach ($members as $member): ?>
                  <option value="<?= $member['id'] ?>" <?= ($r['member_id'] == $member['id']) ? 'selected' : '' ?>>
                    <?= $member['username'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="date" class="form-label">日期<span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="date" name="date" value="<?= $r['date'] ?>">
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="time_filter" class="form-label">時間段</label>
              <select class="form-select" id="time_filter" name="time_filter">
                <option value="">所有時間段</option>
                <option value="早上">早上 (06:00 - 12:00)</option>
                <option value="下午">下午 (12:00 - 18:00)</option>
                <option value="晚上">晚上 (18:00 - 22:00)</option>
              </select>
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="venue_filter" class="form-label">場館</label>
              <select class="form-select" id="venue_filter" name="venue_id">
                <option value="">請選擇場館</option>
                <?php foreach ($venues as $venue): ?>
                  <option value="<?= $venue['id'] ?>"><?= $venue['name'] ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="sport_filter" class="form-label">運動種類</label>
              <select class="form-select" id="sport_filter" name="sport_id">
                <option value="">請選擇運動種類</option>
                <?php foreach ($sports as $sport): ?>
                  <option value="<?= $sport['id'] ?>"><?= $sport['name'] ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="court_timeslot_id" class="form-label">時間<span class="text-danger">*</span></label>
              <select class="form-select" id="court_timeslot_id" name="court_timeslot_id">
                <option value="">請選擇時間</option>
                <?php foreach ($courts as $court): ?>
                  <option value="<?= $court['id'] ?>" <?= ($r['court_timeslot_id'] == $court['id']) ? 'selected' : '' ?>>
                    <?= $court['court_name'] ?> (<?= date('H:i', strtotime($court['start_time'])) ?> - <?= date('H:i', strtotime($court['end_time'])) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="price" class="form-label">價格</label>
              <input type="text" class="form-control" id="price" name="price" value="<?= !empty($r['price']) ? number_format($r['price'], 2) : '' ?>" readonly>
              <div class="form-text text-danger"></div>
            </div>

            <div class="mb-3">
              <label for="status_id" class="form-label">狀態<span class="text-danger">*</span></label>
              <select class="form-select" id="status_id" name="status_id">
                <option value="">請選擇狀態</option>
                <?php foreach ($statuses as $status): ?>
                  <option value="<?= $status['id'] ?>" <?= ($r['status_id'] == $status['id']) ? 'selected' : '' ?>><?= $status['name'] ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger"></div>
            </div>

            <button type="submit" class="btn btn-primary me-2">修改</button>
            <a class="btn btn-secondary " href="reservations_list.php" role="button">取消</a>
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
          成功編輯資料
        </div>
        <div class="alert alert-warning" role="alert">
          沒有資料修改
        </div>
        <div class="alert alert-danger" role="alert">
          該場地時間在選定日期已被預訂
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續新增</button>
        <a href="reservations_list.php" class="btn btn-primary">回列表頁</a>
      </div>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // ✅ 可搜尋下拉表單
    function setupAutocomplete(inputField, selectField) {
      inputField.addEventListener("input", () => {
        const keyword = inputField.value.toLowerCase();
        let hasResults = false;

        selectField.classList.remove("d-none");
        Array.from(selectField.options).forEach(option => {
          if (option.text.toLowerCase().includes(keyword)) {
            option.hidden = false;
            hasResults = true;
          } else {
            option.hidden = true;
          }
        });

        if (!hasResults) {
          selectField.classList.add("d-none");
        }
      });

      // 當選擇選項後，把選擇的值回填到輸入框
      selectField.addEventListener("change", () => {
        inputField.value = selectField.options[selectField.selectedIndex].text;
        selectField.classList.add("d-none");
      });
    }

    setupAutocomplete(document.getElementById("member_search"), document.getElementById("member_id"));

    // ✅ 表單新增模塊

    // 彈出視窗實例化
    const modal = new bootstrap.Modal('#exampleModal')
    const modalBody = document.querySelector(".modal-body");

    // 獲取表單欄位
    const form = document.getElementById("reservationForm");
    const courtSelect = document.getElementById("court_timeslot_id");
    const priceField = document.getElementById("price");

    function updateCourtTimeslots() {
      const timeRange = document.getElementById("time_filter").value;
      const venueId = document.getElementById("venue_filter").value;
      const sportId = document.getElementById("sport_filter").value;
      const selectedDate = document.getElementById("date").value;

      if (!selectedDate) return; // 確保有選擇日期

      // 傳送所有篩選條件
      fetch(`reservations_filter_timeslots-api.php?range=${encodeURIComponent(timeRange)}&venue_id=${venueId}&sport_id=${sportId}&date=${selectedDate}`)
        .then(response => response.json())
        .then(data => {
          const courtSelect = document.getElementById("court_timeslot_id");
          courtSelect.innerHTML = "<option value=''>請選擇場地時間</option>";

          if (!data.success || data.timeslots.length === 0) {
            courtSelect.innerHTML = "<option value=''>沒有符合條件的場地時間</option>";
            return;
          }

          data.timeslots.forEach(timeslot => {
            const option = document.createElement("option");
            option.value = timeslot.id;
            option.textContent = `${timeslot.court_name} (${timeslot.start_time} - ${timeslot.end_time})`;
            courtSelect.appendChild(option);
          });
        })
        .catch(error => console.error("場地時間搜尋錯誤:", error));
    }

    // ✅ 監聽所有篩選選單
    ["time_filter", "venue_filter", "sport_filter", "date"].forEach(id => {
      document.getElementById(id).addEventListener("change", updateCourtTimeslots);
    });

    courtSelect.addEventListener("change", () => {
      const courtTimeslotId = courtSelect.value;

      if (!courtTimeslotId) {
        priceField.value = "";
        return;
      }

      fetch(`reservations_get_price-api.php?court_timeslot_id=${courtTimeslotId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            priceField.value = data.price;
          } else {
            priceField.value = "無法獲取價格";
          }
        })
        .catch(error => console.error("價格獲取錯誤:", error));
    });

    // 點擊事件行為
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      // 重置錯誤訊息
      document.querySelectorAll(".text-danger").forEach(el => el.innerHTML = '');
      /* memberField.classList.remove("border-danger");
      ctField.classList.remove("border-danger");
      dateField.classList.remove("border-danger");
      statusField.classList.remove("border-danger"); */

      // 📝 表單欄位檢查
      // 表單驗證
      let isPass = true;
      form.querySelectorAll(".text-danger").forEach(el => el.innerHTML = '');
      form.querySelectorAll("select, input").forEach(field => field.classList.remove("border-danger"));

      ["member_id", "court_timeslot_id", "date", "status_id"].forEach(id => {
        const field = document.getElementById(id);
        if (!field.value.trim()) {
          isPass = false;
          field.classList.add("border-danger");
          field.nextElementSibling.innerHTML = `請選擇${field.previousElementSibling.textContent}`;
        }
      });

      if (!isPass) return;

      // 送出表單資料
      const fd = new FormData(form);
      fd.append("price", priceField.value); // ✅ 確保價格一併提交
      fd.append("member_id", document.getElementById("member_id").value);
      fd.append("court_timeslot_id", document.getElementById("court_timeslot_id").value);

      console.log([...fd.entries()]); // ✅ 確認所有欄位都包含


      fetch("reservations_edit-api.php", {
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
            modalBody.classList.add("success");
            modalBody.classList.remove("fail");
            modalBody.classList.remove("warning");
            form.reset();
          } else if (data.fail) {
            modalBody.classList.remove("success");
            modalBody.classList.add("fail");
            modalBody.classList.remove("warning");
          } else if (data.warning) {
            modalBody.classList.remove("success");
            modalBody.classList.remove("fail");
            modalBody.classList.add("warning");
          }
          modal.show();
        })
        .catch(error => console.error("表單提交錯誤:", error));
    });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>