<?php include __DIR__ . '/parts/init.php'; # 初始化頁面
$title = 'Dashboard';
$pageName = 'index_';

$colors = [
  '預約場地' => 'primary',
  '商城訂單' => 'success',
  '揪團報名' => 'orange',
  '課程預約' => 'danger'
];

$icon = [
  '預約場地' => 'fa-regular fa-calendar fa-lg',
  '商城訂單' => 'fa-regular fa-credit-card fa-lg',
  '揪團報名' => 'fa-solid fa-users-rectangle fa-lg',
  '課程預約' => 'fa-solid fa-chalkboard-user fa-lg'
];

$sql = "SELECT '預約場地' AS order_type, COUNT(*) AS total_orders FROM reservations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        UNION ALL
        SELECT '商城訂單', COUNT(*) FROM orders WHERE created >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        UNION ALL
        SELECT '揪團報名', COUNT(*) FROM teams WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        UNION ALL
        SELECT '課程預約', COUNT(*) FROM booking WHERE create_time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";

$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sql_latest_orders = "SELECT 
    r.id AS order_id, 
    '預約場地' AS order_type, 
    m.username AS member_name,
    r.price AS price, 
    r.created_at AS created, 
    rs.name AS status_name
FROM reservations r
JOIN reservation_statuses rs ON r.status_id = rs.id
JOIN members m ON r.member_id = m.id 

UNION ALL

SELECT 
    o.order_id, 
    '商城訂單' AS order_type, 
    m.username AS member_name, 
    o.total AS price, 
    o.created AS created, 
    o.status AS status_name
FROM orders o
JOIN members m ON o.member_id = m.id

UNION ALL

SELECT 
    t.id AS order_id, 
    '揪團報名' AS order_type, 
    t.name AS member_name,  -- 這裡用隊伍名稱
    NULL AS price, 
    t.created_at AS created, 
    t.member_count AS status_name
FROM teams t

UNION ALL

SELECT 
    b.booking_id, 
    '課程預約' AS order_type, 
    m.username AS member_name, 
    b.price AS price, 
    b.create_time AS created, 
    bs.status_name AS status_name
FROM booking b
JOIN members m ON b.member_id = m.id
JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id

ORDER BY created DESC
LIMIT 10;";

$stmt_latest_orders = $pdo->query($sql_latest_orders);
$latestOrders = $stmt_latest_orders->fetchAll(PDO::FETCH_ASSOC);

?>
<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<div class="container-fluid">

  <div class="mb-5 px-3 row justify-content-center g-4">
    <h2 class="fs-4 text-sky-blue">訂單統計</h2>
    <?php foreach ($orders as $order): ?>
      <div class="col-12 col-sm-6 col-md-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="card-text d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title"><?= htmlentities($order['order_type']) ?></h5>
              <h6 class="card-subtitle mb-2 text-ocean-blue">過去 1 個月</h6>
            </div>
            <div class="card-text d-flex align-items-center gap-3">
              <div class="rounded-circle d-flex justify-content-center align-items-center bg-<?= $colors[$order['order_type']] ?>-subtle"
                style="width: 50px; height: 50px; aspect-ratio: 1 / 1;">
                <i class="<?= $icon[$order['order_type']] ?> text-<?= $colors[$order['order_type']] ?? 'light' ?>"></i>
              </div>
              <strong class="fs-2"><?= $order['total_orders'] ?></strong>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

  </div>

  <div class="mb-5 px-3 row justify-content-center g-4">
    <h2 class="fs-4 text-sky-blue">趨勢圖表</h2>

    <div class="col-12 col-sm-12 col-md-6 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-3 border-bottom border-secondary-subtle">
            <h5 class="card-title border-t">各運動場館數量</h5>
            <a href="#" class="card-link"><i class="fa-solid fa-angle-right"></i></a>
          </div>
          <canvas id="myChart1"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-12 col-md-6 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-3 border-bottom border-secondary-subtle">
            <h5 class="card-title border-t">各場館的運動類型</h5>
            <a href="#" class="card-link"><i class="fa-solid fa-angle-right"></i></a>
          </div>
          <canvas id="myChart2"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-12 col-md-6 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-3 border-bottom border-secondary-subtle">
            <h5 class="card-title border-t">各場館的收入統計</h5>
            <a href="#" class="card-link"><i class="fa-solid fa-angle-right"></i></a>
          </div>
          <canvas id="myChart3"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-3 border-bottom border-secondary-subtle">
            <h5 class="card-title border-t">各時段的預訂數量</h5>
            <a href="#" class="card-link"><i class="fa-solid fa-angle-right"></i></a>
          </div>
          <canvas id="myChart4"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-3 border-bottom border-secondary-subtle">
            <h5 class="card-title border-t">預訂付款狀態</h5>
            <a href="#" class="card-link"><i class="fa-solid fa-angle-right"></i></a>
          </div>
          <canvas id="myChart5"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-3 border-bottom border-secondary-subtle">
            <h5 class="card-title border-t">商城出貨狀態</h5>
            <a href="#" class="card-link"><i class="fa-solid fa-angle-right"></i></a>
          </div>
          <canvas id="myChart6"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="mb-5 px-3 row justify-content-center g-4">
    <h2 class="fs-4 text-sky-blue">最新訂單</h2>
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-1">
            <h5 class="card-title"><span class="badge text-bg-danger">New</span></h5>
            <a href="#" class="card-link"><i class="fa-solid fa-angle-right"></i></a>
          </div>

          <div class="table-responsive">
            <table class="mb-0 table table-bordered text-center text-break align-middle">
              <thead>
                <tr>
                  <th>訂單類別</th>
                  <th class="sm-th">編號</th>
                  <th>會員</th>
                  <th>價格</th>
                  <th>成立時間</th>
                  <th>狀態</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($latestOrders as $latestOrder): ?>
                  <tr class="table-<?= $colors[$latestOrder['order_type']] ?? 'table-light' ?>">
                    <td><?= htmlentities($latestOrder['order_type']) ?></td>
                    <td><?= $latestOrder['order_id'] ?></td>
                    <td><?= htmlentities($latestOrder['member_name']) ?></td>
                    <td><?= $latestOrder['order_type'] === '揪團報名' ? '無' : number_format($latestOrder['price'] ?? 0, 0) . ' 元' ?></td>
                    <td><?= htmlentities($latestOrder['created']) ?></td>
                    <td><?= $latestOrder['order_type'] === '揪團報名' ? ($latestOrder['status_name'] . ' / 8') : htmlentities($latestOrder['status_name'] ?? '無') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    fetch("get_dashboard_data.php")
      .then(response => response.json())
      .then(json => {
        if (!json.success || typeof json.data !== "object") { // ✅ 檢查是否是物件
          console.error("API 返回的 data 不是物件:", json);
          return;
        }
        console.log("API 返回的數據:", json.data);

        // 繪製「各運動場館數量」的長條圖
        const labels1 = json.data.sports_venues.map(item => item.sport_name);
        const values1 = json.data.sports_venues.map(item => item.venue_count);
        new Chart(document.getElementById("myChart1").getContext("2d"), {
          type: "bar",
          data: {
            labels: labels1,
            datasets: [{
              label: "場館數量",
              data: values1,
              backgroundColor: ["#0041cf8e", "#fe66018e"]
            }]
          }
        });

        // 繪製「各時段的預訂數量」的折線圖
        const labels2 = json.data.reservations_sports.map(item => item.sport_name);
        const values2 = json.data.reservations_sports.map(item => item.reservation_count);
        new Chart(document.getElementById("myChart2").getContext("2d"), {
          type: "bar",
          data: {
            labels: labels2,
            datasets: [{
              label: "預訂數量",
              data: values2,
              backgroundColor: ["#0041cf8e", "#fe66018e"]
            }]
          }
        });

        // 各場館收入統計（長條圖）
        const labels3 = json.data.venues_revenue.map(item => item.venue_name);
        const values3 = json.data.venues_revenue.map(item => item.total_revenue);
        new Chart(document.getElementById("myChart3").getContext("2d"), {
          type: "bar",
          data: {
            labels: labels3,
            datasets: [{
              label: "收入",
              data: values3,
              backgroundColor: ["#0041cf8e", "#fe66018e"]
            }]
          }
        });

        // 繪製「各時段的預訂數量」的折線圖
        const labels4 = json.data.reservations_timeslots.map(item => "時段 " + item.time_slot);
        const values4 = json.data.reservations_timeslots.map(item => item.reservation_count);
        new Chart(document.getElementById("myChart4").getContext("2d"), {
          type: "line",
          data: {
            labels: labels4,
            datasets: [{
              label: "預訂數量",
              data: values4,
              backgroundColor: ["#0041cf8e", "#fe66018e"],
              borderColor: ["#1774d38e"]
            }]
          }
        });


        // 預訂付款狀態（圓餅圖）
        const labels5 = json.data.reservations_status.map(item => item.status_name);
        const values5 = json.data.reservations_status.map(item => item.status_count);
        new Chart(document.getElementById("myChart5").getContext("2d"), {
          type: "pie",
          data: {
            labels: labels5,
            datasets: [{
              label: "付款狀態",
              data: values5,
              backgroundColor: ["#1774d38e", "#fecc998e", "#fc7f008e"]
            }]
          }
        });

        // 商城出貨狀態（圓餅圖）
        const labels6 = json.data.orders_status_percentage.map(item => item.status_name);
        const values6 = json.data.orders_status_percentage.map(item => item.status_count);
        new Chart(document.getElementById("myChart6").getContext("2d"), {
          type: "pie",
          data: {
            labels: labels6,
            datasets: [{
              label: "出貨狀態",
              data: values6,
              backgroundColor: ["#1774d38e", "#fecc998e", "#fc7f008e"]
            }]
          }
        });
      });
  });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>