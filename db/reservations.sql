-- 場地訂單
CREATE TABLE reservations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    member_id BIGINT NOT NULL,
    court_timeslot_id BIGINT NOT NULL,
    date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (court_timeslot_id) REFERENCES courts_timeslots(id),
    FOREIGN KEY (status_id) REFERENCES reservation_statuses(id)
);

ALTER TABLE reservations ADD COLUMN price DECIMAL(10,2) NOT NULL;
