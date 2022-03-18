<?php
// Informations d'identification
define('DB_SERVER', 'database');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', $_ENV['MYSQL_ROOT_PASSWORD']);
define('DB_NAME', 'Doctolib-SQL');

// Connexion à la base de données MySQL 
$conn = mysqli_connect("database", "root", $_ENV['MYSQL_ROOT_PASSWORD'], DB_NAME);

// Vérifier la connexion
if ($conn === false) {
    die("ERREUR : Impossible de se connecter. " . mysqli_connect_error());
}
?>