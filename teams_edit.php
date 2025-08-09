<?php include __DIR__ . '/parts/init.php'; # 初始化頁面

$title = '編輯隊伍';
$pageName = 'teams_edit';

$teamId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($teamId <= 0) {
  header('Location: teams_list.php');
  exit;
}

// 取得隊伍資料
$teamSql = "SELECT t.*,
                   c.name as court_name,     -- 場地名稱
                   s.name as sport_name      -- 運動類型名稱
            FROM teams t
            LEFT JOIN courts c ON t.courts_id = c.id     -- 只連結場地表
            LEFT JOIN sports s ON c.sport_id = s.id     -- 透過場地連結運動類型
            WHERE t.id = $teamId";

try {
  $stmt = $pdo->query($teamSql);
  $teamData = $stmt->fetch(PDO::FETCH_ASSOC);

  if (empty($teamData)) {
    header('Location: teams_list.php'); // 如果找不到隊伍，導回列表頁
    exit;
  }
} catch (PDOException $e) {
  error_log("teams_edit.php SQL Error: " . $e->getMessage());
  echo "資料庫查詢錯誤，請稍後再試。";
  exit;
}

// 取得所有等級資料
$levelSql = "SELECT * FROM level ORDER BY id ASC";
$levelStmt = $pdo->query($levelSql);
$levels = $levelStmt->fetchAll(PDO::FETCH_ASSOC);

// 取得所有運動類型資料
$sportSql = "SELECT * FROM sports ORDER BY id ASC";
$sportStmt = $pdo->query($sportSql);
$sports = $sportStmt->fetchAll(PDO::FETCH_ASSOC);

// 取得所有場地資料 (不再篩選場館，直接取得所有場地)
$courtSql = "SELECT c.id, c.name, s.name AS sport_name
             FROM courts c
             JOIN sports s ON c.sport_id = s.id
             ORDER BY c.name ASC";
$courtStmt = $pdo->query($courtSql);
$courts = $courtStmt->fetchAll(PDO::FETCH_ASSOC);

// 從隊伍資料中取得當前選擇的 ID
$currentLevelId = $teamData['level_id'];
$currentCourtId = $teamData['courts_id']; // 使用正確的欄位名稱

?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>
<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6 pb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">編輯隊伍</h5>
          <form name="form1" onsubmit="sendData(event)">
            <input type="hidden" name="id" value="<?= $teamId ?>">
            <input type="hidden" name="action" value="edit-team">

            <div class="mb-3">
              <label for="name" class="form-label">隊伍名稱</label>
              <input type="text" class="form-control" id="name" name="name"
                value="<?= htmlentities($teamData['name']) ?>">
              <div class="form-text text-danger" data-field="name"></div>
            </div>

            <div class="mb-3">
              <label for="level_id" class="form-label">隊伍等級</label>
              <select class="form-select" id="level_id" name="level_id">
                <option value="">請選擇等級</option>
                <?php foreach ($levels as $level): ?>
                  <option value="<?= $level['id'] ?>"
                    <?= $level['id'] == $currentLevelId ? 'selected' : '' ?>>
                    <?= htmlentities($level['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger" data-field="level_id"></div>
            </div>

            <div class="mb-3">
              <label for="court_id" class="form-label">所屬場地</label>
              <select class="form-select" id="court_id" name="courts_id">
                <option value="">請選擇場地</option>
                <?php foreach ($courts as $court): ?>
                  <option value="<?= $court['id'] ?>"
                    <?= $court['id'] == $currentCourtId ? 'selected' : '' ?>>
                    <?= htmlentities($court['name']) ?> (<?= htmlentities($court['sport_name']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-danger" data-field="courts_id"></div>
            </div>

            <!-- <div class="mb-3">
                            <label for="sport_id" class="form-label">運動類型</label>
                            <select class="form-select" id="sport_id" name="sport_id">
                                <option value="">請選擇運動類型</option>
                                <?php foreach ($sports as $sport): ?>
                                    <option value="<?= $sport['id'] ?>"
                                        <?= $sport['id'] == $currentSportId ? 'selected' : '' ?>> <?= htmlentities($sport['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-danger" data-field="sport_id"></div>
                        </div> -->


            <button type="submit" class="btn btn-primary">修改</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">隊員列表</h5>
          <div class="mb-3">
            <h6 id="member-count-display">目前隊員人數 (<?= $teamData['member_count'] ?? 0 ?>/8)</h6>
            <ul id="current-members-list" class="list-group">
              <?php
              // 重新查詢隊員列表，因為 $teamData 中沒有成員詳情
              $membersSql = "SELECT tm.id AS tmember_id, m.id AS member_id, m.full_name, m.phone_number AS phone, m.email
               FROM tmember tm
               JOIN members m ON tm.members_id = m.id
               WHERE tm.team_id = ?";
              $membersStmt = $pdo->prepare($membersSql);
              $membersStmt->execute([$teamId]);
              $currentMembers = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

              foreach ($currentMembers as $member):
              ?>
                <li class="list-group-item d-flex justify-content-between align-items-center" data-tmember-id="<?= $member['tmember_id'] ?>" data-member-id="<?= $member['member_id'] ?>">
                  <div>
                    <strong>ID:</strong> <?= $member['member_id'] ?> |
                    <strong>姓名:</strong> <?= htmlentities($member['full_name']) ?> |
                    <strong>電話:</strong> <?= htmlentities($member['phone']) ?> |
                    <strong>信箱:</strong> <?= htmlentities($member['email']) ?>
                  </div>
                  <button type="button" class="btn btn-danger btn-sm remove-member-btn" data-tmember-id="<?= $member['tmember_id'] ?>">移除</button>
                </li>
              <?php endforeach; ?>
              <?php if (empty($currentMembers)): ?>
                <li class="list-group-item">目前沒有隊員。</li>
              <?php endif; ?>
            </ul>
          </div>

          <hr>

          <div class="mb-3">
            <h6>新增隊員</h6>
            <div class="input-group mb-3">
              <input type="text" class="form-control" id="new-member-id" placeholder="輸入會員 ID" aria-label="會員 ID">
              <button class="btn btn-outline-secondary" type="button" id="lookup-member-btn">查詢</button>
            </div>
            <div id="member-lookup-error" class="text-danger mb-2"></div>
            <div id="new-member-info" style="display: none;">
              <p><strong>姓名:</strong> <span id="lookup-name"></span></p>
              <p><strong>電話:</strong> <span id="lookup-phone"></span></p>
              <p><strong>信箱:</strong> <span id="lookup-email"></span></p>
              <button type="button" class="btn btn-success" id="add-member-btn">加入隊伍</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
  const nameField = document.getElementById('name');
  const levelIdField = document.getElementById('level_id');
  const courtIdField = document.getElementById('court_id'); // 更改為 court_id
  // const sportIdField = document.getElementById('sport_id');

  // 隊員管理相關元素
  const new_member_id_input = document.getElementById('new-member-id');
  const lookupMemberBtn = document.getElementById('lookup-member-btn');
  const member_lookup_error = document.getElementById('member-lookup-error');
  const new_member_info = document.getElementById('new-member-info');
  const lookup_name = document.getElementById('lookup-name');
  const lookup_phone = document.getElementById('lookup-phone');
  const lookup_email = document.getElementById('lookup-email');
  const addMemberBtn = document.getElementById('add-member-btn');
  const currentMembersList = document.getElementById('current-members-list');

  const memberCountDisplay = document.getElementById('member-count-display');
  const maxMembers = 8; // 定義最大隊員數，方便管理

  let currentLookupMember = null; // 用於儲存查詢到的會員資料

  // 驗證表單
  const sendData = async (e) => {
    e.preventDefault(); // 阻擋表單的預設提交

    // 清除錯誤提示
    document.querySelectorAll('.form-text.text-danger').forEach(el => el.innerText = '');

    let isPass = true; // 表單有沒有通過驗證

    if (nameField.value.length < 2) {
      isPass = false;
      nameField.nextElementSibling.innerText = '隊伍名稱至少2個字';
    }
    if (levelIdField.value === '') {
      isPass = false;
      levelIdField.nextElementSibling.innerText = '請選擇隊伍等級';
    }
    if (courtIdField.value === '') { // 檢查場地 ID
      isPass = false;
      courtIdField.nextElementSibling.innerText = '請選擇所屬場地';
    }
    // if (sportIdField.value === '') {
    //     isPass = false;
    //     sportIdField.nextElementSibling.innerText = '請選擇運動類型';
    // }

    if (isPass) {
      // 送出表單
      const fd = new FormData(document.form1); // 沒有外觀的表單資料物件

      try {
        const response = await fetch('teams_edit-api.php', {
          method: 'POST',
          body: fd, // 當 body 是 FormData 物件時，header 的 Content-Type 會自動設定
        });

        const result = await response.json();
        console.log(result);

        if (result.success) {
          alert('資料修改成功');
          location.href = 'teams_list.php'; // 導向到列表頁
        } else {
          // 顯示後端返回的錯誤訊息
          alert('資料修改失敗: ' + (result.error || '未知錯誤'));
          // 如果有錯誤欄位，也可以顯示在對應位置
          for (let field in result.errors) {
            const errorElement = document.querySelector(`[data-field="${field}"]`);
            if (errorElement) {
              errorElement.innerText = result.errors[field];
            }
          }
        }
      } catch (error) {
        console.error('Fetch error:', error);
        alert('網路錯誤，請稍後再試。');
      }
    }
  };

  // 隊員查詢邏輯
  lookupMemberBtn.addEventListener('click', async () => {
    const memberId = parseInt(new_member_id_input.value);
    member_lookup_error.innerText = '';
    new_member_info.style.display = 'none';

    if (isNaN(memberId) || memberId <= 0) {
      member_lookup_error.innerText = '請輸入有效的會員 ID。';
      return;
    }

    // 檢查該會員是否已經在隊伍中
    const existingMember = document.querySelector(`#current-members-list li[data-member-id="${memberId}"]`);
    if (existingMember) {
      member_lookup_error.innerText = '該會員已在隊伍中。';
      return;
    }

    try {
      // 調用 member-info-api.php，並傳遞 'id' 參數
      const response = await fetch(`member-info-api.php?id=${memberId}`);
      const data = await response.json();

      if (data.success && data.member) {
        currentLookupMember = data.member;
        lookup_name.innerText = currentLookupMember.name;
        lookup_phone.innerText = currentLookupMember.phone;
        lookup_email.innerText = currentLookupMember.email;
        new_member_info.style.display = 'block';
      } else {
        currentLookupMember = null;
        member_lookup_error.innerText = data.error || '查無此會員。';
      }
    } catch (error) {
      console.error('查詢會員時發生錯誤:', error);
      member_lookup_error.innerText = '查詢會員時發生網路錯誤。';
    }
  });

  // 加入隊員邏輯
  addMemberBtn.addEventListener('click', async () => {
    if (!currentLookupMember) {
      alert('請先查詢並選擇一個會員。');
      return;
    }

    // 檢查隊伍人數限制 (8人)
    const currentMemberCount = currentMembersList.children.length;
    if (currentMemberCount >= 8) {
      alert('隊伍成員已達上限 (8人)，無法新增。');
      return;
    }

    try {
      const response = await fetch('teams_edit-api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          action: 'add-member',
          team_id: <?= $teamId ?>,
          member_id: currentLookupMember.id
        })
      });

      const result = await response.json();
      if (result.success) {
        alert('隊員加入成功！');
        // 動態更新列表
        const newMemberLi = document.createElement('li');
        newMemberLi.className = 'list-group-item d-flex justify-content-between align-items-center';
        newMemberLi.dataset.tmemberId = result.tmember_id; // 從 API 返回的 tmember_id
        newMemberLi.dataset.memberId = currentLookupMember.id;
        newMemberLi.innerHTML = `
                    <div>
                        <strong>ID:</strong> ${currentLookupMember.id} |
                        <strong>姓名:</strong> ${currentLookupMember.name} |
                        <strong>電話:</strong> ${currentLookupMember.phone} |
                        <strong>信箱:</strong> ${currentLookupMember.email}
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-member-btn" data-tmember-id="${result.tmember_id}">移除</button>
                `;
        currentMembersList.appendChild(newMemberLi);

        // 為新加入的移除按鈕添加事件監聽器
        //newMemberLi.querySelector('.remove-member-btn').addEventListener('click', handleRemoveMember);

        const currentCount = currentMembersList.children.length;
        memberCountDisplay.innerText = `目前隊員人數 (${currentCount}/${maxMembers})`;

        new_member_id_input.value = ''; // 清空輸入框
        new_member_info.style.display = 'none'; // 隱藏會員資訊
        currentLookupMember = null; // 清空查詢到的會員
      } else {
        alert('隊員加入失敗: ' + (result.error || '未知錯誤'));
      }
    } catch (error) {
      console.error('加入隊員時發生錯誤:', error);
      alert('加入隊員時發生網路錯誤。');
    }
  });

  // 移除隊員邏輯 (事件委派)
  currentMembersList.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-member-btn')) {
      handleRemoveMember(e);
    }
  });

  const handleRemoveMember = async (e) => {
    const tmemberId = e.target.dataset.tmemberId;
    const listItem = e.target.closest('li');

    if (!confirm('確定要移除此隊員嗎？')) {
      return;
    }

    try {
      const response = await fetch('teams_edit-api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          action: 'remove-member',
          tmember_id: tmemberId
        })
      });

      const result = await response.json();
      if (result.success) {
        alert('隊員移除成功！');
        if (listItem) {
          listItem.remove(); // 從 DOM 中移除列表項
          const currentCount = currentMembersList.children.length;
          memberCountDisplay.innerText = `目前隊員人數 (${currentCount}/${maxMembers})`;
        }
      } else {
        alert('隊員移除失敗: ' + (result.error || '未知錯誤'));
      }
    } catch (error) {
      console.error('移除隊員時發生錯誤:', error);
      alert('移除隊員時發生網路錯誤。');
    }
  };
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>