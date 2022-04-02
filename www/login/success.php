<?php
// Initialiser la session
session_start();
// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

//echo "doctor_id = ". $_SESSION["doctor_id"];
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <script src="http://code.jquery.com/jquery.js" type="text/javascript"></script>
</head>

<body>

<?php
$response = file_get_contents('http://localhost/api/doctor.php/lastname');
$response = json_decode($response);
foreach ($response as $id){
    $nom =$id->lastname;
    $prenom =$id->firstname;
    $office =$id->office;
    $num =$id->phone;
    $start_hour= $id->str_hour;
    $end_hour=$id->end_hour;
    $activity_days= $id->activity_days;
    echo $activity_days;
    $infos = 'Docteur '.$nom.' '.$prenom."<br> Cabinet :".$office."<br> Numéro :".$num."<br>";
}

function timestamp_date($d,$m,$y,$h,$mn,$s ){
        return mktime($h,$mn, $s, $m,  $d, $y);
    }


function calandarbydate($date, $start , $end ){

        $dateTab = explode('-',$date);
        $startTab = explode(':',$start);
        $endTab = explode(':',$end);

        $a = array('date'=>$date,
          'start'=>timestamp_date($dateTab[0],$dateTab[1],$dateTab[2],$startTab[0],$startTab[1],$startTab[2]),
          'end'=> timestamp_date( $dateTab[0],$dateTab[1],$dateTab[2],$endTab[0],$endTab[1],$endTab[2]),
          'register' =>  array());


        return $a;}



require('accueil.php');
$date = new Date();
$year = date('Y');
$today_m =floatval(date('m'));
if($today_m <10){
    $today_m = substr(str_repeat(0, 1).$today_m, 0);
                }
$today_d = floatval(date('j'));
$dates = $date->getAll($year);
$min = 15;
$plage= $date->getMinPlage($min);
?>

    <?php
    $response = file_get_contents('http://localhost/api/doctor.php/lastname');
    $response = json_decode($response);
    foreach ($response as $id) {
        $nom = $id->lastname;
        $prenom = $id->firstname;
        $office = $id->office;
        $num = $id->phone;
        $infos = 'Docteur ' . $nom . ' ' . $prenom . "<br> Cabinet :" . $office . "<br> Numéro :" . $num . "<br>";
    }

    ?>


    <div class="sucess">
        <h1>Bienvenue <?php echo $_SESSION['username']; ?>!</h1>
        <p>C'est votre tableau de bord. </p>
        <a href="logout.php">Déconnexion</a>

    </div>

    <div class="medecin">
            <thead>
                <tr>
                    <?php foreach ($response as $id): ?>
                        <th> <?php $nom =$id->lastname;
                        $prenom =$id->firstname;
                        $office =$id->office;
                        $num =$id->phone;
                        $start_hour= $id->str_hour;
                        $end_hour=$id->end_hour;
                        $infos = '<br> Docteur '.$nom.' '.$prenom."<br> Cabinet :".$office."<br> Numéro :".$num."<br>";
                        //echo $infos;
                        $calandar = array();
                        $calandar[date("d-m-Y")] = calandarbydate(date("d-m-Y"), $start_hour ,  $end_hour );
                        //BDD appointment
                        $start_app=array("9:30:0", "15:00:0", '19:20:0');
                        $end_app=array("10:00:0", "16:00:0", '19:30:0');
                        $day_app=array('04-04-2022','04-04-2022','05-04-2022');
                        $nb_app=count($start_app);
                        //echo $nb_app;


                        //créer le calendrier
                        foreach(range(1, 6) as $number){
                            $j = $date->next_day($number);

                            $good_day= $date->get_day($j);
                            $v = json_decode($activity_days, true);

                            if (array_key_exists($good_day,$v)){
                                $calandar[$j] = calandarbydate($j, $start_hour ,  $end_hour );
                                //$calandar[$j]['register'][] =  calandarbydate($j, "9:30:00" ,"10:00:00");
                                }}
                        foreach(range(0, $nb_app-1) as $nb){
                            $calandar[$day_app[$nb]]['register'][] =  calandarbydate($day_app[$nb], $start_app[$nb] ,$end_app[$nb]);
                        }
                        echo $date->viewCalandar($calandar ,$plage );

                        ?></th>
                    <?php endforeach;
                    ?>
                </tr>
            </thead>

    </div>
</body>

</html>