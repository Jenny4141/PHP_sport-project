CREATE DATABASE IF NOT EXISTS sports_database;

USE sports_database;

-- SELECT DATABASE();

-- DROP DATABASE sports_database;

-- DROP TABLE sports;

-- 運動類別
CREATE TABLE sports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL
);
SELECT * FROM sports;
INSERT INTO sports (name) VALUES
-- 可預約場地（依熱門程度排序）
('籃球'),
('羽球'),
('桌球'),
('網球'),
('排球'),
('游泳'),
('體適能訓練'),
('飛輪'),
('壁球'),
('攀岩'),
('射箭'),

-- 提供課程或體驗（依熱門程度排序）
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
('兒童遊戲'),

-- 商城會使用到的運動項目
('足球'),
('棒球');

-- 無法在國民運動中心從事（僅提供分類保存）
/*
('滑板'),
('直排輪'),
('棒球'),
('足球'),
('田徑'),
('滑冰'),
('高爾夫'),
('射擊'),
('健走'),
('登山'),
('滑雪'),
('潛水'),
('水上摩托'),
('滑水'),
('風帆'),
('衝浪'),
('划船'),
('獨木舟'),
('龍舟'),
('馬術'),
('撞球');
*/