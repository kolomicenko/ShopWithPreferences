<?php

/** @file
 * Reads behavior and prepares it as an R input.
 */

require_once '../../connect.php';
require_once '../../myFunctions.php';
require_once '../../data/productRatings.php';

$result = query("select * from behavior natural left join explratings");

$behav = array("displayCount" => array(),
              "scrollCount" => array(),
              "clickCount" => array(),
              "displayTime" => array());

$behavNames = array("displayCount" => "Display count",
              "scrollCount" => "Scroll count",
              "clickCount" => "Click count",
              "displayTime" => "Display time");


for ($i = 0; $i <= ProductRatings::$STARS_COUNT; $i++){
    foreach($behav as &$ar){
        $ar[$i] = "(";
    }
    unset($ar);
    $comma[$i] = "";
}



while ($line = mysql_fetch_assoc($result)){
    if ($line['explRating'] == null)
        $e = 0;
    else
        $e = $line['explRating'];

    foreach ($behav as $key => $ar)
        $behav[$key][$e] .= $comma[$e] . $line[$key];
    $comma[$e] = ",";

}

for ($i = 0; $i <= ProductRatings::$STARS_COUNT; $i++){
    foreach ($behav as &$ar){
        $ar[$i] .= ")";
    }
    unset($ar);
}


$names = '"N/A"';
$ars1 = "ar0";
$ars2 = "";
$comma = "";
for ($i = 1; $i <= ProductRatings::$STARS_COUNT; $i++){
    $names .= ',"'.$i.'"';
    $ars1 .= ",ar$i";
    $ars2 .= $comma . "ar$i";
    $comma = ",";
}

echo '<pre>';

foreach ($behav as $name => $ar){
    echo "#$name:\n";
    foreach ($ar as $expl => $v){
        echo "ar$expl <- c$v\n";
    }
    echo "\n";
    echo 'boxplot('.$ars1.',outline=FALSE,names=list('.$names.'), xlab="Explicit rating", ylab="'.$behavNames[$name].'")
kruskal.test(list('.$ars2.'))



';
    // bez whiskeru:  whisklty=0,staplelty=0
}

echo '</pre>';



?>

