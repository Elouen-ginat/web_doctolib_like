
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
    comment VARCHAR(255),
    PRIMARY KEY (doctor_id, client_id, datetime),
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id),
    FOREIGN KEY (client_id) REFERENCES client(client_id)
);

INSERT INTO `login` VALUES (NULL, 'EG', 'EG@gmail.com', 'CLIENT', '680993ea7ad5ee52ec319d28d326dcc7b4f42315c9c714aadd6d37d4cbfc7af2', true), 
(NULL, 'CF', 'CF@gmail.com', 'CLIENT', '3485d9e01147e534f4b114b99a848f0f0672e6e6caaff95457604e9903246942', true), 
(NULL, 'ZB', 'ZB@gmail.com', 'CLIENT', '4b044f918ba7405caee916d5928ca5fdbbdb97d9cd05c29d9e19e2b2bf985b02', true), 
(NULL, 'LPL', 'LPL@gmail.com', 'CLIENT', '4569e5032693c4038cdac78462c5acf6bd9f65097861a49a2b40f255f3340ac0', true),
(NULL, 'OB', 'OB@gmail.com', 'DOCTOR', '1808ec0cc9e5de472831862f1eb5dd9cfead3a7f85265bb3d64c5df3f9981cdd', true), 
(NULL, 'LT', 'LT@gmail.com', 'DOCTOR', '164fdd92b1eeddc0d9727131ae3d026c41b1ced4a7a076b2d411fab1e5ec64cc', true), 
(NULL, 'VC', 'VC@gmail.com', 'DOCTOR', '88e6c001091f71bc66ff59650f4da52058bd41924df2477f092089c6c15feee3', true);

INSERT INTO client VALUES ( NULL, 2,  'Elouen', 'Ginat', '01/01/1999', '15 rue Pascal, 35170 Bruz', '0610121315','EG@gmail.com' , 'Bonjour je suis un petit peu timide mais sinon ça va' ),
( NULL, 3, 'Clément', 'François', '18/10/1999', '16 rue du Volley, 35170 Bruz', '0670787910','CF@gmail.com' , 'Bonjour je juste posé en train de décrire ma journée' ),
( NULL, 4,  'Zacharie', 'Bouhin', '06/05/1998', '17 rue Ensai Studio, 35170 Bruz', '0610132515','ZB@gmail.com' , '' ),
( NULL, 5 , 'Louis', 'PL', '21/12/1999', '20 rue du 18 et 19, 35170 Bruz', '0615269875','LPL@gmail.com' , 'RAS' );

INSERT INTO doctor VALUES (NULL, 6, 'Olivier', 'Biau', '0607787945', 'Bagneux', {'monday':true, 'tuesday':true, 'friday' : true}, 08:15:00, 14:55:00),
(NULL, 7, 'Laurent', 'Tardif', '0182565567', 'Paris', {'monday':true, 'tuesday':true}, 07:15:00, 17:55:00),
(NULL, 8, 'Valerie', 'Chretien', '0608858545', 'Lorient', {'monday':true, 'tuesday':true, 'friday' : true}, 09:20:00, 12:55:00);
