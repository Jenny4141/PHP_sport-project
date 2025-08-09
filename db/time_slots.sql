CREATE TABLE time_slots (
    id TINYINT PRIMARY KEY AUTO_INCREMENT,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    period_id INT NOT NULL,
    FOREIGN KEY (period_id) REFERENCES time_periods(id)
);

INSERT INTO time_slots (start_time, end_time, period_id) VALUES
('06:00:00', '07:00:00', 1),
('07:00:00', '08:00:00', 1),
('08:00:00', '09:00:00', 1),
('09:00:00', '10:00:00', 1),
('10:00:00', '11:00:00', 1),
('11:00:00', '12:00:00', 1),
('12:00:00', '13:00:00', 2),
('13:00:00', '14:00:00', 2),
('14:00:00', '15:00:00', 2),
('15:00:00', '16:00:00', 2),
('16:00:00', '17:00:00', 2),
('17:00:00', '18:00:00', 2),
('18:00:00', '19:00:00', 3),
('19:00:00', '20:00:00', 3),
('20:00:00', '21:00:00', 3),
('21:00:00', '22:00:00', 3);