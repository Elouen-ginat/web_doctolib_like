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

function get_client_info()
{
    global $error;
    $url = "http://localhost/api/client.php?username=" . $_SESSION["username"] . "&password=" . $_SESSION["password"];
    $myData = file_get_contents($url);
    $json = json_decode($myData, true);
    if (!$json) {
        $error = "Erreur lors de la récupération des données";
    }
    return $json;
}

// Ajouter un rendez-vous
function post($enabled, $client_id)
{
    $url = "http://localhost/api/client.php";
    $data = array(
        'username' => $_SESSION["username"],
        'password' => $_SESSION["password"], 'enabled' => $enabled, 'client_id' => $client_id
    );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    $response = json_decode($response, true);
    if ($response != null && $response["status"] == 200) {
        return true;
    } else {
        return false;
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

$json = get_client_info();

updateSessionVar('search');
updateSessionVar('enabled');

if (isset($_SESSION['post_search'])) {
    $search = strtolower($_SESSION['post_search']);
    $filtered_json = array_filter($json, function ($item) use ($search) {
        return strpos(strtolower($item['lastname']), $search) !== false || strpos(strtolower($item['firstname']), $search) !== false;
    });
    $json = $filtered_json;
    echo "eee";
}
if (isset($_SESSION['post_enabled'])) {
    $client_id = stripslashes($_SESSION['post_enabled']);
    $client_selected = array_filter($json, function ($item) use ($client_id) {
        return $item['client_id'] == $client_id;
    });
    if ($client_selected != null) {
        $client_selected = reset($client_selected);
        $enabled = $client_selected['enabled'];
        if (post(!$enabled, $client_id)) {
            foreach ($json as $idx => $info) {
                if ($info['client_id'] == $client_id) {
                    $json[$idx]['enabled'] = !$enabled;
                    break;
                }
            }
        } else {
            $error = "Une erreur est survenue lors de la modification du client";
        }
    } else {
        $error = "Client introuvable";
    }
}

$cards = '';
foreach ($json as $idx => $info) {

    $cards .= '<button class="cards_item" type="submit" name="enabled" value="' . $info["client_id"] . '"">
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
        <div class="enabled">';
    if ($info["enabled"] == 1) {
        $cards .=   '<div class="rect-enabled">
                    <img class="enabled-icon" src="../assets/svg/430087_check_checkmark_circle_icon.svg" />
                    <div class="enabled-text">Activé</div>
                </div>';
    } else {
        $cards .=   '<div class="rect-enabled">
                <img class="enabled-icon" src="../assets/svg/1904654_cancel_close_cross_delete_reject_icon.svg" />
                <div class="enabled-text">Desactivé</div>
            </div>';
    }
    $cards .=  '</div></button>';
}

?>



<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor</title>
    <link rel="stylesheet" href="../assets/css/colors.css" />
    <link rel="stylesheet" href="../assets/css/bulma.min.css">
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
                    Activez desactivé des clients
                </h2>
            </div>
        </div>
    </section>

    <div class="navigation">
        <form class="logout-search-form" method="POST">
            <input class="search-input" type="text" name="search" placeholder="Rechercher un client" value="<?php echo getInputValue('search'); ?>" onchange="submit()" />
            <?php
            if (!empty($error)) {
                echo "<p class='errorMessage'>$error</p>";
            }
            ?>
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