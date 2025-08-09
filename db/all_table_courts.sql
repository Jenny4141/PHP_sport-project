-- 地區
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

INSERT INTO locations (name) VALUES
('台北市'),
('新北市'),
('桃園市'),
('臺中市'),
('高雄市');

-- 場館
CREATE TABLE venues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location_id INT NOT NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id)
);
INSERT INTO venues (name, location_id) VALUES
('北投運動中心', 1),
('士林運動中心', 1),
('內湖運動中心', 1),
('南港運動中心', 1),
('松山運動中心', 1),
('信義運動中心', 1),
('大同運動中心', 1),
('中山運動中心', 1),
('萬華運動中心', 1),
('中正運動中心', 1),
('大安運動中心', 1),
('文山運動中心', 1),
('新莊國民運動中心', 2),
('蘆洲國民運動中心', 2),
('淡水國民運動中心', 2),
('三重國民運動中心', 2),
('土城國民運動中心', 2),
('中和國民運動中心', 2),
('板橋國民運動中心', 2),
('泰山國民運動中心', 2),
('永和國民運動中心', 2),
('汐止國民運動中心', 2),
('樹林國民運動中心', 2),
('鶯歌國民運動中心', 2),
('三峽國民運動中心', 2),
('林口國民運動中心', 2),
('五股國民運動中心', 2),
('新店國民運動中心', 2);

-- 場館與運動類別的關聯表
CREATE TABLE venues_sports (
    venue_id INT NOT NULL,
    sport_id INT NOT NULL,
    PRIMARY KEY (venue_id, sport_id),
    FOREIGN KEY (venue_id) REFERENCES venues(id),
    FOREIGN KEY (sport_id) REFERENCES sports(id)
);

-- INSERT INTO venues_sports (venue_id, sport_id) VALUES

-- 場地
CREATE TABLE courts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    venue_id INT NOT NULL,
    sport_id INT NOT NULL,
    FOREIGN KEY (venue_id) REFERENCES venues(id),
    FOREIGN KEY (sport_id) REFERENCES sports(id)
);

-- 時間區段 早中晚
CREATE TABLE time_periods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL
);

INSERT INTO time_periods (name) VALUES
('早上'),
('下午'),
('晚上');

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

-- 場地與時間的關聯表
CREATE TABLE courts_timeslots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    court_id INT NOT NULL,
    time_slot_id TINYINT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (court_id) REFERENCES courts(id),
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
);

-- 場地訂單狀態
CREATE TABLE reservation_statuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL
);

INSERT INTO reservation_statuses (name) VALUES
('已付款'),
('待付款'),
('未付款');

-- 場地訂單
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