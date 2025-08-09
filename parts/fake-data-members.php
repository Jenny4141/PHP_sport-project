<?php
require __DIR__ . "/db-connect.php";
exit;

// 隨機資料工具
function randomString($length) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
}

function randomEmail($i) {
    return "user{$i}_" . randomString(5) . "@example.com";
}

function randomPhone($i) {
    return "09" . str_pad((string)rand(10000000, 99999999), 8, "0", STR_PAD_LEFT);
}

function randomDate($start = '1980-01-01', $end = '2010-12-31') {
    $timestamp = rand(strtotime($start), strtotime($end));
    return date("Y-m-d", $timestamp);
}

// 預先準備語法
$lasts = ["何","傅","劉","吳","呂","周","唐","孫","宋","張","彭","徐","於","曹","曾","朱","李","林","梁","楊","沈","王","程","羅","胡","董","蕭","袁","許","謝","趙","郭","鄧","鄭","陳","韓","馬","馮","高","黃"];

$firsts = ["冠廷","冠宇","宗翰","家豪","彥廷","承翰","柏翰","宇軒","家瑋","冠霖","雅婷","雅筑","怡君","佳穎","怡萱","宜庭","郁婷","怡婷","詩涵","鈺婷"];

$areas = ["臺北市","新北市","桃園市","臺中市","臺南市","高雄市","新竹縣","苗栗縣","彰化縣","南投縣","雲林縣","嘉義縣","屏東縣","宜蘭縣","花蓮縣","臺東縣","澎湖縣","金門縣","連江縣","基隆市","新竹市","嘉義市"];
$sql = "INSERT INTO members (
    username, email, password, full_name, phone_number,
    gender, birth_date, avatar_url, address,
    is_active, email_verified, phone_verified, role
) VALUES (
    :username, :email, :password, :full_name, :phone_number,
    :gender, :birth_date, :avatar_url, :address,
    :is_active, :email_verified, :phone_verified, :role
)";
$stmt = $pdo->prepare($sql);

// 開始插入 1000 筆
for ($i = 1; $i <= 1000; $i++) {
    shuffle($lasts);
    shuffle($firsts);
    shuffle($areas);

    $data = [
        ':username'       => "user_" . randomString(6) . "_$i",
        ':email'          => randomEmail($i),
        ':password'       => hash("sha256", randomString(12)), // 模擬加密
        ':full_name'      => $lasts[0]. $firsts[0],
        ':phone_number'   => randomPhone($i),
        ':gender'         => rand(0, 1),
        ':birth_date'     => randomDate(),
        ':avatar_url'     => (rand(0, 1) ? "https://example.com/avatar/$i.jpg" : NULL),
        ':address'        => $areas[0],
        ':is_active'      => rand(0, 1),
        ':email_verified' => rand(0, 1),
        ':phone_verified' => rand(0, 1),
        ':role'           => (rand(1, 20) === 1) ? 'admin' : 'user',
    ];

    try {
        $stmt->execute($data);
    } catch (PDOException $e) {
        echo "第 $i 筆錯誤: " . $e->getMessage() . "\n";
        // 若重複可略過，或改用 INSERT IGNORE 或 ON DUPLICATE KEY
    }
}

// 回傳影響的筆數
echo json_encode([
    "affected_rows" => $stmt->rowCount(),
    "last_insert_id" => $pdo->lastInsertId(),
]);
// echo "✅ 成功產生 1000 筆資料！";
?>