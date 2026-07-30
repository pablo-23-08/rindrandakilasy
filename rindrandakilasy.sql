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