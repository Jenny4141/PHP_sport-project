<?php
include __DIR__ . '/parts/init.php';

$title = '新增隊伍成員';
$pageName = 'tmember_add';

$team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;
$teamName = '';
$team = null;

if ($team_id <= 0) {
    header('Location: teams_list.php?error=invalid_team_id'); 
    exit;
} //檢查ID是否有效 如果沒效導回列表頁

// 取得下拉式選單及其類型內容
$teamStmt = $pdo->prepare("SELECT id, name FROM teams WHERE id = ?");
$teamStmt->execute([$team_id]);
$team = $teamStmt->fetch(\PDO::FETCH_ASSOC);

if (!$team) {
    // 如果資料庫中找不到對應的隊伍
    header('Location: teams_list.php?error=team_not_found'); // 導向列表頁並附帶錯誤訊息
    exit;
}

$teamName = htmlentities($team['name']);

?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<!-- 新增場地輸入 -->
<div class="container">
    <div class="row">
        <div class="col-3"></div>
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">為隊伍「<?= $teamName ?>」新增成員</h5>
                    <form name="tmemberForm" id="tmemberForm" novalidate>
                        <input type="hidden" name="team_id" value="<?= $team_id ?>">

                        <hr>
                        <h6>隊伍成員 (上限8人)</h6>
                        <div id="membersContainer">
                            <?php for ($i = 0; $i < 4; $i++): ?>
                                <div class="member-input-group mb-3 d-flex align-items-center">
                                    <label for="member_id_<?= $i ?>" class="form-label me-2 mb-0">成員ID:</label>
                                    <input type="text" class="form-control member-id-input" id="member_id_<?= $i ?>"
                                        name="member_ids[]" placeholder="輸入會員ID">
                                    <span class="member-name ms-2 text-secondary"></span>
                                    <div class="form-text text-danger"></div>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <div class="mb-3">
                            <button type="button" id="addMemberBtn" class="btn btn-danger btn-sm">新增成員欄位</button>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
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
    const form = document.getElementById("tmemberForm");
    const membersContainer = document.getElementById("membersContainer");
    const addMemberBtn = document.getElementById("addMemberBtn");

    const MAX_MEMBERS = 8; // 隊伍人數上限
    let currentMemberCount = membersContainer.children.length; // 當前成員欄位數量

    // 函數：更新新增成員按鈕的狀態
    function updateAddMemberButton() {
        if (currentMemberCount >= MAX_MEMBERS) {
            addMemberBtn.disabled = true;
            addMemberBtn.textContent = '已達隊員上限';
        } else {
            addMemberBtn.disabled = false;
            addMemberBtn.textContent = '新增成員欄位';
        }
    }

    // 函數：新增一個成員輸入欄位
    function addMemberInput() {
        if (currentMemberCount >= MAX_MEMBERS) {
            alert(`每個隊伍最多只能有 ${MAX_MEMBERS} 位成員。`);
            return;
        }

        const newMemberGroup = document.createElement("div");
        newMemberGroup.className = "member-input-group mb-3 d-flex align-items-center";
        const newIndex = membersContainer.children.length; // 使用當前子元素數量作為索引
        newMemberGroup.innerHTML = `
            <label for="member_id_${newIndex}" class="form-label me-2 mb-0">成員ID:</label>
            <input type="text" class="form-control member-id-input" id="member_id_${newIndex}" name="member_ids[]" placeholder="輸入會員ID">
            <span class="member-name ms-2 text-secondary"></span>
            <button type="button" class="btn btn-danger btn-sm remove-member-btn ms-2">X</button>
            <div class="form-text text-danger"></div>
        `;
        membersContainer.appendChild(newMemberGroup);
        currentMemberCount++; // 更新計數

        // 為新新增的移除按鈕綁定點擊事件
        newMemberGroup.querySelector('.remove-member-btn').addEventListener('click', (event) => {
            event.target.closest('.member-input-group').remove();
            currentMemberCount--; // 更新計數
            updateAddMemberButton(); // 更新按鈕狀態
        });

        updateAddMemberButton(); // 更新按鈕狀態
    }

    // 初始化：更新按鈕狀態
    updateAddMemberButton();

    // 點擊新增成員欄位按鈕
    addMemberBtn.addEventListener('click', addMemberInput);

    // 提交表單
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // 重置所有錯誤訊息和樣式
        document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');
        document.querySelectorAll(".member-id-input").forEach(input => input.classList.remove("border-danger"));
        document.querySelectorAll(".member-name").forEach(span => span.textContent = ''); // 清空查詢結果

        let isPass = true;

        // 從隱藏欄位獲取 team_id
        const teamId = form.querySelector('input[name="team_id"]').value;
        if (!teamId || parseInt(teamId) <= 0) {
            alert('缺少有效的隊伍 ID，請從正確的途徑進入。');
            return; // 阻止提交
        }

        const memberIdInputs = document.querySelectorAll('.member-input-group .member-id-input');
        const uniqueMemberIds = new Set(); // 用於暫時儲存輸入且格式正確的唯一ID
        const finalMemberIds = []; // 最終要提交的有效且不重複的會員ID

        // 檢查是否有任何成員ID被輸入
        let hasAnyMemberIdInput = false;
        memberIdInputs.forEach(input => {
            if (input.value.trim().length > 0) {
                hasAnyMemberIdInput = true;
            }
        });

        if (!hasAnyMemberIdInput) {
            isPass = false;
            const firstMemberInput = document.querySelector('.member-input-group .member-id-input');
            if (firstMemberInput) {
                firstMemberInput.nextElementSibling.nextElementSibling.innerHTML = '請至少輸入一位隊員 ID';
                firstMemberInput.classList.add('border-danger');
            }
        }


        // 第一階段驗證：檢查格式和前端重複
        for (const input of memberIdInputs) {
            const memberId = input.value.trim();
            const memberNameSpan = input.nextElementSibling;
            const errorSpan = memberNameSpan.nextElementSibling;

            if (memberId.length > 0) {
                if (!/^\d+$/.test(memberId)) {
                    isPass = false;
                    errorSpan.innerHTML = 'ID 只能是數字';
                    input.classList.add('border-danger');
                } else if (uniqueMemberIds.has(memberId)) {
                    isPass = false;
                    errorSpan.innerHTML = '隊員 ID 重複';
                    input.classList.add('border-danger');
                } else {
                    uniqueMemberIds.add(memberId);
                }
            }
        }

        if (!isPass) {
            return; // 如果有格式或前端重複錯誤，立即停止
        }

        // 第二階段驗證：透過 API 查詢會員是否存在並顯示名稱，同時檢查後端重複
        // 使用 Promise.allSettled 來並行處理所有查詢，並收集結果
        const queries = Array.from(uniqueMemberIds).map(async memberId => {
            try {
                const response = await fetch(`member-info-api.php?id=${memberId}`);
                const data = await response.json();
                
                // 找到對應的 input 欄位來更新顯示和錯誤
                const inputElement = document.querySelector(`input[name="member_ids[]"][value="${memberId}"]`);
                const memberNameSpan = inputElement ? inputElement.nextElementSibling : null;
                const errorSpan = memberNameSpan ? memberNameSpan.nextElementSibling : null;

                if (data.success && data.member && data.member.name) {
                    // 顯示會員名稱
                    if (memberNameSpan) memberNameSpan.textContent = `(${data.member.name})`;
                    return { id: parseInt(memberId), name: data.member.name, valid: true, element: inputElement };
                } else {
                    // 查無會員
                    if (memberNameSpan) memberNameSpan.textContent = '(查無此會員)';
                    if (errorSpan) errorSpan.innerHTML = '查無此會員 ID';
                    if (inputElement) inputElement.classList.add('border-danger');
                    isPass = false; // 標記為不通過
                    return { id: parseInt(memberId), valid: false, error: '查無此會員 ID', element: inputElement };
                }
            } catch (error) {
                console.error('查詢會員資料錯誤:', error);
                const inputElement = document.querySelector(`input[name="member_ids[]"][value="${memberId}"]`);
                const memberNameSpan = inputElement ? inputElement.nextElementSibling : null;
                const errorSpan = memberNameSpan ? memberNameSpan.nextElementSibling : null;

                if (memberNameSpan) memberNameSpan.textContent = '(查詢錯誤)';
                if (errorSpan) errorSpan.innerHTML = '查詢時發生錯誤';
                if (inputElement) inputElement.classList.add('border-danger');
                isPass = false; // 標記為不通過
                return { id: parseInt(memberId), valid: false, error: '查詢時發生錯誤', element: inputElement };
            }
        });

        // 等待所有會員查詢完成
        const results = await Promise.allSettled(queries);

        // 收集所有通過的會員 ID
        results.forEach(result => {
            if (result.status === 'fulfilled' && result.value.valid) {
                finalMemberIds.push(result.value.id);
            }
        });

        // 檢查是否還有任何錯誤，或者沒有有效的會員ID
        if (!isPass || finalMemberIds.length === 0) {
            // 如果有輸入但沒有任何一個是有效的成員
            if (hasAnyMemberIdInput && finalMemberIds.length === 0) {
                const firstInputWithContent = document.querySelector('.member-input-group .member-id-input:not([value=""])');
                if(firstInputWithContent && !firstInputWithContent.classList.contains('border-danger')) { // 避免重複顯示錯誤
                    firstInputWithContent.nextElementSibling.nextElementSibling.innerHTML = '請至少輸入一位有效隊員';
                    firstInputWithContent.classList.add('border-danger');
                }
            }
            return; // 停止提交
        }

        // 準備送出的資料
        const postData = {
            team_id: teamId,
            member_ids: finalMemberIds
        };

        console.log('提交的資料:', postData);

        try {
            const response = await fetch("tmember_add-api.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(postData)
            });
            const data = await response.json();

            if (data.success) {
                // 清空表單欄位以方便繼續新增
                form.reset();
                document.querySelectorAll('.member-name').forEach(span => span.textContent = '');
                document.querySelectorAll(".member-id-input").forEach(input => input.classList.remove("border-danger"));
                document.querySelectorAll(".form-text.text-danger").forEach(el => el.innerHTML = '');

                // 移除所有動態新增的欄位，只保留預設的4個
                while (membersContainer.children.length > 4) {
                    membersContainer.lastChild.remove();
                }
                currentMemberCount = 4; // 重置計數器
                updateAddMemberButton(); // 更新按鈕狀態

                modal.show();
            } else {
                alert("新增隊伍成員失敗：" + data.error);
                // 如果後端返回特定錯誤，例如成員已存在於隊伍中，可以根據錯誤類型給予更精確的提示
            }
        } catch (error) {
            console.error("表單提交錯誤:", error);
            alert("提交發生錯誤，請檢查網路或聯繫管理員。");
        }
    });
});
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>