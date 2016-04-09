<?php

/** @file
 * Generates speakers and their attributes and saves them to the database.
 */

return;

require_once '../../connect.php';
require_once '../../myFunctions.php';



$r21 = array('creative' => 'Creative',
    'creative7' => 'Creative',
    'genius' => 'Genius',
    'genius2' => 'Genius',
    'genius3' => 'Genius',
    'logitech' => 'Logitech',
    'logitech3' => 'Logitech',
    'logitech4' => 'Logitech',
    'sony3' => 'Sony');

$r51 = array('creative2' => 'Creative',
    'creative3' => 'Creative',
    'creative5' => 'Creative',
    'creative6' => 'Creative',
    'creative8' => 'Creative',
    'creative9' => 'Creative',
    'logitech2' => 'Logitech',
    'sandberg' => 'Sandberg');

$r71 = array('creative4' => 'Creative',
    'creative10' => 'Creative',
    'creative11' => 'Creative');


$id = mysql_result(query("select max(productID) from products"),0);

function gen($ar, $system, $catID){
    global $id;

    $impedance = array('4', '8', '16', '32');

    $frequency = array('40 - 20000', '20 - 20000', '15 - 20000', '60 - 16000');

    $sensitivity = array('70', '80', '90', '100');

    $power = array('12', '25', '40', '50');



    foreach($ar as $path => $brand){
        $id ++;
        query ("insert into products (productID,productName,productPrice,categoryID)
        values ($id,'%s', %d, $catID)", $brand . ' ' . ($id - 1000), rand(1000, 8000));

        query ("insert into attrvalues (attrID, productID, stringValue)
        values(9, $id, '$brand')");

        query ("insert into attrvalues (attrID, productID, intValue)
        values(10, $id, %d)", $impedance[array_rand($impedance)]);
        
        query ("insert into attrvalues (attrID, productID, stringValue)
        values(11, $id, '%s')", $frequency[array_rand($frequency)]);
        
        query ("insert into attrvalues (attrID, productID, intValue)
        values(12, $id, %d)", $sensitivity[array_rand($sensitivity)]);

        query ("insert into attrvalues (attrID, productID, intValue)
        values(13, $id, %d)", $power[array_rand($power)]);

        query ("insert into attrvalues (attrID, productID, stringValue)
        values(14, $id, '%s')", $system);

        query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'small')",
        'img/products/small/'.$path.'.jpg');

        query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'medium')",
        'img/products/medium/'.$path.'.jpg');

        query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'big')",
        'img/products/big/'.$path.'.jpg');

    }


}

gen ($r21, '2.1', 18);
gen ($r51, '5.1', 19);
gen ($r71, '7.1', 20);



echo 'done';

?>
