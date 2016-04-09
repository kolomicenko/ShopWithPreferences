<?php

/** @file
 * Ajax backend to store received explicit rating to database.
 * Confirmation is sent back.
 */

require_once '../myFunctions.php';
require_once '../connect.php';

require_once '../data/productRatings.php';

session_start();

//check input
if ($_POST['rating'] > ProductRatings::$STARS_COUNT || $_POST['rating'] < 1 ||
    !$_SESSION['userLogged'] || $_POST['productID'] <= 0)
    return;

query("insert into explratings (productID, userID, explRating)
        values(%d,%d,%d) on duplicate key update explRating = %d",
        $_POST['productID'], $_SESSION['userID'], $_POST['rating'], $_POST['rating']);

?>

<p>OK</p>