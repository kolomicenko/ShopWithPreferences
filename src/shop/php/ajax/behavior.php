<?php

/** @file
 * Ajax backend to store behavior to database.
 * This script doesn't send anything back (because this happens during onUnload
 * and browsers don't wait for the response anyway), it only calls appropriate
 * black box function.
 */

require_once '../myFunctions.php';
require_once '../connect.php';

require_once '../blackBox.php';

session_start();

// check input
if ($_POST['productID'] <= 0)
    return;

// using $_SESSION['userID'] is wrong when user logs in or off
// on the other hand, $_POST could be abused


$blackBox = new BlackBox($_SESSION['userID']);
$blackBox->setPreference("TopItems", $_POST['productID'], $_POST['scrollCount'],
                             $_POST['clickCount'], $_POST['displayTime'],
                             $_POST['displayCount']);



?>