-- 場地訂單狀態
CREATE TABLE reservation_statuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL
);

INSERT INTO reservation_statuses (name) VALUES
('已付款'),
('待付款'),
('未付款');