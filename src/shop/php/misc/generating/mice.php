<?php

/** @file
 * Generates mice and their attributes and saves them to the database.
 */

return;

require_once '../../connect.php';
require_once '../../myFunctions.php';



$mice = array('creativeOptical5000' => 'Creative 5000',
    'geniusNavigator535' => 'Genius Navigator 535',
    'logitechG9' => 'Logitech G9',
    'logitechV550' => 'Logitech V550',
    'microsoftSidewinder' => 'Microsoft Sidewinder');




$id = mysql_result(query("select max(productID) from products"),0);

$resolution = array(800, 1200, 1000);

$buttons = array(3, 5, 6);

$connection = array('USB', 'PS/2');

$type = array('Optical', 'Laser', 'BlueTrack');



foreach($mice as $path => $name){
    $id ++;
    query ("insert into products (productID,productName,productPrice,categoryID)
    values ($id,'%s', %d, 21)", $name, rand(200, 1500));

    query ("insert into attrvalues (attrID, productID, stringValue)
    values(15, $id, '%s')", $type[array_rand($type)]);

    query ("insert into attrvalues (attrID, productID, intValue)
    values(16, $id, %d)", $buttons[array_rand($buttons)]);

    query ("insert into attrvalues (attrID, productID, stringValue)
    values(17, $id, '%s')", $connection[array_rand($connection)]);

    query ("insert into attrvalues (attrID, productID, intValue)
    values(18, $id, %d)", $resolution[array_rand($resolution)]);


    query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'small')",
    'img/products/small/'.$path.'.jpg');

    query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'medium')",
    'img/products/medium/'.$path.'.jpg');

    query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'big')",
    'img/products/big/'.$path.'.jpg');




}




echo 'done';

?>
