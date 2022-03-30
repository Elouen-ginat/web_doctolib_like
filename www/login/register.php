<?php
require('../config.php');
session_start();

function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}

$is_refresh = false;
if (isset($_SESSION['post'])) {
    if ($_SESSION['post'] == $_POST) {
        $is_refresh = true;
    }
}
$_SESSION['post'] = $_POST;

function Search($search)
{
    if ($search == "home") {
        session_unset();
        session_destroy();
        header("Location:../index.php");
    } else if ($search == "connect") {
        session_unset();
        session_destroy();
        header("Location:login.php");
    }
}

function isUsernameValid(mysqli $conn, string $username)
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

function updateSessionVar(string $varname)
{
    // récupérer les post
    if (isset($_POST[$varname])) {
        $_SESSION["post_$varname"] = $_POST[$varname];
    }
}

function getInputValue(string $varname)
{
    if (isset($_SESSION["post_$varname"])) {
        return htmlspecialchars($_SESSION["post_$varname"], ENT_QUOTES);
    }
    return '';
}

function goNext(mysqli $conn)
{
    global $next_call, $message;
    if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['grade'])) {
        // récupérer le nom d'utilisateur et supprimer les antislashes ajoutés par le formulaire
        $_SESSION['username'] = stripslashes($_POST['username']);
        $_SESSION['username'] = mysqli_real_escape_string($conn, $_SESSION['username']);
        // récupérer l'email et supprimer les antislashes ajoutés par le formulaire
        $_SESSION['email'] = stripslashes($_POST['email']);
        $_SESSION['email'] = mysqli_real_escape_string($conn, $_SESSION['email']);
        // récupérer le mot de passe et supprimer les antislashes ajoutés par le formulaire
        $password = stripslashes($_POST['password']);
        $password = mysqli_real_escape_string($conn, $password);
        //mot de passe crypté
        $_SESSION['hash'] = hash('sha256', $password);
        //role
        $_SESSION['grade'] = $_POST['grade'];

        $username = $_SESSION['username'];
        $grade = $_SESSION['grade'];

        if (isUsernameValid($conn, $username)) {
            $_SESSION['state'] = $grade;
            $next_call = "<script type=\"text/javascript\">next('$grade');</script>";
        } else {
            $message = "Nom d'utilisateur déja utilisé";
        }
    } else {
        $message = "Veuillez remplir toutes les informations";
    }
}

function addUser(mysqli $conn, string $username, string $email, string $grade, string $hash)
{
    $query = "INSERT INTO `login` (`user_id`, `username`, `email`, `grade`, `password`, `enabled`) VALUES (NULL,'$username','$email','$grade','$hash', 0)";
    console_log($query);
    // Exécuter la requête sur la base de données
    return mysqli_query($conn, $query);
}

function removeUser(mysqli $conn, string $username) {
    $query = "DELETE FROM `login` WHERE username='$username'";
    console_log($query);
    // Exécuter la requête sur la base de données
    return mysqli_query($conn, $query);
}

function getUserID(mysqli $conn, string $username, string $hash)
{
    // Get id of added user
    $query = "SELECT user_id FROM `login` WHERE username='$username' and password='$hash'";
    console_log($query);
    $result = mysqli_query($conn, $query);
    $user_id = mysqli_fetch_row($result);
    if (count($user_id) > 0) {
        return $user_id[0];
    }
    return NULL;
}

function sumbitUserClient(mysqli $conn)
{
    global $message_client, $success_call;
    if (isset(
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['birthday'],
        $_POST['adresse'],
        $_POST['phone'],
        $_POST['comment'],
        $_SESSION['username'],
        $_SESSION['email'],
        $_SESSION['grade'],
        $_SESSION['hash']
    )) {
        // récupérer le prenom d'utilisateur et supprimer les antislashes ajoutés par le formulaire
        $firstname = stripslashes($_POST['firstname']);
        $firstname = mysqli_real_escape_string($conn, $firstname);
        // récupérer le nom et supprimer les antislashes ajoutés par le formulaire
        $lastname = stripslashes($_POST['lastname']);
        $lastname = mysqli_real_escape_string($conn, $lastname);
        // récupérer date de naissance
        $birthday = stripslashes($_POST['birthday']);
        $birthday = mysqli_real_escape_string($conn, $birthday);
        // récupérer adresse
        $adresse = stripslashes($_POST['adresse']);
        $adresse = mysqli_real_escape_string($conn, $adresse);
        // récupérer telephone
        $phone = stripslashes($_POST['phone']);
        $phone = mysqli_real_escape_string($conn, $phone);
        // récupérer commentaire
        $comment = stripslashes($_POST['comment']);
        $comment = mysqli_real_escape_string($conn, $comment);

        $username = $_SESSION['username'];
        $email = $_SESSION['email'];
        $grade = $_SESSION['grade'];
        $hash = $_SESSION['hash'];

        // add user
        $query = "INSERT INTO `login` (`user_id`, `username`, `email`, `grade`, `password`, `enabled`) VALUES (NULL,'$username','$email','$grade','$hash', 0)";
        console_log($query);
        // Exécuter la requête sur la base de données
        $res = addUser($conn, $username, $email, $grade, $hash);
        if ($res) {
            // Get user ID
            $user_id = getUserID($conn, $username, $hash);
            if ($user_id != NULL) {
                // Add client
                $query = "INSERT INTO `client`(`client_id`, `user_id`, `firstname`, `lastname`, `birthday`, `adresse`, `phone`, `email`, `comment`) 
                VALUES (NULL,$user_id,'$firstname','$lastname','$birthday','$adresse','$phone','$email','$comment')";
                console_log($query);
                // Exécuter la requête sur la base de données
                $res = mysqli_query($conn, $query);
                if ($res) {
                    $_SESSION['state'] = 'SUCCESS';
                    $success_call = '<script type="text/javascript">success("CLIENT");</script>';
                } else {
                    removeUser($conn, $username);
                    $message_client = "Erreur de connection à la base des clients";
                }
            } else {
                removeUser($conn, $username);
                $message_client = "Impossible de récuperer le profil utilisateur";
            }
        } else {
            $message_client = "Erreur de connection à la base des utilisateurs";
        }
    } else {
        $message_client = "Veuillez remplir toutes les informations requises";
    }
}

function sumbitUserDoctor(mysqli $conn)
{
    global $message_doctor, $success_call;
    if (isset(
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['phone'],
        $_POST['office'],
        $_POST['activity_days'],
        $_POST['str_hour'],
        $_POST['end_hour'],
        $_SESSION['username'],
        $_SESSION['email'],
        $_SESSION['grade'],
        $_SESSION['hash']
    )) {
        // récupérer le prenom d'utilisateur et supprimer les antislashes ajoutés par le formulaire
        $firstname = stripslashes($_POST['firstname']);
        $firstname = mysqli_real_escape_string($conn, $firstname);
        // récupérer le nom et supprimer les antislashes ajoutés par le formulaire
        $lastname = stripslashes($_POST['lastname']);
        $lastname = mysqli_real_escape_string($conn, $lastname);
        // récupérer telephone
        $phone = stripslashes($_POST['phone']);
        $phone  = mysqli_real_escape_string($conn, $phone);
        // récupérer adresse
        $office = stripslashes($_POST['office']);
        $office = mysqli_real_escape_string($conn, $office);
        // recupere jour d'activité et transforme le en json
        $activity_days = $_POST['activity_days'];
        console_log($activity_days);
        $activity_days_json = new stdClass();
        foreach ($activity_days as $day) {
            $activity_days_json->$day = true;
        }
		$activity_days_json = json_encode($activity_days_json,JSON_FORCE_OBJECT);
        // récupérer telephone
        $str_hour = stripslashes($_POST['str_hour']);
        $str_hour = mysqli_real_escape_string($conn, $str_hour);
        // récupérer commentaire
        $end_hour = stripslashes($_POST['end_hour']);
        $end_hour = mysqli_real_escape_string($conn, $end_hour);

        $username = $_SESSION['username'];
        $email = $_SESSION['email'];
        $grade = $_SESSION['grade'];
        $hash = $_SESSION['hash'];

        // Add user
        $res = addUser($conn, $username, $email, $grade, $hash);
        if ($res) {
            // Get user ID
            $user_id = getUserID($conn, $username, $hash);
            if ($user_id != NULL) {
                // Add doctor
                $query = "INSERT INTO `doctor`(`doctor_id`, `user_id`, `firstname`, `lastname`, `phone`, `office`, `activity_days`, `str_hour`, `end_hour`) 
                VALUES (NULL,'$user_id','$firstname','$lastname','$phone','$office','$activity_days_json','$str_hour','$end_hour')";
                console_log($query);
                // Exécuter la requête sur la base de données
                $res = mysqli_query($conn, $query);
                if ($res) {
                    $_SESSION['state'] = 'SUCCESS';
                    $success_call = '<script type="text/javascript">success("DOCTOR");</script>';
                } else {
                    removeUser($conn, $username);
                    $message_doctor = "Erreur de connection à la base des Médecins";
                }
            } else {
                removeUser($conn, $username);
                $message_doctor = "Impossible de récuperer le profil utilisateur";
            }
        } else {
            $message_doctor = "Erreur de connection à la base des Médecins";
        }
    } else {
        $message_doctor = "Veuillez remplir toutes les informations requises";
    }
}

updateSessionVar('username');
updateSessionVar('email');
updateSessionVar('password');
updateSessionVar('firstname');
updateSessionVar('lastname');
updateSessionVar('birthday');
updateSessionVar('phone');
updateSessionVar('adresse');
updateSessionVar('office');
updateSessionVar('str_hour');
updateSessionVar('end_hour');
updateSessionVar('comment');


if (isset($_POST['next'])) {
    // next page
    goNext($conn);
}else if (isset($_POST['prev_client'])) {
    // prev page from client
    $_SESSION['state'] = 'USER';
    $prev_client_call = '<script type="text/javascript">prev("CLIENT");</script>';
}else if (isset($_POST['prev_doctor'])) {
    // prev page from doctor
    $_SESSION['state'] = 'USER';
    $prev_doctor_call = '<script type="text/javascript">prev("DOCTOR");</script>';
}

if (isset($_POST['submit_client']) && !$is_refresh) {
    // Post user and client
    sumbitUserClient($conn);
}else if (isset($_POST['submit_doctor']) && !$is_refresh) {
    // Post user and doctor
    sumbitUserDoctor($conn);
}else if (isset($_SESSION["state"]) && $is_refresh) {
    // set state
    $state = $_SESSION["state"];
    $set_state = "<script type='text/javascript'>setState('$state');</script>";
}


if (isset($_GET['search']))
{
    search($_GET['search']);
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <script onload="onLoad();" src="../assets/javascript/loginAnimation.js"></script>
</head>

<body>
    <div class="box">
        <h1 class="box-logo box-title"><a class="box-logo" href="?search=home">Doctolib</a></h1>
        <h1 class="box-title">S'inscrire</h1>
        <div class="float-container">
            <form class="float-user-child" action="" method="post">
                <?php if (!empty($message)) { ?>
                    <p class="errorMessage"><?php echo $message; ?></p>
                <?php } ?>
                <input type="text" class="box-input" name="username" placeholder="Nom d'utilisateur" value="<?php echo getInputValue('username'); ?>" required />
                <input type="email" class="box-input" name="email" placeholder="Email" value="<?php echo getInputValue('email'); ?>" required />
                <input type="password" class="box-input" name="password" placeholder="Mot de passe" value="<?php echo getInputValue('password'); ?>" required />
                <fieldset class="box-radio">
                    <legend>Inscription en tant que</legend>
                    <input class="radio" type="radio" id="roleChoice1" name="grade" value="CLIENT" />
                    <label class="radio" for="roleChoice1">Client</label>

                    <input class="radio" type="radio" id="roleChoice2" name="grade" value="DOCTOR" />
                    <label class="radio" for="roleChoice2">Médecin</label>
                </fieldset>
                <div style="width:90%">
                    <input type="submit" name="next" value="Suivant >" class="box-next-button">
                </div>
            </form>
            <form class="float-client-child" action="" method="post">
                <?php
                if (!empty($message_client)) {
                    echo "<p class='errorMessage'>$message_client</p>
                    <script type='text/javascript'>setState('CLIENT');</script>";
                }
                ?>
                <input type="text" class="box-input" name="firstname" placeholder="Prénom" value="<?php echo getInputValue('firstname'); ?>" required />
                <input type="text" class="box-input" name="lastname" placeholder="Nom" value="<?php echo getInputValue('lastname'); ?>" required />
                <legend>Date de naissance</legend>
                <input type="date" class="box-input" name="birthday" placeholder="Date de naissance" value="<?php echo getInputValue('birthday'); ?>" required />
                <input type="text" class="box-input" name="adresse" placeholder="Adresse" value="<?php echo getInputValue('adresse'); ?>" required />
                <input type="tel" class="box-input" name="phone" placeholder="Téléphone" value="<?php echo getInputValue('phone'); ?>" required />
                <input type="text" class="box-input" name="comment" placeholder="Commentaire ?" value="<?php echo getInputValue('comment'); ?>" />
                <div style="width:100%">
                    <input type="submit" name="prev_client" value="< Précedent" class="box-prev-button" formnovalidate>
                    <input type="submit" name="submit_client" value="S'inscrire" class="box-register-button">
                </div>
            </form>
            <form class="float-doctor-child" action="" method="post">
                <?php
                if (!empty($message_doctor)) {
                    echo "<p class='errorMessage'>$message_doctor</p>
                    <script type='text/javascript'>setState('DOCTOR');</script>";
                }
                ?>
                <input type="text" class="box-input" name="firstname" placeholder="Prénom" value="<?php echo getInputValue('firstname'); ?>" required />
                <input type="text" class="box-input" name="lastname" placeholder="Nom" value="<?php echo getInputValue('lastname'); ?>" required />
                <input type="tel" class="box-input" name="phone" placeholder="Téléphone" value="<?php echo getInputValue('phone'); ?>" required />
                <input type="text" class="box-input" name="office" placeholder="Bureau" value="<?php echo getInputValue('office'); ?>" required />
                <fieldset class="box-radio">
                    <legend>Jour d'activité</legend>
                    <input class="radio" type="checkbox" id="actChoice1" name="activity_days[]" value="monday" />
                    <label class="checkbox" for="actChoice1">Lundi</label>
                    <input class="radio" type="checkbox" id="actChoice2" name="activity_days[]" value="tuesday" />
                    <label class="checkbox" for="actChoice2">Mardi</label>
                    <input class="radio" type="checkbox" id="actChoice3" name="activity_days[]" value="wednesday" />
                    <label class="checkbox" for="actChoice3">Mercredi</label>
                    <input class="radio" type="checkbox" id="actChoice4" name="activity_days[]" value="thursday" />
                    <label class="checkbox" for="actChoice4">Jeudi</label>
                    <input class="radio" type="checkbox" id="actChoice5" name="activity_days[]" value="friday" />
                    <label class="checkbox" for="actChoice5">Vendredi</label>
                    <input class="radio" type="checkbox" id="actChoice6" name="activity_days[]" value="saturday" />
                    <label class="checkbox" for="actChoice6">Samedi</label>
                </fieldset>
                <legend>Heure de début</legend>
                <input type="time" class="box-input" name="str_hour" placeholder="Heure de début" value="<?php echo getInputValue('str_hour'); ?>" required />
                <legend>Heure de finn</legend>
                <input type="time" class="box-input" name="end_hour" placeholder="Heure de fin" value="<?php echo getInputValue('end_hour'); ?>" required />
                <div style="width:100%">
                    <input type="submit" name="prev_doctor" value="< Précedent" class="box-prev-button" formnovalidate>
                    <input type="submit" name="submit_doctor" value="S'inscrire" class="box-register-button">
                </div>
            </form>
            <div class="float-success-child">
                <div class='sucess'>
                    <h3>Vous êtes inscrit avec succès.</h3>
                    <p>Cliquez ici pour vous <a href='?search=connect'>connecter</a></p>
                </div>
            </div>
        </div>
        <p class="box-register">Déjà inscrit? <a href="?search=connect">Connectez-vous ici</a></p>
    </div>
</body>
<?php
if (!empty($next_call)) {
    echo $next_call;
}
if (!empty($prev_client_call)) {
    echo $prev_client_call;
}
if (!empty($prev_doctor_call)) {
    echo $prev_doctor_call;
}
if (!empty($success_call)) {
    echo $success_call;
}
if (!empty($set_state)) {
    echo $set_state;
}
?>

</html>