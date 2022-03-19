<?php
require('../config.php');
session_start();

if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($conn, $username);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($conn, $password);
    $query = "SELECT * FROM `login` WHERE username='$username' and password='" . hash('sha256', $password) . "'";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    $rows = mysqli_num_rows($result);
    if ($rows == 1) {
        $_SESSION['username'] = $username;
        header("Location: success.php");
    } else {
        $message = "Le nom d'utilisateur ou le mot de passe est incorrect.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="box">
        <h1 class="box-logo box-title"><a class="box-logo" href="../index.php">Doctolib</a></h1>
        <h1 class="box-title">Connexion</h1>
        <div class="float-container">
        <form class="float-user-child" action="" method="post" name="login">
            <?php if (!empty($message)) { ?>
                <p class="errorMessage"><?php echo $message; ?></p>
            <?php } ?>
            <input type="text" class="box-input" name="username" placeholder="Nom d'utilisateur">
            <input type="password" class="box-input" name="password" placeholder="Mot de passe">
            <input type="submit" value="Connexion" name="submit" class="box-button">
            <p class="box-register">Vous êtes nouveau ici? <a href="register.php">S'inscrire</a></p>
        </form>
    </div>
    </div>
</body>

</html>