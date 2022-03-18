<?php
require('config.php');
session_start();
function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}

function username_valid(mysqli $conn, string $username)
{
    // Get all user names
    $query = "SELECT username FROM `login`";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_row($result)) {
        if ($row[0] == $username) {
            return false;
        }
    }
    return true;
}

if (isset($_POST['submit'])) {
    if (isset($_REQUEST['username'], $_REQUEST['email'], $_REQUEST['password'], $_REQUEST['grade'])) {
        // récupérer le nom d'utilisateur et supprimer les antislashes ajoutés par le formulaire
        $username = stripslashes($_REQUEST['username']);
        $username = mysqli_real_escape_string($conn, $username);
        // récupérer l'email et supprimer les antislashes ajoutés par le formulaire
        $email = stripslashes($_REQUEST['email']);
        $email = mysqli_real_escape_string($conn, $email);
        // récupérer le mot de passe et supprimer les antislashes ajoutés par le formulaire
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($conn, $password);
        //mot de passe crypté
        $hash = hash('sha256', $password);
        //role
        $grade = $_REQUEST['grade'];

        if (username_valid($conn, $username)) {
            $query = "INSERT INTO `login` (`user_id`, `username`, `email`, `grade`, `password`, `enabled`) VALUES (NULL,'$username','$email','$grade','$hash', 0)";
            console_log($query);
            // Exécuter la requête sur la base de données
            $res = mysqli_query($conn, $query);
            if ($res) {
                echo "<div class='sucess'>
                    <h3>Vous êtes inscrit avec succès.</h3>
                    <p>Cliquez ici pour vous <a href='login.php'>connecter</a></p>
                    </div>";
            } else {
                $message = "Erreur de connection";
            }
        } else {
            $message = "Nom d'utilisateur déja utilisé";
        }
    } else {
        $message = "Veuillez remplir toutes les informations";
    }
    unset($_POST);
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>

</body>
<form class="box" action="" method="post">
    <h1 class="box-logo box-title"><a href="index.php">Doctolib</a></h1>
    <h1 class="box-title">S'inscrire</h1>
    <input type="text" class="box-input" name="username" placeholder="Nom d'utilisateur" required />
    <input type="text" class="box-input" name="email" placeholder="Email" required />
    <input type="password" class="box-input" name="password" placeholder="Mot de passe" required />
    <fieldset>
        <legend>Inscription en tant que:</legend>
        <div class="box-radio">
            <input class="radio" type="radio" id="roleChoice1" name="grade" value="CLIENT">
            <label for="roleChoice1">Client</label>

            <input class="radio" type="radio" id="roleChoice2" name="grade" value="DOCTOR">
            <label for="roleChoice2">Médecin</label>
        </div>
    </fieldset>
    <input type="submit" name="submit" value="S'inscrire" class="box-button" />
    <p class="box-register">Déjà inscrit? <a href="login.php">Connectez-vous ici</a></p>
    <?php if (!empty($message)) { ?>
        <p class="errorMessage"><?php echo $message; ?></p>
    <?php } ?>
</form>

</html>