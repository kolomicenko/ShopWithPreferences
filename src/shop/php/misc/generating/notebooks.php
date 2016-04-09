<?php

/** @file
 * Generates notebooks and their attributes and saves them to the database.
 */

return;

require_once '../../connect.php';
require_once '../../myFunctions.php';

$numberOfNotebooks = 1000;


// generating of notebooks

$brandValues = array("Acer", "Asus", "Dell", "Fujitsu-Siemens", "HP", "Lenovo",
                "Packard Bell", "Sony", "Toshiba");

$lcdSizeValues = array("11.6","12.1", "13.3",   "14.1",    "15.6",    "16.0",    "17.3");

$hddSizeValues = array(    "160",    "250",    "320",    "500",    "640");

$memorySizeValues = array(    "256",    "512",    "1024",    "2048",    "3072",    "4096");

$processorValues = array(    "AMD Athlon",    "AMD Sempron",    "AMD Turion",    "Intel Atom",
        "Intel Celeron Dual-Core",    "Intel Core 2 Duo",    "Intel Core 2 Solo",
            "Intel Core Duo",    "Intel Pentium Dual-Core",    "Intel Core i3",
                "Intel Core i5",    "Intel Core i7");

//$cpuFrequency rand(120, 260) / 100

//$weight rand(150, 350) / 100

$osValues = array(    "Linux",    "MAC OS",    "Windows Vista",    "Windows 7",    "Windows XP");

// $price rand(15000, 30000)

$maxID = mysql_result(query("select max(productID) from products"),0);

for ($id = $maxID + 1; $id <= $maxID + $numberOfNotebooks; $id ++){
    $brand = $brandValues[array_rand($brandValues)];
    query ("insert into products (productID,productName,productPrice,categoryID)
        values ($id, '%s', %d, 1)", $brand . ' ' . $id, rand(15000, 30000));

    query ("insert into attrvalues (attrID, productID, stringValue)
        values(1, $id, '$brand')");
    query ("insert into attrvalues (attrID, productID, floatValue)
        values(2, $id, '%s')", $lcdSizeValues[array_rand($lcdSizeValues)]);
    query ("insert into attrvalues (attrID, productID, intValue)
        values(3, $id, %d)", $hddSizeValues[array_rand($hddSizeValues)]);
    query ("insert into attrvalues (attrID, productID, intValue)
        values(4, $id, %d)", $memorySizeValues[array_rand($memorySizeValues)]);
    query ("insert into attrvalues (attrID, productID, stringValue)
        values(5, $id, '%s')", $processorValues[array_rand($processorValues)]);
    query ("insert into attrvalues (attrID, productID, floatValue)
        values(6, $id, '%s')", rand(120, 260) / 100);
    query ("insert into attrvalues (attrID, productID, floatValue)
        values(7, $id, '%s')", rand(150, 350) / 100);
    query ("insert into attrvalues (attrID, productID, stringValue)
        values(8, $id, '%s')", $osValues[array_rand($osValues)]);


}

// generating of notebook categories
// changing notebooks' cat numbers

$catNames = array(10 => "11.6","12.1", "13.3",   "14.1",    "15.6",    "16.0",    "17.3");

foreach ($catNames as $number => $name){
    query("insert into categories(categoryName, superCategory, attrsToDisplay)
        values ('%s', 1, 5)", $name.'"');

    query("update products set categoryID=$number where productID in (
        select productID from attrvalues where attrID=2 and concat(floatValue)='$name')");

}


// generating of notebooks' imagePaths

$two = "";

for ($id = $maxID + 1; $id <= $maxID + $numberOfNotebooks; $id ++){
    $name = mysql_result(query("select productName from products where productID = $id"), 0);
    $x = explode(" ", $name);
    $imgName = strtolower($x[0]);

    query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'small')",
        'img/products/small/'.$imgName.$two.'.jpg');

    query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'medium')",
        'img/products/medium/'.$imgName.$two.'.jpg');

    query("insert into imagePaths(productID, imgPath, imgSize) values ($id, '%s', 'big')",
        'img/products/big/'.$imgName.$two.'.jpg');

    if ($two == ""){
        $two = "2";
    }else{
        $two = "";
    }
}

echo "done";




?>
