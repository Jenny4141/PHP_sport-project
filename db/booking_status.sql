-- 預約狀況
CREATE TABLE booking_status (
  booking_status_id INT NOT NULL PRIMARY KEY,
  status_name VARCHAR(20) NOT NULL
);INSERT INTO booking_status (booking_status_id, status_name) VALUES
(0, '未付款'),
(1, '已付款'),
(2, '取消');