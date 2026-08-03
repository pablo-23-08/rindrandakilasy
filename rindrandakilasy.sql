DROP DATABASE IF EXISTS rindrandakilasy_db;

CREATE DATABASE IF NOT EXISTS rindrandakilasy_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE rindrandakilasy_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'logistics_department') NOT NULL
);

CREATE TABLE sessions (
    id_session VARCHAR(128) PRIMARY KEY,
    id_user INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,

    FOREIGN KEY (id_user)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,

    building_id INT NOT NULL,

    name VARCHAR(100) NOT NULL,

    capacity INT NOT NULL,

    description TEXT,

    status ENUM(
        'available',
        'maintenance',
        'disabled'
    ) DEFAULT 'available',

    FOREIGN KEY (building_id)
        REFERENCES buildings(id)
);

CREATE TABLE equipments (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE room_equipments (

    id_room INT NOT NULL,

    id_equipment INT NOT NULL,

    PRIMARY KEY(id_room,id_equipment),

    FOREIGN KEY(id_room)
        REFERENCES rooms(id)
        ON DELETE CASCADE,

    FOREIGN KEY(id_equipment)
        REFERENCES equipments(id)
        ON DELETE CASCADE
);

CREATE TABLE reservations (

    id INT AUTO_INCREMENT PRIMARY KEY,

    id_room INT NOT NULL,

    id_user INT NOT NULL,

    purpose VARCHAR(255) NOT NULL,

    start_datetime DATETIME NOT NULL,

    end_datetime DATETIME NOT NULL,

    status ENUM(
        'pending',
        'approved',
        'refused',
        'cancelled'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    validated_at DATETIME NULL,

    refusal_reason TEXT NULL,

    validated_by INT NULL,

    FOREIGN KEY(id_room)
        REFERENCES rooms(id),

    FOREIGN KEY(id_user)
        REFERENCES users(id),

    FOREIGN KEY(validated_by)
        REFERENCES users(id)
);

CREATE TABLE reservation_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    reservation_id INT NOT NULL,

    id_user INT NOT NULL,

    action VARCHAR(50) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(reservation_id)
        REFERENCES reservations(id)
        ON DELETE CASCADE,

    FOREIGN KEY(id_user)
        REFERENCES users(id)
);

CREATE TABLE reports (

    id INT AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(200),

    type ENUM(
        'csv',
        'pdf'
    ),

    file_path VARCHAR(255),

    generated_by INT,

    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(generated_by)
        REFERENCES users(id)
);



INSERT INTO users (name, email, password, role)
VALUES (
    'Administrateur',
    'admin@gmail.com',
    '$2y$10$aJNT29Sq9cM7We06o0bAIeEJF.9MMTU7UaCQUXh7Q9CJ9Ld0P8OOi',
    'admin'
);

INSERT INTO users (name, email, password, role)
VALUES (
    'pablo',
    'yiarivo@gmail.com',
    '$2y$10$aJNT29Sq9cM7We06o0bAIeEJF.9MMTU7UaCQUXh7Q9CJ9Ld0P8OOi',
    'student'
);

INSERT INTO users (name, email, password, role)
VALUES (
    'Logistics',
    'logistics@gmail.com',
    '$2y$10$aJNT29Sq9cM7We06o0bAIeEJF.9MMTU7UaCQUXh7Q9CJ9Ld0P8OOi',
    'logistics_department'
);

INSERT INTO users (name, email, password, role)
VALUES (
    'Escobar',
    'teacher@gmail.com',
    '$2y$10$aJNT29Sq9cM7We06o0bAIeEJF.9MMTU7UaCQUXh7Q9CJ9Ld0P8OOi',
    'teacher'
);
