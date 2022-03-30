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

}