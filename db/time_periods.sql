-- 時間區段 早中晚
CREATE TABLE time_periods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL
);

INSERT INTO time_periods (name) VALUES
('早上'),
('下午'),
('晚上');