CREATE DATABASE IF NOT EXISTS bus_tracker_db;

USE bus_tracker_db;

CREATE TABLE IF NOT EXISTS bus_location (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id VARCHAR(50) NOT NULL,
    from_city VARCHAR(100),
    to_city VARCHAR(100),
    crowd_level VARCHAR(20) DEFAULT 'Medium',
    status VARCHAR(20) DEFAULT 'Running',
    latitude FLOAT NOT NULL,
    longitude FLOAT NOT NULL,
    last_moving_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
ALTER TABLE bus_location
    ADD UNIQUE KEY uniq_bus_location_bus (bus_id);

CREATE TABLE IF NOT EXISTS passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS passenger_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    bus_id VARCHAR(50) NOT NULL,
    from_city VARCHAR(100),
    to_city VARCHAR(100),
    travel_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_bus_id VARCHAR(50) NOT NULL,
    sender_type ENUM('passenger', 'driver') NOT NULL,
    sender_id VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bus_emergencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id VARCHAR(50) NOT NULL,
    driver_username VARCHAR(100) NOT NULL,
    issue_type VARCHAR(100) NOT NULL,
    message TEXT,
    status VARCHAR(20) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lost_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id VARCHAR(50) NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_phone VARCHAR(20) NOT NULL,
    item_description TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'Lost',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id VARCHAR(50) NOT NULL UNIQUE,
    bus_name VARCHAR(100) NOT NULL
);

-- Insert Mock Data for Demo
INSERT IGNORE INTO buses (bus_id, bus_name) VALUES 
('KA-21-F-4455', 'KSRTC Rajahamsa'),
('KA-19-D-2233', 'VRL Coastal Travels'),
('KA-19-A-9988', 'Kerala State Transport'),
('KA-21-B-5566', 'Durgamba Motors'),
('KL-14-C-7777', 'Sugama Tourist');

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    bus_id VARCHAR(50) NOT NULL DEFAULT '',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS active_trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    bus_id VARCHAR(50) NOT NULL,
    from_city VARCHAR(100) NOT NULL,
    to_city VARCHAR(100) NOT NULL,
    crowd_level VARCHAR(20) DEFAULT 'Medium',
    status VARCHAR(30) DEFAULT 'Running',
    latitude FLOAT NOT NULL,
    longitude FLOAT NOT NULL,
    last_moving_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_ping_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_active_driver (driver_id),
    UNIQUE KEY uniq_active_bus (bus_id)
);

CREATE TABLE IF NOT EXISTS trip_history_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    bus_id VARCHAR(50) NOT NULL,
    from_city VARCHAR(100) NOT NULL,
    to_city VARCHAR(100) NOT NULL,
    start_lat FLOAT NULL,
    start_lng FLOAT NULL,
    end_lat FLOAT NULL,
    end_lng FLOAT NULL,
    ended_reason VARCHAR(30) DEFAULT 'manual',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL
);

-- Insert Default Master Admin (Password: admin123)
-- We use a plain MD5 hash here for simplicity in the demo, real prod uses proper hashing.
INSERT IGNORE INTO admins (username, password) VALUES 
('admin', MD5('admin123'));

CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL UNIQUE
);

INSERT IGNORE INTO cities (city_name) VALUES 
('Bangalore'), ('Mangalore'), ('Puttur'), ('Udupi'), ('Kasaragod'), ('Dharwad'), ('Sullia'), ('Belthangady'),
('Bantwal'), ('Moodabidri'), ('Karkala'), ('Kundapura'), ('Byndoor'), ('Bhatkal'), ('Honnavar'), ('Kumta'),
('Gokarna'), ('Karwar'), ('Ankola'), ('Sirsi'), ('Siddapur'), ('Yellapur'), ('Haliyal'), ('Joida'),
('Mundgod'), ('Sagara'), ('Soraba'), ('Shikaripura'), ('Shivamogga (Shimoga)'), ('Bhadravathi'),
('Tarikere'), ('Kadur'), ('Chikkamagaluru'), ('Mudigere'), ('Koppa'), ('Sringeri'), ('Narasimharajapura'),
('Hassan'), ('Belur'), ('Halebeedu'), ('Sakleshpur'), ('Alur'), ('Arkalgud'), ('Holenarasipura'),
('Channarayapatna'), ('Arsikere'), ('Tumakuru (Tumkur)'), ('Gubbi'), ('Tiptur'), ('Chikkanayakanahalli'),
('Sira'), ('Madhugiri'), ('Koratagere'), ('Pavagada'), ('Kunigal'), ('Turuvekere'), ('Ramanagara'),
('Channapatna'), ('Kanakapura'), ('Magadi'), ('Mandya'), ('Maddur'), ('Malavalli'), ('Srirangapatna'),
('Pandavapura'), ('Krishnarajapet'), ('Nagamangala'), ('Mysuru (Mysore)'), ('Tirumakudalu Narasipura'),
('Nanjangud'), ('Heggadadevanakote'), ('Hunsur'), ('Piriyapatna'), ('Saligrama'), ('Chamarajanagar'),
('Gundlupet'), ('Kollegal'), ('Yelandur'), ('Hanur'), ('Madikeri'), ('Virajpet'), ('Somwarpet'),
('Hubballi (Hubli)'), ('Kalghatgi'), ('Kundgol'), ('Navalgund'), ('Belagavi (Belgaum)'), ('Athani'),
('Bailhongal'), ('Chikkodi'), ('Gokak'), ('Hukkeri'), ('Khanapur'), ('Raibag'), ('Ramdurg'), ('Savadatti'),
('Vijayapura (Bijapur)'), ('Bagalkot'), ('Jamkhandi'), ('Mudhol'), ('Badami'), ('Ilkal'), ('Gadag'),
('Koppal'), ('Gangavathi'), ('Hosapete (Hospet)'), ('Ballari (Bellary)'), ('Siruguppa'), ('Sandur'),
('Raichur'), ('Sindhanur'), ('Kalaburagi (Gulbarga)'), ('Sedam'), ('Chittapur'), ('Aland'), ('Afzalpur'),
('Jewargi'), ('Chincholi'), ('Yadgir'), ('Shahapur'), ('Surpur'), ('Bidar'), ('Basavakalyan'), ('Bhalki'),
('Humnabad'), ('Aurad'), ('Davangere'), ('Harihara'), ('Jagalur'), ('Honnali'), ('Channagiri'), ('Chitradurga'),
('Challakere'), ('Hiriyur'), ('Holalkere'), ('Hosadurga'), ('Molakalmuru'), ('Haveri'), ('Ranebennur'),
('Hirekerur'), ('Shiggaon'), ('Savanoor'), ('Byadgi'), ('Kolar'), ('Bangarapet'), ('Malur'), ('Mulbagal'),
('Srinivaspur'), ('Kolar Gold Fields (KGF)'), ('Chikkaballapur'), ('Gauribidanur'), ('Bagepalli'), 
('Gudibanda'), ('Chintamani'), ('Sidlaghatta'), ('Kukke Subramanya'), ('Dharmasthala'),
('Manipal'), ('Ujire'), ('Uppinangady'), ('Vittal'), ('Kotekar'), ('Someshwara'), ('Ullal'), ('Surathkal');
