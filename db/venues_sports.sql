-- 場館與運動類別的關聯表
CREATE TABLE venues_sports (
    venue_id INT NOT NULL,
    sport_id INT NOT NULL,
    PRIMARY KEY (venue_id, sport_id),
    FOREIGN KEY (venue_id) REFERENCES venues(id),
    FOREIGN KEY (sport_id) REFERENCES sports(id)
);

-- INSERT INTO venues_sports (venue_id, sport_id) VALUES
