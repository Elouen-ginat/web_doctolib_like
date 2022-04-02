<?php
class Date{

    var $days = array('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche');
    var $months = array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre');


    function getAll($year){
        $r= array();

        $date = new Datetime($year.'-01-01');
        while($date->format('Y')<=$year){
            $y = $date->format('Y');
            $m = $date->format('m');
            $d = $date->format('j');
            $w = str_replace('0','7',$date->format('w'));
            $r[$y][$m][$d] = $w;
            $date->add(new DateInterval('P1D'));

        }
        return $r;
    }
    function next_day($day_number)
    {
      for ($i = 2; $i <= 8; $i++)
      {
        $next_day = mktime(0,0,0, date("m"), date("d")+$i, date("Y"));
        if(date("w",$next_day)==$day_number)
        {
          $XDate = getdate($next_day);
          $next_day_fund = sprintf('%02d', $XDate['mday']).'-'.sprintf('%02d', $XDate['mon']).'-'.sprintf('%02d', $XDate['year']);
        }
      }
      return $next_day_fund;
    }

    function get_day($day){
        // date du jour
        $date = date($day);
        echo $date;
        // tableau des jours de la semaine
        $joursem = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        // extraction des jour, mois, an de la date
        list($jour, $mois, $annee) = explode('-', $date);
        // calcul du timestamp
        $timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
        // affichage du jour de la semaine
        return $joursem[date("w",$timestamp)];

    }

    function timestamp_date($d,$m,$y,$h,$mn,$s ){
        return mktime($h,$mn, $s, $m,  $d, $y);
    }

    function getMinPlage($min){
        return $min*60;
    }
    function calandarbydate($date, $start , $end ){

        $dateTab = explode('-',$date);
        $startTab = explode(':',$start);
        $endTab = explode(':',$end);
        $b = timestamp_date($dateTab[0],$dateTab[1],$dateTab[2],$startTab[0],$startTab[1],$startTab[2]);

        $a = array('date'=>$date,
          'start'=>$b,
          'end'=> timestamp_date( $dateTab[0],$dateTab[1],$dateTab[2],$endTab[0],$endTab[1],$endTab[2]),
          'register' =>  array());


        return $a;}

    function viewCalandar($calandar ,$plage ){
            foreach ($calandar as $key => $values){

            $nbdispoplage =   round(( $values['end'] - $values['start'] )/ $plage)  ;
            $timestampDate  = $values['start'] +$nbdispoplage;
            echo '<br/><u>'.$nbdispoplage. ' plages de '.($plage/60).'mn disponible pour le '.$values['date'].'</u><br/>';
            for( $i=0;$i< $nbdispoplage ;$i++){
                $style= "";
                $timestampDate  = $values['start'] + ( $i*$plage );
                if($timestampDate + $plage > $values['end'] ){
                $style='style="color:red"';
                }
                    foreach ($values['register'] as $k => $v ){
                        if($timestampDate >= $v['start']  && $timestampDate < $v['end'] ){
                            $style='style="color:red"';
                        }
                        else if($timestampDate + $plage >  $v['start']  && $timestampDate < $v['end'] ){
                            $style='style="color:red"';
                        }
                    }
                echo"<span $style >".date("H:i:s", $timestampDate).'</span> | ';
            }

        }
    }

}
?>