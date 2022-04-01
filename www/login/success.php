<?php
// Initialiser la session
session_start();
// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <script src="http://code.jquery.com/jquery.js" type="text/javascript"></script>
    <!--<script type="text/javascript">
        jQuery(function($){
            $('.month').hide();
            /*$('.month:first').show();*/
            $('.months a:first').addClass('active');
            var current = 1;
            $('.months a').click(function(){
                var month = $(this).attr('id').replace('linkMonth','');
                if (month != current){
                    $('#month'+current).slideUp();
                    $('#month'+month).slideDown();
                    $('.months a').removeClass('active');
                    $('.months a#linkMonth'+month).addClass('active');
                    current = month;
                }
                return false;
            });

        });
    </script>-->


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
    $infos = 'Docteur '.$nom.' '.$prenom."<br> Cabinet :".$office."<br> Numéro :".$num."<br>";
}

?>

    <div class="sucess">
        <h1>Bienvenue <?php echo $_SESSION['username']; ?>!</h1>
        <p>C'est votre tableau de bord.  </p>
        <a href="logout.php">Déconnexion</a>
    </div>

    <div class="medecin">
            <thead>
                <tr>
                    <?php foreach ($response as $id): ?>
                        <th> <?php echo $infos; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>

               </tr>
            </tbody>
        </div>

<!--


    require('accueil.php');
    $date = new Date();
    $year = date('Y');
 /*   $events = $date->getEvents($year);*/
    $dates = $date->getAll($year);
    ?>
    <div class="periods">
        <div class="year"><?php echo $year; ?></div>

        <div class="months">
            <ul>
                <?php foreach ($date->months as $id=>$m): ?>
                    <li><a href='#' id="linkMonth<?php echo $id+1; ?>"><?php echo utf8_encode(substr(utf8_decode($m),0,3)); ?></li>
                <?php endforeach; ?>
            </ul>
    </div>
    <div class="clear"></div>

    <?php $dates = current($dates); ?>
    <?php foreach ($dates as $m=>$days): ?>
        <div class="month" id="month<?php echo $m; ?>">
        <table>
            <thead>
                <tr>
                    <?php foreach ($date->days as $d): ?>
                        <th> <?php echo substr($d,0,3); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                <?php $end= end($days); foreach ($days as $d=>$w): ?>
                    <?php if($d == 1): ?>
                        <td colspan="<?php echo $w-1; ?>"> </td>
                    <?php endif; ?>
                    <td><?php echo $d; ?></td>

                    <?php if($w == 7): ?>
                        </tr><tr>
                    <?php endif; ?>
                <?php endforeach; ?>

               <?php if ($end != 7): ?>
                    <td colspan="<?php echo 7-$end; ?>"></td>
                <?php endif; ?>

               </tr>
            </tbody>
        </table>
        </div>
    <?php endforeach; ?>





    <pre> <?php print_r($dates); ?> </pre>-->
</body>

</html>