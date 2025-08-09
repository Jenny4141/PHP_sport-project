-- 專長表單
CREATE TABLE specialties (
    specialty_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    specialty_name VARCHAR(50) NOT NULL
);
INSERT INTO specialties (specialty_name) VALUES
('瑜珈'),
('皮拉提斯'),
('有氧舞蹈'),
('TRX懸吊訓練'),
('韻律教室課程'),
('健身訓練'),
('舞蹈'),
('拳擊'),
('跆拳道'),
('空手道'),
('柔道'),
('劍道'),
('太極'),
('兒童體操'),
('兒童遊戲');