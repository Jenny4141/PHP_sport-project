-- 地區
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

INSERT INTO locations (name) VALUES
('台北市'),
('新北市'),
('桃園市'),
('臺中市'),
('高雄市');