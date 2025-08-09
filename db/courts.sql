-- 場地
CREATE TABLE courts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    venue_id INT NOT NULL,
    sport_id INT NOT NULL,
    FOREIGN KEY (venue_id) REFERENCES venues(id),
    FOREIGN KEY (sport_id) REFERENCES sports(id)
);