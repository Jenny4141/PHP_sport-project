-- 場地與時間的關聯表
CREATE TABLE courts_timeslots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    court_id INT NOT NULL,
    time_slot_id TINYINT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (court_id) REFERENCES courts(id),
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
);