<?php include __DIR__ . '/parts/init.php';
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

$title = '訂單管理';
$pageName = 'orders_list';

// 設定分頁
$perPage = 15; # 每頁幾筆資料
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) {
    header('Location: ?page=1');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = '';
$params = [];

if (!empty($search)) {
    $searchSql = "WHERE LOWER(o.order_id) LIKE LOWER(?) 
              OR LOWER(m.id) LIKE LOWER(?) 
              OR LOWER(o.delivery) LIKE LOWER(?) 
              OR LOWER(o.payment) LIKE LOWER(?) 
              OR LOWER(o.invoice) LIKE LOWER(?) 
              OR LOWER(o.status) LIKE LOWER(?)";
    $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
}

// 取得總筆數（考慮搜尋條件）
$t_sql = "SELECT COUNT(1) FROM orders o 
        JOIN members m ON o.member_id = m.id $searchSql";
$stmt = $pdo->prepare($t_sql);
$stmt->execute($params);

# 預設值
$totalPages = 0;
$rows = [];

// 計算總頁數
$totalRows = $stmt->fetch(PDO::FETCH_NUM)[0];
$totalPages = ceil($totalRows / $perPage);

// 確保 page 不超過最大頁數
if ($page > $totalPages && $totalPages > 0) {
    header("Location: ?page={$totalPages}");
    exit;
}

// 取得符合條件的場館與地區列表
$sql = "SELECT o.*, m.id
        FROM orders o 
        JOIN members m ON o.member_id = m.id
        $searchSql 
        ORDER BY o.order_id 
        LIMIT " . (($page - 1) * $perPage) . ", " . $perPage;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

?>
<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<div class="container-fluid px-3">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-11 col-md-10">
            <div class="d-flex flex-wrap justify-content-md-between align-items-center gap-2 mb-3">
                <!-- 新增按鈕 -->
                <a href="orders_add.php" class="btn btn-outline-primary">新增</a>
                <!-- 搜尋框區塊 -->
                <form class="d-flex align-items-center mb-0" role="search" method="GET" action="orders_list.php">
                    <input class="form-control me-2" type="search" name="search" placeholder="輸入訂單資訊" value="<?= htmlentities($search) ?>" aria-label="Search" />
                    <button class="btn btn-outline-success" style="white-space: nowrap;" type="submit">搜尋</button>
                </form>
            </div>

            <!-- 主內容表格區 -->
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="mb-0 table table-bordered table-striped text-center text-break align-middle">
                        <thead>
                            <tr>
                                <th class="sm-th">編號</th>
                                <th>會員ID</th>
                                <th>商品金額</th>
                                <th>運費</th>
                                <th>物流方式</th>
                                <th>付款方式</th>
                                <th>發票類型</th>
                                <th>訂單狀態</th>
                                <th>成立時間</th>
                                <th>更新時間</th>
                                <th class="sm-th">編輯</th>
                                <th class="sm-th">刪除</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td class="sm-td"><?= $r['order_id'] ?></td>
                                    <td class="name search-field"><?= htmlentities($r['id']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['total']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['fee']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['delivery']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['payment']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['invoice']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['status']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['created']) ?></td>
                                    <td class="search-field"><?= htmlentities($r['updated']) ?></td>
                                    <td class="sm-td">
                                        <a href="orders_edit.php?order_id=<?= $r['order_id'] ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </td>
                                    <td class="sm-td">
                                        <a href="#" class="delete-btn text-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 分頁按鈕區 -->
            <?php include __DIR__ . '/parts/html-pagination.php' ?>

        </div>
    </div>
</div>
<!-- 確認刪除 Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">確認刪除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteMessage" class="alert alert-danger" role="alert">
                    確定要刪除此筆訂單資料嗎？
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">刪除</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
    // 搜尋模塊
    const $_GET = <?= json_encode($_GET) ?>;
    const search = $_GET.search;
    if (search) {
        const searchFields = document.querySelectorAll('.search-field');
        for (let td of searchFields) {
            td.innerHTML = td.innerHTML.split(search).join(`<b>${search}</b>`)
        }
    }


    document.addEventListener("DOMContentLoaded", () => {

        // 表單刪除模塊
        const deleteButtons = document.querySelectorAll(".delete-btn");
        let deleteId = null;
        let deleteRow = null;

        const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
        const deleteMessage = document.getElementById("deleteMessage");
        const confirmDeleteButton = document.getElementById("confirmDelete");

        deleteButtons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();

                deleteRow = e.target.closest("tr");
                deleteId = parseInt(deleteRow.querySelector("td:first-child").innerText.trim(), 10);
                const name = deleteRow.querySelector(".name").innerHTML;
                deleteMessage.innerHTML = `確定要刪除此筆訂單嗎？`;
                deleteModal.show();
            });
        });

        confirmDeleteButton.addEventListener("click", () => {
            if (deleteId) {
                const urlParams = new URLSearchParams(window.location.search);
                const page = urlParams.get("page") || 1;
                const search = urlParams.get("search") || "";

                fetch(`orders_delete-api.php`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            order_id: deleteId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = `orders_list.php?page=${page}&search=${encodeURIComponent(search)}`;
                        } else {
                            alert("刪除失敗：" + (data.message || data.error || '未知錯誤'));
                        }
                        deleteModal.hide(); 
                    })
                    .catch(error => {
                        console.error("刪除請求時發生錯誤:", error);
                        alert("刪除過程中發生前端錯誤，請檢查網路連線或主控台訊息。");
                        deleteModal.hide();
                    });
            }
        });
    });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>