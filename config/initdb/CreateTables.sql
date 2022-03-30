
CREATE TABLE IF NOT EXISTS login (
    user_id INT AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    grade ENUM('CLIENT', 'DOCTOR') NOT NULL,
    password VARCHAR(255) NOT NULL,
    enabled BOOLEAN NOT NULL,
    PRIMARY KEY (user_id)
);

INSERT INTO `login` VALUES (NULL, 'admin', 'admin@gmail.com', 'CLIENT', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', true);

CREATE TABLE IF NOT EXISTS client (
    client_id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    birthday DATE NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    comment VARCHAR(255),
    PRIMARY KEY (client_id),
    FOREIGN KEY (user_id) REFERENCES login(user_id)
);

CREATE TABLE IF NOT EXISTS doctor (
    doctor_id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL UNIQUE,
    office VARCHAR(100) NOT NULL,
    activity_days JSON NOT NULL,
    str_hour TIME NOT NULL,
    end_hour TIME NOT NULL,
    PRIMARY KEY (doctor_id),
    FOREIGN KEY (user_id) REFERENCES login(user_id)
);

CREATE TABLE IF NOT EXISTS appointment (
    doctor_id INT NOT NULL,
    client_id INT NOT NULL,
    datetime DATETIME NOT NULL UNIQUE,
    PRIMARY KEY (doctor_id, client_id),
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id),
    FOREIGN KEY (client_id) REFERENCES client(client_id)
);