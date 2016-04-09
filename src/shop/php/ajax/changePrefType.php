<?php

/** @file
 * Ajax backend to change shipping or payment types.
 * This script doesn't send anything back, it only calls appropriate black box
 * function. Confirmation is sent back.
 */

require_once '../myFunctions.php';
require_once '../connect.php';
require_once '../data/checkOutTypes.php';
require_once '../data/preference.php';
require_once '../blackBox.php';


session_start();

// check input
if (!$_SESSION['userLogged'] || $_POST['value'] <= 0 || $_POST['name'] == '')
    return;


$blackBox = new BlackBox($_SESSION['userID']);

$blackBox->setPreference($_POST['name'], $_POST['value']);


?>

<p>OK</p>
