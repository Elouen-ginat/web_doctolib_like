<?php
// Initialiser la session
session_start();
// Vérifiez si l"utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
    header("Location:../login/login.php");
    exit;
}

// Vérifiez si le paramètre doctor_id est défini, sinon redirigez-le vers la page de sélection de médecin
if (!isset($_SESSION["doctor_id"])) {
    header("Location:../doctor/select.php");
    exit;
}

// Verifie si la page a été raffraichie
$is_refresh = false;
if (isset($_SESSION['post'])) {
    if ($_SESSION['post'] == $_POST) {
        $is_refresh = true;
    }
}
$_SESSION['post'] = $_POST;

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
    } else if ($varname == 'date') {
        return date('Y-m-d');
    }
    return '';
}

function get_week(string $date)
{
    $dateTab = explode('-', $date);
    $week = date('W', mktime(0, 0, 0, $dateTab[1], $dateTab[2], $dateTab[0]));
    return $week;
}

function get_year(string $date)
{
    $dateTab = explode('-', $date);
    $year = date('Y', mktime(0, 0, 0, $dateTab[1], $dateTab[2], $dateTab[0]));
    return $year;
}

function getStartAndEndDate($week, $year)
{
    $dateTime = new DateTime();
    $dateTime->setISODate($year, $week);
    $result['str_date'] = $dateTime->format('Y-m-d');
    $dateTime->modify('+6 days');
    $result['end_date'] = $dateTime->format('Y-m-d');
    return $result;
}


// Ajouter un rendez-vous
function post($datetime, $comment)
{
    $url = "http://localhost/api/appointment.php";
    $data = array(
        'doctor_id' => $_SESSION["doctor_id"], 'username' => $_SESSION["username"],
        'password' => $_SESSION["password"], 'datetime' => $datetime, 'comment' => $comment
    );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    echo $response;
    $response = json_decode($response, true);
    if ($response != null && $response["status"] == 200) {
        return true;
    } else {
        return false;
    }
}

// HTML du calendrier
$calendar = "";
// Vérifiez si le formulaire a été soumis avec un rendez-vous
$appointment_is_set = null;
// Affiche l'overlay de commentaire
$show_comment_overlay = false;

updateSessionVar('date');
updateSessionVar('comment');

// On retourne sur la page de selection de médecin
if (!$is_refresh && isset($_POST['prev_doctor'])) {
    // prev page from doctor
    $_SESSION['doctor_id'] = '';
    header("Location:../doctor/select.php");
    exit;
}

function getAPIResponse($dates)
{
    $url = 'http://localhost/api/appointment.php?id=' . $_SESSION["doctor_id"] . '&str_date=' . $dates["str_date"] . '&end_date=' . $dates["end_date"] . '';
    $ch = curl_init($url);
    try {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return false;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == intval(200)) {
            return $response;
        } else {
            return false;
        }
    } catch (\Throwable $th) {
        return false;
    } finally {
        curl_close($ch);
    }
}


function getAppointments()
{
    global $error, $appointment_is_set;
    //Recuperer les dates de debut de fin de semaine
    $date = getInputValue('date');
    $week = get_week($date);
    $year = get_year($date);

    $dates = getStartAndEndDate($week, $year);

    //Appel l'api pour recuperer les rendez-vous  
    $response = getAPIResponse($dates);

    $appointments = array();
    if (!$response) {
        $error = "Impossible de se connecter à l'API";
    } else {
        $appointments = json_decode($response, true);
    }

    // array to store all appointments
    $days = array();

    //Create array of days format data
    foreach ($appointments as $date => $user_id) {
        $date = new DateTime($date);
        //Recupere le jour de date
        $day = $date->format('Y-m-d');
        //Recupere l'heure de date
        $time = $date->format('H:i:s');
        //Appointment free
        $free = $user_id == null;
        //Format date
        $date_str = $date->format('Y-m-d H:i:s');
        $date_str_post = $date->format('Y-m-d_H:i:s');
        //Add to days array
        $days[$day][$time]["free"] = $free;
        $days[$day][$time]["datetime"] = $date_str;

        //Si un rdv a été cliqué
        if (isset($_POST[$date_str_post])) {
            $appointment_is_set = $date_str;
        }
    }
    return $days;
}

$days = getAppointments();

// Set the appointment datetime
if ($appointment_is_set != null) {
    $datetime = stripslashes($appointment_is_set);
    try {
        $datetime = new DateTime($datetime);
    } catch (Exception $e) {
        $error = "La date n'est pas valide";
    }
    $datetime = $datetime->format('Y-m-d H:i:s');
    $_SESSION['datetime'] = $datetime;
    $show_comment_overlay = true;
}
// Si le formulaire a été soumis avec un rendez-vous on poste le rendez-vous
if (!$is_refresh && isset($_POST['comment']) && $_SESSION['datetime'] != null) {
    $comment = stripslashes($_POST['comment']);
    $sucess = post($datetime, $comment);
    if (!$sucess) {
        $error = "Impossible de prendre le rendez-vous";
    }
    $days = getAppointments();
    $show_comment_overlay = false;
}

// Create HTML for the calendar

function get_comment_overlay_html($show)
{
    if ($show) {
        $style = "display:block";
    } else {
        $style = "display:none";
    }
    $html = '<div class="comment-container" style="' . $style . '">
            <div class="comment-box">
                <div class="comment-box-header">
                    <span class="comment-box-title">Commentaire</span>
                    <span class="comment-box-close">&times;</span>
                </div>
                <div class="comment-box-body">
                    <form method="post">
                        <textarea name="comment" id="comment" cols="30" rows="10" placeholder="Votre commentaire"
                            value="<?php echo getInputValue("comment"); ?>"></textarea>
                        <input type="submit" name="comment_submit" value="Prendre rendez-vous" />
                    </form>
                </div>
            </div>
        </div>';
    return $html;
}

function get_list_of_appointment(array $times)
{
    $html = '';
    foreach ($times as $time => $data) {
        $time_str = strtotime($time);
        $time_str = date('H:i', $time_str);
        if ($time_str == '00:00') {
            $time_str = 'Ne travaille pas';
        }
        if ($data["free"] && $time_str != "00:00") {
            $color = "rgb(69, 176, 248)";
            $html .= '<input class="appointment-time" type="submit" name="' . $data["datetime"] . '" value="' . $time_str . '" class="appointment-time" style="background-color:' . $color . '"></input>';
        } else {
            $color = "#707275";
            $html .= '<span class="appointment-time" style="background-color:' . $color . '">' . $time_str . '</span>';
        }
    }
    return $html;
}

$overlay = get_comment_overlay_html($show_comment_overlay);

foreach ($days as $day => $times) {
    $day_obj = strtotime($day);
    $num_day = date('N', $day_obj);
    $day_str = strtotime($day);
    $day_str = date('d/m/Y', $day_str);
    $calendar .= '<div class="day-container week' . $num_day . '">';
    $calendar .= '<span class="appointment-date">' . $day_str . '</span>';
    $calendar .= '<div class="appointment-list">';
    $calendar .= get_list_of_appointment($times);
    $calendar .= '</div>';
    $calendar .= '</div>';
}

?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css">
    <link rel="stylesheet" href="../assets/css/calendar.css">
    <link rel="stylesheet" href="../assets/css/logout.css" />
    <link rel="stylesheet" href="../assets/css/overlay.css" />
</head>

<body>
    <div class="overlay">
        <?php echo $overlay; ?>
    </div>
    <section class="hero is-medium is-info is-bold">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h1 class="title">
                    <a href="../index.php">DOCTOR</a>
                </h1>
                <h2 class="subtitle">
                    Choisissez un rendez-vous
                </h2>
            </div>
        </div>
    </section>
    <div class="navigation">
        <form class="logout-form" method="POST" action="logout.php">
            <a class="button" href="../login/logout.php">
                <img class="images" src="../assets/svg/turn-off-svgrepo-com.svg">
                <div class="pseudo"> <?php echo $_SESSION["username"] ?> </div>
                <div class="logout"> | Déconnexion</div>

            </a>
        </form>
    </div>
    <section class="section">
        <div class="container">
            <?php
            if (!empty($error)) {
                echo "<p class='errorMessage'>$error</p>";
            }
            ?>
            <form class="calendar-form" method="post">
                <div class="form-header">
                    <input type="submit" name="prev_doctor" value="< Précedent" class="box-prev-button" formnovalidate>
                    <div class="date-input">
                        <legend>Date de rendez-vous</legend>
                        <input type="date" class="box-input" name="date" placeholder="Date du rendez-vous" onchange="submit()" value="<?php echo getInputValue('date'); ?>" required />
                    </div>
                </div>
                <div class="calendar-container">
                    <?php echo $calendar; ?>
                </div>
            </form>
        </div>
    </section>
</body>

</html>