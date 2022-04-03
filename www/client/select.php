<?php
// Initialiser la session
session_start();
// Vérifiez si l"utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
    header("Location:../login/login.php");
    exit;
}

if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] != "admin") {
    header("Location:../login/login.php");
    exit;
}

$is_refresh = false;
if (isset($_SESSION['post'])) {
    if ($_SESSION['post'] == $_POST) {
        $is_refresh = true;
    }
}
$_SESSION['post'] = $_POST;

$url = "http://localhost/api/client.php?username=" . $_SESSION["username"] . "&password=" . $_SESSION["password"];
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

updateSessionVar('search');

if (isset($_POST['search'])) {
    $search = strtolower($_POST['search']);
    $filtered_json = array_filter($json, function ($item) use ($search) {
        return strpos(strtolower($item['lastname']), $search) !== false || strpos(strtolower($item['firstname']), $search) !== false;
    });
    $json = $filtered_json;
    echo "eee";
}

$cards = '';
foreach ($json as $idx => $info) {

    $cards .= '<button class="cards_item" type="submit" name="doctor_id" value="' . $info["doctor_id"] . '"">
        <div class="card_header">
            <img class="client_icon" src="../assets/svg/403019_avatar_male_man_person_user_icon.svg" />
            <div class="client_name">
                <p>' . $info["lastname"] . ' ' . $info["firstname"] . '</p>
            </div>
            <img class="client_arrow" src="../assets/svg/arrow-right-svgrepo-com.svg" />
        </div>
        <div class="client_info_recto">
            <div class="info">
                <img class="info_def" src="../assets/svg/172496_location_icon.svg" />
                 <div class="info_office">'  . $info["adresse"] . '</div>
            </div>
            <div class="info">
                <img class="info_def" src="../assets/svg/3586359_device_mobile_phone_smartphone_icon.svg" />
                <div class="info_num"> ' . $info["phone"] . '</div>
            </div>
            <div class="info">
                <img class="info_def" src="../assets/svg/185078_mail_email_icon.svg" />
                 <div class="info_office">'  . $info["email"] . '</div>
            </div>
            <div class="info">
                <img class="info_def" src="../assets/svg/6707676_birthday_calendar_decoration_event_fun_icon.svg" />
                 <div class="info_office">'  . $info["birthday"] . '</div>
            </div>
        </div>
        <div class="client_info_verso">
            <div class="card_hours">Commentaire : </div>
            <div class="card_hours">' . $info["comment"] . '</div>
        </div>
        <div class="enable">
            <div class="rect-enable">
                <img class="enable-icon" src="../assets/svg/1904654_cancel_close_cross_delete_reject_icon.svg" />
                <div class="enable-text">Desactivé</div>
            </div>
        </div>
    </button>';
}

// <div class="rect-enable">
//                 <img class="enable-icon" src="../assets/svg/430087_check_checkmark_circle_icon.svg" />
//                 <div class="enable-text">Activé</div>
//             </div>
// <div class="rect-enable">
//                 <img class="enable-icon" src="../assets/svg/1904654_cancel_close_cross_delete_reject_icon.svg" />
//                 <div class="enable-text">Desactivé</div>
//             </div>

selectDoctor();


?>



<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor</title>
    <link rel="stylesheet" href="../assets/css/colors.css" />
    <link rel="stylesheet" href="/assets/css/bulma.min.css">
    <link rel="stylesheet" href="../assets/css/client.css">
    <link rel="stylesheet" href="../assets/css/logout.css" />
    <link rel="stylesheet" href="../assets/css/add.css" />
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
        <form class="logout-search-form" method="POST">
            <input class="search-input" type="text" name="search" placeholder="Rechercher un client" value="<?php echo getInputValue('search'); ?>" onchange="submit()" />
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

    <div class="navigation-add">
        <form class="add-form" method="POST">
            <a class="add-button" href="../login/register.php">
                <img class="images" src="../assets/svg/134224_add_plus_new_icon.svg">
                <div class="pseudo"> Ajouter un client </div>
                <div class="logout">...</div>
            </a>
        </form>
    </div>
</body>

</html>