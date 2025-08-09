-- 商品資料
CREATE TABLE products (
	product_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(50) NOT NULL,
	brand_id INT NOT NULL,
	sport_id INT NOT NULL,
	created DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (sport_id) REFERENCES sports(id),
	FOREIGN KEY (brand_id) REFERENCES brands(brand_id)
);

INSERT INTO products (product_id, name, brand_id, sport_id) VALUES
(1, '極限飛馳籃球鞋', 1, 1),
(2, '冠軍系列標準七號籃球', 9, 1),
(3, '疾速之星足球', 2, 27),
(4, '專業訓練五號足球', 10, 27),
(5, '金手套捕手手套', 5, 28),
(6, '強打少年鋁合金球棒', 8, 28),
(7, '碳纖維進階網球拍', 8, 4),
(8, '訓練級網球', 13, 4),
(9, '攻擊型碳素羽球拍', 7, 2),
(10, '比賽級鵝毛羽球', 18, 2),
(11, '奧運競賽標準排球', 10, 5),
(12, '五星級成品桌球拍', 17, 3),
(13, '三星訓練桌球', 17, 3),
(14, '專業格鬥拳擊手套', 20, 19),
(15, '2KG啞鈴組', 13, 17),
(16, '專業硬舉訓練鞋', 1, 17),
(17, '環保無毒TPE瑜珈墊', 13, 12),
(18, '輕巧TPE材質瑜珈墊', 13, 12),
(19, '比賽訓練足球', 3, 27),
(20, '少年軟式棒球棒', 5, 28),
(21, '專用訓練排球五號排球', 5, 5),
(22, '桌球拍組', 17, 3);
