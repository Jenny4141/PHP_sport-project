-- 商品圖片
CREATE TABLE images (
	image_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	product_id INT NOT NULL,
	image_url TEXT NOT NULL,
	image_order INT NOT NULL,
	FOREIGN KEY (product_id) REFERENCES products(product_id)
);

INSERT INTO images (image_id, product_id, image_url, image_order) VALUES
(1, 1, 'spec01.jpeg', 1),
(2, 2, 'spec02.jpeg', 1),
(3, 3, 'spec03.jpeg', 1),
(4, 4, 'spec04.jpeg', 1),
(5, 5, 'spec05.jpeg', 1),
(6, 6, 'spec06.jpeg', 1),
(7, 7, 'spec07.jpeg', 1),
(8, 8, 'spec08.jpeg', 1),
(9, 9, 'spec09.jpeg', 1),
(10, 10, 'spec10.jpeg', 1),
(11, 11, 'spec11.jpeg', 1),
(12, 12, 'spec12.jpeg', 1),
(13, 13, 'spec13.jpeg', 1),
(14, 14, 'spec14.jpeg', 1),
(15, 15, 'spec15.jpeg', 1),
(16, 16, 'spec16.jpeg', 1),
(17, 17, 'spec17.jpeg', 1),
(18, 18, 'spec18.jpeg', 1),
(19, 19, 'spec19.jpeg', 1),
(20, 20, 'spec20.jpeg', 1),
(21, 21, 'spec21.jpeg', 1),
(22, 22, 'spec22.jpeg', 1);