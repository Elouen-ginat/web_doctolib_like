<?php
// Initialiser la session
session_start();
// Vérifiez si l"utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
    header("Location:../login/login.php");
    exit;
}
$url = "http://localhost/api/doctor.php";
$myData = file_get_contents($url);
$json = json_decode($myData, true);

function get_color($info, $day)
{
    $info = json_decode($info, true);
    if (!$info) {
        return "#8A93A6";
    }
    if (array_key_exists($day, $info)) {
        return "#30BF45";
    } else {
        return "#8A93A6";
    }
}

function selectDoctor()
{
    if (isset($_POST["doctor_id"])) {
        $doctor_id = $_POST["doctor_id"];
        $_SESSION["doctor_id"] = $doctor_id;
        header("Location:calendar.php");
    }
}

$cards = '';
foreach ($json as $idx => $info) {

    $monday = get_color($info["activity_days"], "monday");
    $tuesday = get_color($info["activity_days"], "tuesday");
    $wednesday = get_color($info["activity_days"], "wednesday");
    $thursday = get_color($info["activity_days"], "thursday");
    $friday = get_color($info["activity_days"], "friday");
    $saturday = get_color($info["activity_days"], "saturday");

    $cards .= '<button class="cards_item" type="submit" name="doctor_id" value="' . $info["doctor_id"] . '"">
        <div class="card_header">
            <img class="doctor_icon" src="../assets/svg/doctor-svgrepo-2.svg" />
            <div class="doctor_name">
                <p>' . $info["lastname"] . ' ' . $info["firstname"] . '</p>
            </div>
            <img class="doctor_arrow" src="../assets/svg/arrow-right-svgrepo-com.svg" />
        </div>
        <div class="doctor_info_recto">
            <div class="info">
                <img class="info_def" src="../assets/svg/172496_location_icon.svg" />
                 <div class="info_office">'  . $info["office"] . '
                </div>
            </div>
            <div class="info">
            <img class="info_def" src="../assets/svg/3586359_device_mobile_phone_smartphone_icon.svg" />
             <div class="info_num"> ' . $info["phone"] . '
            </div>
        </div>
        
        </div>
        <div class="doctor_info_verso">
            <div class="card_hours">Disponible de ' . $info["str_hour"] . ' à ' . $info["end_hour"] . ' les :</div>
            <div class="card_dates">
                <div class="date" style="background-color:' . $monday . '">Lundi</div>
                <div class="date" style="background-color:' . $thursday . '">Jeudi</div>
                <div class="date" style="background-color:' . $tuesday . '">Mardi</div>
                <div class="date" style="background-color:' . $friday . '">Vendredi</div>
                <div class="date" style="background-color:' . $wednesday . '">Mercredi</div>
                <div class="date" style="background-color:' . $saturday . '">Samedi</div>
            </div>
        </div>
    </button>';
}

selectDoctor();


?>



<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor</title>
    <link rel="stylesheet" href="../assets/css/colors.css" />
    <link rel="stylesheet" href="../assets/css/bulma.min.css">
    <link rel="stylesheet" href="../assets/css/doctor.css">
    <link rel="stylesheet" href="../assets/css/logout.css" />
</head>

<body>
    <section class="hero is-medium is-info is-bold">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h1 class="title">
                    <a href="../index.php">DOCTOR</a>
                </h1>
                <h2 class="subtitle">
                    Choisissez un médecin
                </h2>
            </div>
        </div>
    </section>
    <div class="navigation">
        <form class="logout-form" method="POST">
            <a class="logout-button" href="../login/logout.php">
                <img class="images" src="../assets/svg/turn-off-svgrepo-com.svg">
                <div class="pseudo"> <?php echo $_SESSION["username"] ?> </div>
                <div class="logout"> | Déconnexion</div>

            </a>
        </form>
    </div>


    <section class="section">
        <div class="container">
            <form class="cards" method="post">
                <?php echo $cards ?>
            </form>
        </div>
    </section>
</body>

</html>