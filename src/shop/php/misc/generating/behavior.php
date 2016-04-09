<?php

return;

require_once '../../connect.php';
require_once '../../myFunctions.php';

/** @file
 * Generates behavior and saves them to the database.
 * Generated behavior si very unrealistic, this script was only used to simulate
 * haevier workload.
 */

for ($i = 1; $i <= 1000; $i++){
    for ($u = 0; $u < 5; $u++){

        $displayCount = $displayCountPHP = rand(1, 100);
        $scrollCount = rand(1,1000000);
        $clickCount = rand(1,100000);
        $displayTime = rand(1,1000000000);

        $query = "replace into behavior values($i, $u, $displayCount, $displayCountPHP,
                            $scrollCount, $clickCount, $displayTime)";

        query($query);
    }
}

echo mysql_error();

?>
