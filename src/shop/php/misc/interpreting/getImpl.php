
<?php

/** @file
 * Reads implicit ratings and prepares it as an R input.
 */

require_once '../../connect.php';
require_once '../../myFunctions.php';
require_once '../../data/productRatings.php';

$methodsRes = query("Select method from methods");
$methods = array();
while ($line = mysql_fetch_assoc($methodsRes)){
    $methods[] = $line['method'];
}


foreach ($methods as $method){
    echo "<h1>$method</h1>";


    $result = query("select * from
            implratings natural join methods natural left join explratings
            where userID > 0 and displayCountRating > 0 and method='$method'");
    // displayCountRating > 0 to filter out bought but not visited products

    $impl = array("displayCountRating" => array(),
                  "scrollRating" => array(),
                  "clickRating" => array(),
                  "displayTimeRating" => array(),
                  "finalRating" => array());

    $implNames = array("displayCountRating" => "Display count rating ($method)",
                  "scrollRating" => "Scroll count rating ($method)",
                  "clickRating" => "Click count rating ($method)",
                  "displayTimeRating" => "Display time rating ($method)",
                  "finalRating" => "Final rating ($method)");


    $comma = array();
    for ($i = 0; $i <= ProductRatings::$STARS_COUNT; $i++){
        foreach($impl as &$ar){
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

        foreach ($impl as $key => $ar)
            $impl[$key][$e] .= $comma[$e] . $line[$key];
        $comma[$e] = ",";

    }

    for ($i = 0; $i <= ProductRatings::$STARS_COUNT; $i++){
        foreach ($impl as &$ar){
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

    foreach ($impl as $name => $ar){
        echo "#$name:\n";
        foreach ($ar as $expl => $v){
            echo "ar$expl <- c$v\n";
        }
        echo "\n";
        echo 'boxplot('.$ars1.',outline=FALSE,names=list('.$names.'), xlab="Explicit rating", ylab="'.$implNames[$name].'")
    kruskal.test(list('.$ars2.'))



';
        // bez whiskeru:  whisklty=0,staplelty=0
    }

    echo '</pre>';


}

?>

