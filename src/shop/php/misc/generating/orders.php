<?php

return;

require_once '../../connect.php';
require_once '../../myFunctions.php';

/** @file
 * Generates random orders and saves them to the database.
 * Every product will be at least in one order, all quantities are left at 1
 * because of no importance.
 *
 */

 $maxID = mysql_result(query("select max(productID) from products"),0);

 for ($i = 1; $i <= $maxID; ++$i){
     $count = rand(1, 10); //maximum count of distinct items in an order
     query("insert into orders(orderID, userID, time)
            values($i, 0, %d)", time());
     query("insert into orderProducts(orderID, productID, quantity)
            values($i, $i, 1)");
     for ($c = 1; $c < $count; ++$c){
         $j = rand(1, $maxID);
         if ($i == $j)
            continue;
         query("insert into orderProducts(orderID, productID, quantity)
            values($i, $j, 1)");
     }
 }

 echo mysql_error();




?>
