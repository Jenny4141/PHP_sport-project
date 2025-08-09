-- 品牌種類
CREATE TABLE brands (
	brand_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(50)
);

INSERT INTO brands (brand_id, name) VALUES
(1, 'Nike'), 
(2, 'Adidas'),
(3, 'Puma'), 
(4, 'Under Armour'), 
(5, 'Mizuno'),
(6, 'Asics'), 
(7, 'Yonex'), 
(8, 'Wilson'), 
(9, 'Spalding'), 
(10, 'Molten'),
(11, 'Titleist'), 
(12, 'Callaway'), 
(13, 'Decathlon'), 
(14, 'Li-Ning'), 
(15, 'Anta'),
(16, 'Jordan'), 
(17, 'Butterfly'),
(18, 'VICTOR'), 
(19, 'Giant'), 
(20, 'Everlast');